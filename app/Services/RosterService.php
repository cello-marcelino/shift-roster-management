<?php

namespace App\Services;

use App\Models\Line;
use App\Models\Roster;
use App\Models\RosterEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RosterService
{
    protected RosterValidationService $validationService;

    public function __construct(RosterValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Mengambil atau membuat instance roster untuk line dan tanggal minggu tertentu.
     */
    public function getOrCreateRoster(int $lineId, string $weekStart, ?int $leaderId = null): Roster
    {
        $startDate = Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY);
        $endDate = (clone $startDate)->endOfWeek(Carbon::SUNDAY);

        $roster = Roster::with(['entries.station', 'entries.manpower', 'line.stations', 'leader'])
            ->where('line_id', $lineId)
            ->whereDate('minggu_mulai', $startDate->format('Y-m-d'))
            ->first();

        if (!$roster) {
            $defaultLeaderId = $leaderId ?? \App\Models\Leader::where('status', 'aktif')->value('id') ?? 1;

            $roster = Roster::create([
                'line_id' => $lineId,
                'leader_id' => $defaultLeaderId,
                'minggu_mulai' => $startDate->format('Y-m-d'),
                'minggu_selesai' => $endDate->format('Y-m-d'),
                'status' => 'draft',
            ]);

            $roster->load(['entries.station', 'entries.manpower', 'line.stations', 'leader']);
        }

        return $roster;
    }

    /**
     * Menyimpan seluruh entri slot jadwal roster dalam satu transaksi DB.
     *
     * @param int $rosterId
     * @param int $leaderId
     * @param array $gridEntries [ ['shift' => 1, 'station_id' => 5, 'manpower_id' => 12], ... ]
     * @param string $status 'draft' atau 'final'
     */
    public function saveRosterEntries(int $rosterId, int $leaderId, array $gridEntries, string $status = 'draft'): Roster
    {
        return DB::transaction(function () use ($rosterId, $leaderId, $gridEntries, $status) {
            $roster = Roster::with('line')->findOrFail($rosterId);
            $roster->leader_id = $leaderId;
            $roster->status = $status;
            $roster->save();

            $line = $roster->line;
            $effHours = $line ? ($line->effective_shift_hours ?? []) : [];

            // Hapus entri lama dan masukkan entri yang baru
            $roster->entries()->delete();

            $entriesToInsert = [];
            $now = now();

            foreach ($gridEntries as $entry) {
                $shift = (int) ($entry['shift'] ?? 1);
                $stationId = (int) ($entry['station_id'] ?? 0);
                $mpId = !empty($entry['manpower_id']) ? (int) $entry['manpower_id'] : null;

                if ($mpId) {
                    $rawStart = !empty($entry['start_shift']) ? trim($entry['start_shift']) : null;
                    $rawEnd = !empty($entry['end_shift']) ? trim($entry['end_shift']) : null;

                    $startShift = null;
                    $endShift = null;

                    if ($rawStart !== null || $rawEnd !== null) {
                        $defaultStart = '07:00';
                        $defaultEnd = '16:00';
                        if (!empty($effHours[$shift]['hours']) && str_contains($effHours[$shift]['hours'], '-')) {
                            $hParts = explode('-', $effHours[$shift]['hours']);
                            $defaultStart = trim(str_replace('.', ':', $hParts[0]));
                            $defaultEnd = trim(str_replace('.', ':', $hParts[1]));
                        }

                        $startShift = $rawStart ?: $defaultStart;
                        $endShift = $rawEnd ?: $defaultEnd;
                    }

                    $entriesToInsert[] = [
                        'roster_id' => $rosterId,
                        'hari' => 1,
                        'shift' => $shift,
                        'station_id' => $stationId ?: null,
                        'manpower_id' => $mpId,
                        'start_shift' => $startShift,
                        'end_shift' => $endShift,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (!empty($entriesToInsert)) {
                RosterEntry::insert($entriesToInsert);
            }

            return $roster->fresh(['entries.station', 'entries.manpower', 'line.stations', 'leader']);
        });
    }

    /**
     * Menyalin jadwal dari roster minggu sebelumnya (Clone Previous Week)
     */
    public function copyFromPreviousWeek(int $lineId, string $targetWeekStart, int $currentRosterId, bool $applyRotation = false): array
    {
        $targetMonday = Carbon::parse($targetWeekStart)->startOfWeek(Carbon::MONDAY);
        $prevMonday = (clone $targetMonday)->subWeek()->format('Y-m-d');

        $previousRoster = Roster::with('entries')
            ->where('line_id', $lineId)
            ->whereDate('minggu_mulai', $prevMonday)
            ->first();

        if (!$previousRoster || $previousRoster->entries->isEmpty()) {
            return [
                'success' => false,
                'message' => "Tidak ditemukan jadwal roster pada minggu sebelumnya ({$prevMonday}).",
                'assignments' => [],
                'entries' => [],
            ];
        }

        $line = Line::findOrFail($lineId);
        $shiftCount = $line->shift_count;

        $copiedAssignments = [];
        $copiedShiftTimes = [];
        $copiedEntries = [];

        for ($s = 1; $s <= $shiftCount; $s++) {
            $copiedAssignments[$s] = [];
            $copiedShiftTimes[$s] = [];
        }

        foreach ($previousRoster->entries as $entry) {
            $shift = $entry->shift;

            // Jika rotasi shift diterapkan (PRD 5.2: rotasi mundur)
            if ($applyRotation) {
                if ($shiftCount === 3) {
                    if ($shift === 1) {
                        $shift = 3;
                    } elseif ($shift === 3) {
                        $shift = 2;
                    } elseif ($shift === 2) {
                        $shift = 1;
                    }
                } elseif ($shiftCount === 2) {
                    $shift = $shift === 1 ? 2 : 1;
                } else {
                    $shift = 1;
                }
            }

            $mpId = $entry->manpower_id;
            $stId = $entry->station_id;

            if (!isset($copiedAssignments[$shift][$mpId])) {
                $copiedAssignments[$shift][$mpId] = [];
            }
            if ($stId && !in_array($stId, $copiedAssignments[$shift][$mpId], true)) {
                $copiedAssignments[$shift][$mpId][] = $stId;
            }

            if (!isset($copiedShiftTimes[$shift][$mpId])) {
                $copiedShiftTimes[$shift][$mpId] = [
                    'start' => $entry->start_shift ?? '',
                    'end' => $entry->end_shift ?? '',
                ];
            }

            $copiedEntries[] = [
                'shift' => $shift,
                'station_id' => $stId,
                'manpower_id' => $mpId,
                'start_shift' => $entry->start_shift,
                'end_shift' => $entry->end_shift,
            ];
        }

        return [
            'success' => true,
            'message' => "Berhasil menyalin penempatan dari minggu sebelumnya.",
            'assignments' => $copiedAssignments,
            'shift_times' => $copiedShiftTimes,
            'entries' => $copiedEntries,
        ];
    }

    /**
     * Mengambil ringkasan jadwal seluruh line pada minggu tertentu untuk cross-line checking
     */
    public function getCrossLineSummary(string $weekStart): Collection
    {
        $startDate = Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        return Roster::whereDate('minggu_mulai', $startDate)
            ->with(['line', 'leader', 'entries.manpower', 'entries.station'])
            ->get();
    }

    /**
     * Fitur Isi Cepat: Mengisi slot roster secara otomatis dengan operator aktif yang tersedia.
     * Mengutamakan stasiun berkeahlian tinggi (Trained & Basic-Trained) terlebih dahulu,
     * serta menghindari duplikasi badge ID antar shift dan lintas line.
     */
    public function autoFillRoster(
        int $lineId,
        string $weekStart,
        array $currentAssignments = [],
        bool $onlyEmpty = true,
        ?int $currentRosterId = null
    ): array {
        $line = Line::with(['stations' => function ($q) {
            $q->orderByRaw("CASE WHEN level = 'Trained' THEN 1 WHEN level = 'Basic-Trained' THEN 2 ELSE 3 END");
        }])->findOrFail($lineId);

        $shiftCount = $line->shift_count;
        $stations = $line->stations;

        // Ambil seluruh operator aktif
        $allActiveOperators = \App\Models\Manpower::where('status', 'aktif')->get();

        // Cari operator yang sudah terjadwal di line lain pada minggu ini
        $weekStartDate = Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $occupiedInOtherLines = RosterEntry::whereHas('roster', function ($q) use ($weekStartDate, $currentRosterId, $lineId) {
            $q->whereDate('minggu_mulai', $weekStartDate);
            if ($currentRosterId) {
                $q->where('id', '!=', $currentRosterId);
            } else {
                $q->where('line_id', '!=', $lineId);
            }
        })->pluck('manpower_id')->unique()->flip()->toArray();

        // Inisialisasi struktur shiftAssignments
        $assignments = [];
        $usedInThisRoster = [];

        for ($s = 1; $s <= $shiftCount; $s++) {
            $assignments[$s] = [];
            if ($onlyEmpty && isset($currentAssignments[$s])) {
                $assignments[$s] = $currentAssignments[$s];
                foreach (array_keys($assignments[$s]) as $mpId) {
                    $usedInThisRoster[(int) $mpId] = true;
                }
            }
        }

        $filledCount = 0;

        for ($s = 1; $s <= $shiftCount; $s++) {
            foreach ($stations as $station) {
                // Cek apakah stasiun ini sudah di-handle oleh operator di shift ini
                $stationAlreadyHandled = false;
                if ($onlyEmpty) {
                    foreach ($assignments[$s] as $mpId => $stIds) {
                        if (in_array($station->id, $stIds, true)) {
                            $stationAlreadyHandled = true;
                            break;
                        }
                    }
                }

                if ($onlyEmpty && $stationAlreadyHandled) {
                    continue;
                }

                $chosenCandidate = null;

                // Prioritas 1: Operator aktif bebas, bukan di line lain, relevan dengan stasiun
                foreach ($allActiveOperators as $operator) {
                    $opId = $operator->id;
                    if (isset($usedInThisRoster[$opId]) || isset($occupiedInOtherLines[$opId])) {
                        continue;
                    }
                    if ($operator->isRelevantToStation($station->nama)) {
                        $chosenCandidate = $operator;
                        break;
                    }
                }

                // Prioritas 2: Jika stasiun Basic dan belum dapat, pilih operator bebas mana pun
                if (!$chosenCandidate && $station->level === 'Basic') {
                    foreach ($allActiveOperators as $operator) {
                        $opId = $operator->id;
                        if (!isset($usedInThisRoster[$opId]) && !isset($occupiedInOtherLines[$opId])) {
                            $chosenCandidate = $operator;
                            break;
                        }
                    }
                }

                // Prioritas 3: Fallback ke operator bebas relevan
                if (!$chosenCandidate) {
                    foreach ($allActiveOperators as $operator) {
                        $opId = $operator->id;
                        if (!isset($usedInThisRoster[$opId]) && !isset($occupiedInOtherLines[$opId]) && $operator->isRelevantToStation($station->nama)) {
                            $chosenCandidate = $operator;
                            break;
                        }
                    }
                }

                // Prioritas 4: Fallback terakhir operator bebas
                if (!$chosenCandidate) {
                    foreach ($allActiveOperators as $operator) {
                        $opId = $operator->id;
                        if (!isset($usedInThisRoster[$opId]) && !isset($occupiedInOtherLines[$opId])) {
                            $chosenCandidate = $operator;
                            break;
                        }
                    }
                }

                if ($chosenCandidate) {
                    $opId = $chosenCandidate->id;
                    if (!isset($assignments[$s][$opId])) {
                        $assignments[$s][$opId] = [];
                    }
                    if (!in_array($station->id, $assignments[$s][$opId], true)) {
                        $assignments[$s][$opId][] = $station->id;
                    }
                    $usedInThisRoster[$opId] = true;
                    $filledCount++;
                }
            }
        }

        // Susun daftar entri datar
        $entries = [];
        $legacyGrid = [];
        foreach ($assignments as $s => $operators) {
            foreach ($operators as $mpId => $stIds) {
                foreach ($stIds as $stId) {
                    $entries[] = [
                        'shift' => $s,
                        'manpower_id' => $mpId,
                        'station_id' => $stId,
                    ];
                    $legacyGrid["{$s}_{$stId}"] = $mpId;
                }
            }
        }

        return [
            'assignments' => $assignments,
            'entries' => $entries,
            'grid' => $legacyGrid,
            'filled_count' => $filledCount,
            'message' => $filledCount > 0 
                ? "Berhasil mengisi otomatis {$filledCount} penugasan stasiun pada operator aktif."
                : "Semua stasiun sudah memiliki penugasan operator.",
        ];
    }

    /**
     * Fitur Isi Cepat: Mengisi Manpower saja (tanpa station).
     * Memasukkan operator aktif ke dalam shift hingga memenuhi standar MP line,
     * tanpa memasangkan stasiun kerja (stasiun dikosongkan agar dipilih manual lewat dropdown).
     *
     * @param int $lineId
     * @param string $weekStart
     * @param array $currentAssignments [ shift => [ mpId => [stationIds...] ] ]
     * @param int|null $targetShift Jika ditentukan, hanya isi shift tersebut; jika null, isi seluruh shift line.
     * @param int|null $currentRosterId
     * @return array
     */
    public function autoFillManpowerOnly(
        int $lineId,
        string $weekStart,
        array $currentAssignments = [],
        ?int $targetShift = null,
        ?int $currentRosterId = null
    ): array {
        $line = Line::with('stations')->findOrFail($lineId);
        $shiftCount = $line->shift_count;
        $mpStandard = $line->mp_standard > 0 ? $line->mp_standard : $line->stations->count();

        // Ambil seluruh operator aktif
        $allActiveOperators = \App\Models\Manpower::where('status', 'aktif')
            ->orderBy('nama', 'asc')
            ->get();

        // Cari operator yang sudah terjadwal di line lain pada minggu ini
        $weekStartDate = Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $occupiedInOtherLines = RosterEntry::whereHas('roster', function ($q) use ($weekStartDate, $currentRosterId, $lineId) {
            $q->whereDate('minggu_mulai', $weekStartDate);
            if ($currentRosterId) {
                $q->where('id', '!=', $currentRosterId);
            } else {
                $q->where('line_id', '!=', $lineId);
            }
        })->pluck('manpower_id')->unique()->flip()->toArray();

        $assignments = [];
        $usedInThisRoster = [];

        // Catat operator yang sudah ada di line ini
        for ($s = 1; $s <= $shiftCount; $s++) {
            $assignments[$s] = $currentAssignments[$s] ?? [];
            foreach (array_keys($assignments[$s]) as $mpId) {
                $usedInThisRoster[(int) $mpId] = true;
            }
        }

        $shiftsToProcess = $targetShift ? [$targetShift] : range(1, $shiftCount);
        $addedCount = 0;

        foreach ($shiftsToProcess as $s) {
            if ($s > $shiftCount) {
                continue;
            }

            $currentCountInShift = count($assignments[$s]);
            $needed = max(0, $mpStandard - $currentCountInShift);

            if ($needed <= 0) {
                continue;
            }

            $addedInThisShift = 0;

            // Prioritas 1: Operator aktif bebas, belum di line ini dan belum di line lain
            foreach ($allActiveOperators as $operator) {
                if ($addedInThisShift >= $needed) {
                    break;
                }

                $opId = $operator->id;
                if (isset($usedInThisRoster[$opId]) || isset($occupiedInOtherLines[$opId])) {
                    continue;
                }

                $assignments[$s][$opId] = [];
                $usedInThisRoster[$opId] = true;
                $addedInThisShift++;
                $addedCount++;
            }

            // Prioritas 2 (Fallback): Operator bebas yang belum di line ini
            if ($addedInThisShift < $needed) {
                foreach ($allActiveOperators as $operator) {
                    if ($addedInThisShift >= $needed) {
                        break;
                    }

                    $opId = $operator->id;
                    if (isset($usedInThisRoster[$opId])) {
                        continue;
                    }

                    $assignments[$s][$opId] = [];
                    $usedInThisRoster[$opId] = true;
                    $addedInThisShift++;
                    $addedCount++;
                }
            }
        }

        return [
            'assignments' => $assignments,
            'added_count' => $addedCount,
            'message' => $addedCount > 0 
                ? "Berhasil menambahkan {$addedCount} operator aktif ke shift (tanpa stasiun). Silakan pilih stasiun via dropdown."
                : "Jumlah operator di shift sudah memenuhi standar MP ({$mpStandard} orang).",
        ];
    }
}
