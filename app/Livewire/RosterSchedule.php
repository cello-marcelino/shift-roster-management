<?php

namespace App\Livewire;

use App\Models\Leader;
use App\Models\Line;
use App\Models\Manpower;
use App\Models\Roster;
use App\Models\RosterEntry;
use App\Models\Station;
use App\Services\MasterDataService;
use App\Services\RosterService;
use App\Services\RosterValidationService;
use Carbon\Carbon;
use Livewire\Component;

class RosterSchedule extends Component
{
    // Filter & Scope state
    public ?int $selectedLineId = null;
    public string $weekStart = '';
    public ?int $selectedLeaderId = null;

    // Roster model state
    public ?int $currentRosterId = null;
    public string $rosterStatus = 'draft';

    // Active shift tab (0 for All Shifts side-by-side, or 1, 2, 3 for single shift)
    public int $activeShift = 0;

    // Data Penugasan Operator:
    // Format: $shiftAssignments[shift][manpower_id] = [ station_id_1, station_id_2, ... ]
    // Memungkinkan 1 operator memegang 2+ station, dan 1 station dijalankan oleh 2+ operator.
    public array $shiftAssignments = [
        1 => [],
        2 => [],
        3 => [],
    ];

    // Data Penyesuaian Waktu Kerja (Start Shift & End Shift per Operator per Shift):
    // Format: $shiftTimes[shift][manpower_id] = [ 'start' => '07.00', 'end' => '16.00' ]
    public array $shiftTimes = [
        1 => [],
        2 => [],
        3 => [],
    ];

    // Dirty state tracking (membedakan draft belum disimpan vs tersimpan)
    public string $lastSavedHash = '';

    // Validation state
    public array $validationResults = [
        'duplicate_warnings' => [],
        'skill_warnings' => [],
        'mp_warnings' => [],
        'operator_statuses' => [],
    ];
    public bool $showWarnings = true;

    // Modal state: Tambah operator ke shift
    public bool $showAddOperatorModal = false;
    public int $targetShiftForAdd = 1;
    public string $operatorSearch = '';

    // Modal state: Cross-line overview
    public bool $showCrossLineModal = false;

    // Modal state: Edit Line Quick Button
    public bool $showEditLineModal = false;
    public string $editLineNama = '';
    public int $editLineMpStandard = 15;
    public string $editLinePolaKerja = '5_days_2_shift';
    public string $editLineStatus = 'aktif';
    public string $editLineShift1Start = '07.00';
    public string $editLineShift1End = '16.00';
    public string $editLineShift2Start = '16.00';
    public string $editLineShift2End = '01.00';
    public string $editLineShift3Start = '23.00';
    public string $editLineShift3End = '07.00';

    // Feedback banner
    public string $feedbackMessage = '';
    public string $errorMessage = '';
    public int $flashCounter = 0;

    public function setFeedback(string $message): void
    {
        $this->feedbackMessage = $message;
        $this->errorMessage = '';
        $this->flashCounter++;
    }

    public function setError(string $message): void
    {
        $this->errorMessage = $message;
        $this->feedbackMessage = '';
        $this->flashCounter++;
    }

    public function mount(RosterService $rosterService): void
    {
        $this->weekStart = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $firstLine = Line::where('status', 'aktif')->orderBy('nama', 'asc')->first() ?? Line::first();
        if ($firstLine) {
            $this->selectedLineId = $firstLine->id;
        }

        $firstLeader = Leader::where('status', 'aktif')->first();
        if ($firstLeader) {
            $this->selectedLeaderId = $firstLeader->id;
        }

        $this->loadRoster($rosterService);
    }

    public function updatedSelectedLineId(RosterService $rosterService): void
    {
        $this->loadRoster($rosterService);
    }

    public function updatedWeekStart(RosterService $rosterService): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $this->loadRoster($rosterService);
    }

    public function setActiveShift(int $shift): void
    {
        $this->activeShift = $shift;
    }

    /**
     * Navigasi langsung ke line tertentu dari modal rekap untuk mengeditnya
     */
    public function navigateToLine(int $lineId, RosterService $rosterService): void
    {
        $this->selectedLineId = $lineId;
        $this->showCrossLineModal = false;
        $this->loadRoster($rosterService);

        $line = Line::find($lineId);
        if ($line) {
            $this->setFeedback("Beralih ke Line '{$line->nama}'. Anda dapat langsung mengedit jadwal roster ini.");
        }
    }

    /**
     * Memuat atau inisialisasi data roster dari database
     */
    public function loadRoster(RosterService $rosterService): void
    {
        if (!$this->selectedLineId) {
            return;
        }

        $this->feedbackMessage = '';
        $this->errorMessage = '';

        $roster = $rosterService->getOrCreateRoster(
            $this->selectedLineId,
            $this->weekStart,
            $this->selectedLeaderId
        );

        $this->currentRosterId = $roster->id;
        $this->rosterStatus = $roster->status;
        $this->selectedLeaderId = $roster->leader_id;

        // Inisialisasi struktur shiftAssignments & shiftTimes
        $newAssignments = [1 => [], 2 => [], 3 => []];
        $newShiftTimes = [1 => [], 2 => [], 3 => []];

        foreach ($roster->entries as $entry) {
            $s = (int) $entry->shift;
            $mpId = (int) $entry->manpower_id;
            $stId = $entry->station_id ? (int) $entry->station_id : null;

            if (!isset($newAssignments[$s][$mpId])) {
                $newAssignments[$s][$mpId] = [];
            }
            if ($stId && !in_array($stId, $newAssignments[$s][$mpId], true)) {
                $newAssignments[$s][$mpId][] = $stId;
            }

            if (!isset($newShiftTimes[$s][$mpId])) {
                $newShiftTimes[$s][$mpId] = [
                    'start' => $this->normalizeTime($entry->start_shift),
                    'end' => $this->normalizeTime($entry->end_shift),
                ];
            }
        }

        $this->shiftAssignments = $newAssignments;
        $this->shiftTimes = $newShiftTimes;
        $this->lastSavedHash = $this->calculateAssignmentsHash();
        $this->runValidation();
    }

    /**
     * Memastikan string waktu hanya dalam format waktu yang valid HH:mm atau HH.mm, selain itu dikosongkan
     */
    public function normalizeTime(?string $time): string
    {
        if (empty($time)) {
            return '';
        }
        $t = trim(str_replace('.', ':', $time));
        if (preg_match('/^([0-1]?[0-9]|2[0-3]):([0-5][0-9])$/', $t, $matches)) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }
        return '';
    }

    /**
     * Mengubah array shiftAssignments menjadi daftar entri datar untuk validasi & penyimpanan
     */
    protected function getFlatEntries(): array
    {
        $currentLine = $this->selectedLineId ? Line::find($this->selectedLineId) : null;
        $effHours = $currentLine ? ($currentLine->effective_shift_hours ?? []) : [];

        $entries = [];
        foreach ($this->shiftAssignments as $shift => $operators) {
            $defaultStart = '07:00';
            $defaultEnd = '16:00';
            if (!empty($effHours[$shift]['hours']) && str_contains($effHours[$shift]['hours'], '-')) {
                $hParts = explode('-', $effHours[$shift]['hours']);
                $defaultStart = $this->normalizeTime($hParts[0] ?? '07:00');
                $defaultEnd = $this->normalizeTime($hParts[1] ?? '16:00');
            }

            foreach ($operators as $mpId => $stationIds) {
                $customStart = $this->normalizeTime($this->shiftTimes[$shift][$mpId]['start'] ?? null);
                $customEnd = $this->normalizeTime($this->shiftTimes[$shift][$mpId]['end'] ?? null);

                $finalStart = null;
                $finalEnd = null;

                if (!empty($customStart) || !empty($customEnd)) {
                    $finalStart = !empty($customStart) ? $customStart : $defaultStart;
                    $finalEnd = !empty($customEnd) ? $customEnd : $defaultEnd;
                }

                if (empty($stationIds)) {
                    // Operator tercatat di shift tapi belum memilih stasiun
                    $entries[] = [
                        'shift' => (int) $shift,
                        'station_id' => 0,
                        'manpower_id' => (int) $mpId,
                        'start_shift' => $finalStart,
                        'end_shift' => $finalEnd,
                    ];
                } else {
                    foreach ($stationIds as $stId) {
                        $entries[] = [
                            'shift' => (int) $shift,
                            'station_id' => (int) $stId,
                            'manpower_id' => (int) $mpId,
                            'start_shift' => $finalStart,
                            'end_shift' => $finalEnd,
                        ];
                    }
                }
            }
        }
        return $entries;
    }

    /**
     * Menjalankan validasi real-time pada penugasan saat ini
     */
    public function runValidation(): void
    {
        if (!$this->selectedLineId) {
            return;
        }

        $entries = $this->getFlatEntries();

        $validator = app(RosterValidationService::class);
        $this->validationResults = $validator->validateSchedule(
            $this->selectedLineId,
            $this->weekStart,
            $entries,
            $this->currentRosterId
        );
    }

    /**
     * Menampilkan atau menyembunyikan panel peringatan
     */
    public function toggleWarnings(): void
    {
        $this->showWarnings = !$this->showWarnings;
    }

    /**
     * Buka modal untuk menambahkan operator ke shift aktif
     */
    public function openAddOperatorModal(int $shift): void
    {
        $this->targetShiftForAdd = $shift;
        $this->operatorSearch = '';
        $this->showAddOperatorModal = true;
    }

    /**
     * Menambahkan operator ke dalam shift dengan proteksi mutlak dari duplikasi shift maupun lintas line
     */
    public function addOperatorToShift(int $manpowerId): void
    {
        $s = $this->targetShiftForAdd;

        // 1. Cek apakah operator sudah ada di shift manapun pada line ini
        foreach ($this->shiftAssignments as $existingShift => $ops) {
            if (isset($ops[$manpowerId])) {
                $op = Manpower::find($manpowerId);
                $opName = $op ? "{$op->nama} ({$op->badge_id})" : "Operator #{$manpowerId}";
                $this->setError("Operator {$opName} sudah ditugaskan pada Shift {$existingShift} di line ini.");
                $this->showAddOperatorModal = false;
                return;
            }
        }

        // 2. Cek apakah operator sudah ada di line lain pada minggu ini
        $weekStartDate = Carbon::parse($this->weekStart)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $otherLineEntry = RosterEntry::where('manpower_id', $manpowerId)
            ->whereHas('roster', function ($q) use ($weekStartDate) {
                $q->whereDate('minggu_mulai', $weekStartDate);
                if ($this->currentRosterId) {
                    $q->where('id', '!=', $this->currentRosterId);
                } else if ($this->selectedLineId) {
                    $q->where('line_id', '!=', $this->selectedLineId);
                }
            })
            ->with(['roster.line'])
            ->first();

        if ($otherLineEntry) {
            $op = Manpower::find($manpowerId);
            $opName = $op ? "{$op->nama} ({$op->badge_id})" : "Operator #{$manpowerId}";
            $otherLineName = $otherLineEntry->roster->line->nama ?? 'Line Lain';
            $this->setError("Operator {$opName} sudah ditugaskan di {$otherLineName} (Shift {$otherLineEntry->shift}).");
            $this->showAddOperatorModal = false;
            return;
        }

        if (!isset($this->shiftAssignments[$s])) {
            $this->shiftAssignments[$s] = [];
        }

        if (!isset($this->shiftAssignments[$s][$manpowerId])) {
            $this->shiftAssignments[$s][$manpowerId] = [];
        }

        if (!isset($this->shiftTimes[$s][$manpowerId])) {
            $this->shiftTimes[$s][$manpowerId] = [
                'start' => '',
                'end' => '',
            ];
        }

        $this->errorMessage = '';
        $this->showAddOperatorModal = false;
        $this->runValidation();
    }

    /**
     * Menghapus operator dari shift
     */
    public function removeOperatorFromShift(int $shift, int $manpowerId): void
    {
        if (isset($this->shiftAssignments[$shift][$manpowerId])) {
            unset($this->shiftAssignments[$shift][$manpowerId]);
        }
        if (isset($this->shiftTimes[$shift][$manpowerId])) {
            unset($this->shiftTimes[$shift][$manpowerId]);
        }
        $this->runValidation();
    }

    /**
     * Menambahkan penugasan stasiun ke operator (via Dropdown)
     */
    public function addStationToOperator(int $shift, int $manpowerId, int $stationId): void
    {
        if (!$stationId) {
            return;
        }

        if (!isset($this->shiftAssignments[$shift][$manpowerId])) {
            $this->shiftAssignments[$shift][$manpowerId] = [];
        }

        if (!in_array($stationId, $this->shiftAssignments[$shift][$manpowerId], true)) {
            $this->shiftAssignments[$shift][$manpowerId][] = $stationId;
        }

        $this->runValidation();
    }

    /**
     * Melepas stasiun tertentu dari operator
     */
    public function removeStationFromOperator(int $shift, int $manpowerId, int $stationId): void
    {
        if (isset($this->shiftAssignments[$shift][$manpowerId])) {
            $this->shiftAssignments[$shift][$manpowerId] = array_values(
                array_diff($this->shiftAssignments[$shift][$manpowerId], [$stationId])
            );
            $this->runValidation();
        }
    }

    /**
     * Mengatur stasiun utama (mengganti stasiun pertama atau menambah jika belum ada)
     */
    public function setPrimaryStation(int $shift, int $manpowerId, int $stationId): void
    {
        if (!$stationId) {
            return;
        }

        if (!isset($this->shiftAssignments[$shift][$manpowerId])) {
            $this->shiftAssignments[$shift][$manpowerId] = [];
        }

        $current = $this->shiftAssignments[$shift][$manpowerId];
        if (empty($current)) {
            $this->shiftAssignments[$shift][$manpowerId] = [$stationId];
        } else {
            // Ganti elemen pertama dengan station baru
            $others = array_diff($current, [$current[0]]);
            if (!in_array($stationId, $others, true)) {
                $this->shiftAssignments[$shift][$manpowerId] = array_merge([$stationId], array_values($others));
            }
        }

        $this->runValidation();
    }

    /**
     * Fitur Isi Cepat: Mengisi jadwal secara otomatis
     */
    public function quickFill(RosterService $rosterService, bool $onlyEmpty = false): void
    {
        if (!$this->selectedLineId) {
            return;
        }

        $result = $rosterService->autoFillRoster(
            $this->selectedLineId,
            $this->weekStart,
            $this->shiftAssignments,
            $onlyEmpty,
            $this->currentRosterId
        );

        $this->shiftAssignments = $result['assignments'];
        $this->setFeedback($result['message']);

        $this->runValidation();
    }

    /**
     * Fitur Isi Cepat: Mengisi operator/manpower saja tanpa stasiun (stasiun dipilih manual kemudian)
     */
    public function quickFillManpowerOnly(?int $targetShift = null, ?RosterService $rosterService = null): void
    {
        if (!$this->selectedLineId) {
            return;
        }

        $rosterService = $rosterService ?? app(RosterService::class);
        $shiftToFill = $targetShift ?? ($this->activeShift > 0 ? $this->activeShift : null);

        $result = $rosterService->autoFillManpowerOnly(
            $this->selectedLineId,
            $this->weekStart,
            $this->shiftAssignments,
            $shiftToFill,
            $this->currentRosterId
        );

        $this->shiftAssignments = $result['assignments'];
        $this->setFeedback($result['message']);

        $this->runValidation();
    }

    /**
     * Mengosongkan seluruh slot jadwal pada roster ini
     */
    public function clearAllSlots(): void
    {
        $this->shiftAssignments = [1 => [], 2 => [], 3 => []];
        $this->shiftTimes = [1 => [], 2 => [], 3 => []];
        $this->setFeedback('Seluruh penugasan jadwal pada line ini telah dikosongkan.');
        $this->runValidation();
    }

    /**
     * Menyimpan roster sebagai Draft
     */
    public function saveDraft(RosterService $rosterService): void
    {
        $this->saveRoster($rosterService, 'draft');
        $this->setFeedback('Jadwal roster berhasil disimpan sebagai DRAFT.');
    }

    /**
     * Menetapkan status roster menjadi FINAL
     */
    public function setFinal(RosterService $rosterService): void
    {
        $this->saveRoster($rosterService, 'final');
        $this->setFeedback('Jadwal roster berhasil ditetapkan sebagai FINAL dan siap diekspor ke PDF.');
    }

    /**
     * Mengembalikan status roster menjadi Draft
     */
    public function reopenDraft(RosterService $rosterService): void
    {
        $this->saveRoster($rosterService, 'draft');
        $this->setFeedback('Status roster dikembalikan ke DRAFT untuk pengubahan.');
    }

    protected function saveRoster(RosterService $rosterService, string $status): void
    {
        if (!$this->currentRosterId || !$this->selectedLeaderId) {
            return;
        }

        $entries = $this->getFlatEntries();

        $roster = $rosterService->saveRosterEntries(
            $this->currentRosterId,
            $this->selectedLeaderId,
            $entries,
            $status
        );

        $this->rosterStatus = $roster->status;
        $this->lastSavedHash = $this->calculateAssignmentsHash();
        $this->runValidation();
    }

    /**
     * Menghitung hash deterministik dari penugasan operator, waktu shift khusus, dan leader
     */
    public function calculateAssignmentsHash(): string
    {
        $normalized = [];
        foreach ($this->shiftAssignments as $s => $ops) {
            $normalizedOps = [];
            foreach ($ops as $mpId => $stIds) {
                $cleaned = array_map('intval', (array) $stIds);
                sort($cleaned);
                $normalizedOps[(int) $mpId] = $cleaned;
            }
            ksort($normalizedOps);
            $normalized[(int) $s] = $normalizedOps;
        }
        ksort($normalized);

        $normalizedTimes = [];
        foreach ($this->shiftTimes as $s => $ops) {
            $normalizedOpTimes = [];
            foreach ($ops as $mpId => $times) {
                $normalizedOpTimes[(int) $mpId] = [
                    'start' => $this->normalizeTime($times['start'] ?? ''),
                    'end' => $this->normalizeTime($times['end'] ?? ''),
                ];
            }
            ksort($normalizedOpTimes);
            $normalizedTimes[(int) $s] = $normalizedOpTimes;
        }
        ksort($normalizedTimes);

        return md5(json_encode([
            'assignments' => $normalized,
            'shift_times' => $normalizedTimes,
            'leader_id' => (int) $this->selectedLeaderId,
        ]));
    }

    /**
     * Mengecek apakah ada perubahan jadwal atau leader yang belum disimpan ke database
     */
    public function getHasUnsavedChangesProperty(): bool
    {
        if ($this->lastSavedHash === '') {
            return false;
        }

        return $this->lastSavedHash !== $this->calculateAssignmentsHash();
    }

    /**
     * Menyalin jadwal dari minggu sebelumnya
     */
    public function copyFromPreviousWeek(RosterService $rosterService, bool $applyRotation = false): void
    {
        if (!$this->selectedLineId || !$this->currentRosterId) {
            return;
        }

        $result = $rosterService->copyFromPreviousWeek(
            $this->selectedLineId,
            $this->weekStart,
            $this->currentRosterId,
            $applyRotation
        );

        if (!$result['success']) {
            $this->setError($result['message']);
            return;
        }

        $this->shiftAssignments = $result['assignments'];
        if (isset($result['shift_times'])) {
            $this->shiftTimes = $result['shift_times'];
        }
        $this->setFeedback($result['message'] . ($applyRotation ? ' (Rotasi shift diterapkan).' : ''));
        $this->runValidation();
    }

    /**
     * Membuka modal edit line yang sedang aktif dipilih
     */
    public function openEditLineModal(): void
    {
        if (!$this->selectedLineId) {
            return;
        }

        $line = Line::find($this->selectedLineId);
        if (!$line) {
            return;
        }

        $this->resetValidation();
        $this->editLineNama = $line->nama;
        $this->editLineMpStandard = $line->mp_standard;
        $this->editLinePolaKerja = $line->pola_kerja;
        $this->editLineStatus = $line->status;

        $hours = $line->effective_shift_hours;
        if (isset($hours[1]['hours'])) {
            $parts = explode('-', $hours[1]['hours']);
            $this->editLineShift1Start = trim($parts[0] ?? '07.00');
            $this->editLineShift1End = trim($parts[1] ?? '16.00');
        }
        if (isset($hours[2]['hours'])) {
            $parts = explode('-', $hours[2]['hours']);
            $this->editLineShift2Start = trim($parts[0] ?? '16.00');
            $this->editLineShift2End = trim($parts[1] ?? '01.00');
        }
        if (isset($hours[3]['hours'])) {
            $parts = explode('-', $hours[3]['hours']);
            $this->editLineShift3Start = trim($parts[0] ?? '23.00');
            $this->editLineShift3End = trim($parts[1] ?? '07.00');
        } else {
            $this->editLineShift3Start = '23.00';
            $this->editLineShift3End = '07.00';
        }

        $this->showEditLineModal = true;
    }

    /**
     * Menyimpan perubahan data line dari modal edit line
     */
    public function saveEditedLine(MasterDataService $masterService): void
    {
        if (!$this->selectedLineId) {
            return;
        }

        $this->validate([
            'editLineNama' => 'required|string|max:50|unique:lines,nama,' . $this->selectedLineId,
            'editLineMpStandard' => 'required|integer|min:1',
            'editLinePolaKerja' => 'required|string',
            'editLineStatus' => 'required|in:aktif,off,waiting_material',
        ]);

        if ($this->editLinePolaKerja === '6_days_3_shift') {
            $jamShift = [
                1 => ['label' => 'Shift 1', 'hours' => "{$this->editLineShift1Start} - {$this->editLineShift1End}"],
                2 => ['label' => 'Shift 2', 'hours' => "{$this->editLineShift2Start} - {$this->editLineShift2End}"],
                3 => ['label' => 'Shift 3', 'hours' => "{$this->editLineShift3Start} - {$this->editLineShift3End}"],
            ];
        } else {
            $jamShift = [
                1 => ['label' => 'Shift 1', 'hours' => "{$this->editLineShift1Start} - {$this->editLineShift1End}"],
                2 => ['label' => 'Shift 2', 'hours' => "{$this->editLineShift2Start} - {$this->editLineShift2End}"],
            ];
        }

        $line = $masterService->saveLine([
            'nama' => strtoupper(trim($this->editLineNama)),
            'mp_standard' => $this->editLineMpStandard,
            'pola_kerja' => $this->editLinePolaKerja,
            'status' => $this->editLineStatus,
            'jam_shift' => $jamShift,
        ], $this->selectedLineId);

        $this->showEditLineModal = false;
        $this->setFeedback("Data Line '{$line->nama}' berhasil diperbarui.");
        $this->runValidation();
    }

    /**
     * Hapus line yang sedang dipilih beserta seluruh relasinya dari sistem
     */
    public function deleteCurrentLine(MasterDataService $masterService, RosterService $rosterService): void
    {
        if (!$this->selectedLineId) {
            return;
        }

        $line = Line::find($this->selectedLineId);
        if (!$line) {
            return;
        }

        $lineName = $line->nama;
        $masterService->deleteLine($this->selectedLineId);

        $nextLine = Line::where('status', 'aktif')->orderBy('nama', 'asc')->first() ?? Line::first();
        $this->selectedLineId = $nextLine ? $nextLine->id : null;

        if ($this->selectedLineId) {
            $this->loadRoster($rosterService);
        } else {
            $this->shiftAssignments = [1 => [], 2 => [], 3 => []];
            $this->shiftTimes = [1 => [], 2 => [], 3 => []];
            $this->currentRosterId = null;
            $this->lastSavedHash = $this->calculateAssignmentsHash();
        }

        $this->setFeedback("Line '{$lineName}' beserta seluruh stasiun dan riwayat roster terkait berhasil dihapus dari sistem.");
    }

    public function render(MasterDataService $masterService, RosterService $rosterService)
    {
        $lines = $masterService->getLines();
        $leaders = $masterService->getActiveLeaders();
        $currentLine = $this->selectedLineId ? $masterService->getLineWithStations($this->selectedLineId) : null;

        if (!$currentLine && $lines->isNotEmpty()) {
            $this->selectedLineId = $lines->first()->id;
            $currentLine = $masterService->getLineWithStations($this->selectedLineId);
        }

        // Ambil daftar operator aktif untuk modal pemilihan
        $activeOperatorsQuery = Manpower::where('status', 'aktif');
        if (!empty($this->operatorSearch)) {
            $activeOperatorsQuery->where(function ($q) {
                $q->where('badge_id', 'like', "%{$this->operatorSearch}%")
                  ->orWhere('nama', 'like', "%{$this->operatorSearch}%");
            });
        }
        $activeOperators = $activeOperatorsQuery->orderBy('nama', 'asc')->get();

        // Peta manpower yang sedang ditugaskan di seluruh shift line ini
        $assignedInThisLine = [];
        $allAssignedMpIds = [];
        foreach ($this->shiftAssignments as $s => $ops) {
            foreach (array_keys($ops) as $mpId) {
                $assignedInThisLine[(int) $mpId] = (int) $s;
                $allAssignedMpIds[] = (int) $mpId;
            }
        }
        $assignedMap = Manpower::whereIn('id', array_unique($allAssignedMpIds))->get()->keyBy('id');

        // Peta manpower yang sudah terjadwal di line lain pada minggu ini
        $weekStartDate = Carbon::parse($this->weekStart)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $otherLineEntries = RosterEntry::whereHas('roster', function ($q) use ($weekStartDate) {
            $q->whereDate('minggu_mulai', $weekStartDate);
            if ($this->currentRosterId) {
                $q->where('id', '!=', $this->currentRosterId);
            } else if ($this->selectedLineId) {
                $q->where('line_id', '!=', $this->selectedLineId);
            }
        })->with('roster.line')->get();

        $assignedInOtherLines = [];
        foreach ($otherLineEntries as $entry) {
            $assignedInOtherLines[(int) $entry->manpower_id] = [
                'line_name' => $entry->roster->line->nama ?? 'Line Lain',
                'shift' => (int) $entry->shift,
            ];
        }

        // Pastikan shiftTimes selalu terinisialisasi untuk semua operator yang ditugaskan
        foreach ($this->shiftAssignments as $s => $ops) {
            if (!isset($this->shiftTimes[$s])) {
                $this->shiftTimes[$s] = [];
            }
            foreach (array_keys($ops) as $mpId) {
                if (!isset($this->shiftTimes[$s][$mpId])) {
                    $this->shiftTimes[$s][$mpId] = [
                        'start' => '',
                        'end' => '',
                    ];
                }
            }
        }

        // Peta stasiun untuk lookup nama & level
        $stationsMap = $currentLine ? $currentLine->stations->keyBy('id') : collect();

        // Hitung cakupan stasiun per shift (berapa operator di masing-masing stasiun)
        // Format: $stationCoverage[shift][station_id] = [ mpId1, mpId2, ... ]
        $stationCoverage = [1 => [], 2 => [], 3 => []];
        foreach ($this->shiftAssignments as $s => $ops) {
            foreach ($ops as $mpId => $stIds) {
                foreach ($stIds as $stId) {
                    if (!isset($stationCoverage[$s][$stId])) {
                        $stationCoverage[$s][$stId] = [];
                    }
                    $stationCoverage[$s][$stId][] = $mpId;
                }
            }
        }

        // Data untuk Cross-Line modal
        $crossLineRosters = $this->showCrossLineModal ? $rosterService->getCrossLineSummary($this->weekStart) : collect();

        return view('livewire.roster-schedule', [
            'lines' => $lines,
            'leaders' => $leaders,
            'currentLine' => $currentLine,
            'activeOperators' => $activeOperators,
            'assignedMap' => $assignedMap,
            'assignedInThisLine' => $assignedInThisLine,
            'assignedInOtherLines' => $assignedInOtherLines,
            'stationsMap' => $stationsMap,
            'stationCoverage' => $stationCoverage,
            'crossLineRosters' => $crossLineRosters,
        ])->layout('layouts.app', ['title' => 'Penjadwalan Roster Mingguan']);
    }
}
