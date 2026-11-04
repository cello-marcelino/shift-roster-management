<?php

namespace App\Services;

use App\Models\Line;
use App\Models\Manpower;
use App\Models\RosterEntry;
use App\Models\Station;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RosterValidationService
{
    /**
     * Memvalidasi seluruh entri jadwal pada sebuah roster aktif.
     * Mengembalikan daftar peringatan & catatan informasi tanpa memblokir penyimpanan.
     *
     * @param int $lineId ID line yang sedang diedit
     * @param string $weekStart Tanggal mulai minggu (Y-m-d)
     * @param array $gridEntries Array penugasan: [ [ 'shift' => 1, 'station_id' => 10, 'manpower_id' => 25 ], ... ]
     * @return array [
     *    'duplicate_warnings' => [ ... ],
     *    'skill_warnings' => [ ... ],
     *    'mp_warnings' => [ ... ],
     *    'operator_statuses' => [ 'shift_manpowerId' => [ 'type' => 'warning'|'info'|'error', 'messages' => [] ] ]
     * ]
     */
    public function validateSchedule(int $lineId, string $weekStart, array $gridEntries, ?int $currentRosterId = null): array
    {
        $line = Line::with('stations')->findOrFail($lineId);
        $stationsById = $line->stations->keyBy('id');

        // Ambil data manpower yang terlibat dalam grid
        $manpowerIds = array_filter(array_column($gridEntries, 'manpower_id'));

        // Jika seluruh jadwal belum memiliki manpower sama sekali (draft kosong atau baru dikosongkan),
        // tidak perlu memunculkan peringatan apapun
        if (empty($manpowerIds)) {
            return [
                'duplicate_warnings' => [],
                'skill_warnings' => [],
                'mp_warnings' => [],
                'operator_statuses' => [],
                'slot_statuses' => [],
            ];
        }

        $manpowersById = Manpower::whereIn('id', $manpowerIds)->get()->keyBy('id');

        $duplicateWarnings = [];
        $skillWarnings = [];
        $mpWarnings = [];
        $operatorStatuses = [];

        // 1. Deteksi duplikasi shift internal di line ini
        // Catatan: 1 operator memegang 2 station dalam shift yang SAMA adalah DIPERBOLEHKAN.
        // Duplikasi hanya terjadi jika 1 operator dijadwalkan di lebih dari 1 SHIFT BERBEDA dalam periode minggu ini.
        $operatorShifts = [];
        foreach ($gridEntries as $entry) {
            $shift = (int) ($entry['shift'] ?? 1);
            $mpId = !empty($entry['manpower_id']) ? (int) $entry['manpower_id'] : null;

            if (!$mpId) {
                continue;
            }

            if (!isset($operatorShifts[$mpId])) {
                $operatorShifts[$mpId] = [];
            }
            $operatorShifts[$mpId][$shift] = true;
        }

        foreach ($operatorShifts as $mpId => $shifts) {
            if (count($shifts) > 1) {
                $mp = $manpowersById[$mpId] ?? null;
                $mpName = $mp ? "{$mp->nama} ({$mp->badge_id})" : "Operator #{$mpId}";
                $shiftNames = implode(', ', array_map(fn($s) => "Shift {$s}", array_keys($shifts)));

                $msg = "Duplikasi Shift: {$mpName} dijadwalkan pada beberapa shift sekaligus di line ini ({$shiftNames}).";
                $duplicateWarnings[] = [
                    'manpower_id' => $mpId,
                    'badge_id' => $mp->badge_id ?? '-',
                    'operator_nama' => $mp->nama ?? "Operator #{$mpId}",
                    'type' => 'internal_shift_conflict',
                    'severity' => 'Tinggi',
                    'severity_level' => 'error',
                    'reference' => "Line {$line->nama} · " . $shiftNames,
                    'message' => $msg,
                ];

                foreach (array_keys($shifts) as $s) {
                    $key = "{$s}_{$mpId}";
                    $operatorStatuses[$key]['type'] = 'error';
                    $operatorStatuses[$key]['messages'][] = "Duplikasi jadwal shift di line ini";
                }
            }
        }

        // 2. Deteksi duplikasi lintas line pada minggu periode yang sama
        if (!empty($manpowerIds)) {
            $weekStartDate = Carbon::parse($weekStart)->format('Y-m-d');

            $crossLineEntries = RosterEntry::query()
                ->whereIn('manpower_id', $manpowerIds)
                ->whereHas('roster', function ($q) use ($weekStartDate, $currentRosterId, $lineId) {
                    $q->whereDate('minggu_mulai', $weekStartDate);
                    if ($currentRosterId) {
                        $q->where('id', '!=', $currentRosterId);
                    } else {
                        $q->where('line_id', '!=', $lineId);
                    }
                })
                ->with(['roster.line', 'station', 'manpower'])
                ->get();

            foreach ($crossLineEntries as $crossEntry) {
                $mp = $crossEntry->manpower;
                $otherLineName = $crossEntry->roster->line->nama ?? 'Line Lain';
                $otherShift = $crossEntry->shift;
                $otherStation = $crossEntry->station->nama ?? 'Station';

                $msg = "Duplikasi Lintas Line: {$mp->nama} ({$mp->badge_id}) juga telah terjadwal di {$otherLineName} (Shift {$otherShift}, Station {$otherStation}).";
                
                $duplicateWarnings[] = [
                    'manpower_id' => $crossEntry->manpower_id,
                    'badge_id' => $mp->badge_id ?? '-',
                    'operator_nama' => $mp->nama ?? '-',
                    'type' => 'cross_line_duplicate',
                    'severity' => 'Tinggi',
                    'severity_level' => 'error',
                    'line_name' => $otherLineName,
                    'shift' => $otherShift,
                    'station' => $otherStation,
                    'reference' => "{$otherLineName} · Shift {$otherShift} · {$otherStation}",
                    'message' => $msg,
                ];

                foreach ([1, 2, 3] as $s) {
                    $key = "{$s}_{$crossEntry->manpower_id}";
                    $operatorStatuses[$key]['type'] = 'error';
                    $operatorStatuses[$key]['messages'][] = "Terjadwal juga di {$otherLineName} Shift {$otherShift}";
                }
            }
        }

        // 3. Evaluasi kecocokan level stasiun (Skill Matrix Warning)
        $processedSkills = [];
        foreach ($gridEntries as $entry) {
            $shift = (int) ($entry['shift'] ?? 1);
            $stationId = (int) ($entry['station_id'] ?? 0);
            $mpId = !empty($entry['manpower_id']) ? (int) $entry['manpower_id'] : null;

            if (!$mpId || !$stationId) {
                continue;
            }

            $mp = $manpowersById[$mpId] ?? null;
            $station = $stationsById[$stationId] ?? null;

            if (!$mp || !$station) {
                continue;
            }

            $skillKey = "{$shift}_{$mpId}_{$stationId}";
            if (isset($processedSkills[$skillKey])) {
                continue;
            }
            $processedSkills[$skillKey] = true;

            $isRelevant = $mp->isRelevantToStation($station->nama);
            $opKey = "{$shift}_{$mpId}";

            if (!$isRelevant) {
                if ($station->level === 'Basic') {
                    // PRD 5.3: Info saja jika stasiun level Basic
                    $msg = "Info: {$mp->nama} di luar stasiun utamanya pada stasiun Basic ({$station->nama}).";
                    $skillWarnings[] = [
                        'type' => 'info',
                        'severity' => 'Rendah',
                        'severity_level' => 'info',
                        'station_id' => $stationId,
                        'manpower_id' => $mpId,
                        'badge_id' => $mp->badge_id ?? '-',
                        'operator_nama' => $mp->nama ?? '-',
                        'station_nama' => $station->nama,
                        'station_level' => $station->level,
                        'shift' => $shift,
                        'reference' => "Shift {$shift} · Station {$station->nama} ({$station->level})",
                        'message' => $msg,
                    ];
                    if (!isset($operatorStatuses[$opKey])) {
                        $operatorStatuses[$opKey] = ['type' => 'info', 'messages' => []];
                    }
                    $operatorStatuses[$opKey]['messages'][] = "Di luar stasiun utama ({$station->nama} - Basic)";
                } else {
                    // PRD 5.3: Warning jika stasiun level Basic-Trained atau Trained
                    $msg = "Peringatan Keahlian: {$mp->nama} belum terverifikasi relevan untuk stasiun {$station->level} ({$station->nama}).";
                    $skillWarnings[] = [
                        'type' => 'warning',
                        'severity' => 'Sedang',
                        'severity_level' => 'warning',
                        'station_id' => $stationId,
                        'manpower_id' => $mpId,
                        'badge_id' => $mp->badge_id ?? '-',
                        'operator_nama' => $mp->nama ?? '-',
                        'station_nama' => $station->nama,
                        'station_level' => $station->level,
                        'shift' => $shift,
                        'reference' => "Shift {$shift} · Station {$station->nama} ({$station->level})",
                        'message' => $msg,
                    ];
                    if (!isset($operatorStatuses[$opKey]) || $operatorStatuses[$opKey]['type'] !== 'error') {
                        $operatorStatuses[$opKey] = ['type' => 'warning', 'messages' => []];
                    }
                    $operatorStatuses[$opKey]['messages'][] = "Belum relevan untuk {$station->nama} ({$station->level})";
                }
            }
        }

        // 4. Evaluasi MP Standard per Shift (Dihitung dari jumlah operator UNIK dalam shift)
        // Hanya dievaluasi jika shift sedang aktif diisi (currentCount > 0) namun kurang dari MP Standard
        $shiftsInLine = $line->shift_count;
        for ($s = 1; $s <= $shiftsInLine; $s++) {
            $shiftOperators = [];
            foreach ($gridEntries as $entry) {
                if ((int) ($entry['shift'] ?? 0) === $s && !empty($entry['manpower_id'])) {
                    $shiftOperators[$entry['manpower_id']] = true;
                }
            }

            $currentCount = count($shiftOperators);
            if ($line->mp_standard > 0 && $currentCount > 0 && $currentCount < $line->mp_standard) {
                $deficit = $line->mp_standard - $currentCount;
                $mpWarnings[] = [
                    'shift' => $s,
                    'current' => $currentCount,
                    'standard' => $line->mp_standard,
                    'deficit' => $deficit,
                    'severity' => 'Sedang',
                    'severity_level' => 'warning',
                    'reference' => "Shift {$s} · {$currentCount} / {$line->mp_standard} MP (Kurang {$deficit} OP)",
                    'message' => "Peringatan MP: Shift {$s} terisi {$currentCount} operator, kurang dari MP Standard line ({$line->mp_standard} operator).",
                ];
            }
        }

        return [
            'duplicate_warnings' => $duplicateWarnings,
            'skill_warnings' => $skillWarnings,
            'mp_warnings' => $mpWarnings,
            'operator_statuses' => $operatorStatuses,
            'slot_statuses' => $operatorStatuses, // backward compatibility
        ];
    }
}
