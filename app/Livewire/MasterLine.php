<?php

namespace App\Livewire;

use App\Models\Line;
use App\Models\Station;
use App\Services\MasterDataService;
use Livewire\Component;

class MasterLine extends Component
{
    public ?int $selectedLineId = null;

    // Line form fields
    public ?int $lineId = null;
    public string $nama = '';
    public int $mp_standard = 15;
    public string $pola_kerja = '5_days_2_shift';
    public string $status = 'aktif';

    // Shift Hours fields
    public string $shift1_start = '07.00';
    public string $shift1_end = '16.00';
    public string $shift2_start = '16.00';
    public string $shift2_end = '01.00';
    public string $shift3_start = '23.00';
    public string $shift3_end = '07.00';

    // Station form fields (for adding/editing a station under the selected line)
    public ?int $stationId = null;
    public string $station_nama = '';
    public string $station_level = 'Basic';
    public string $station_cycle_time = '45s';

    public bool $showLineModal = false;
    public bool $showStationModal = false;
    public string $feedbackMessage = '';
    public int $flashCounter = 0;

    public function setFeedback(string $message): void
    {
        $this->feedbackMessage = $message;
        $this->flashCounter++;
    }

    public function mount(): void
    {
        $firstLine = Line::orderBy('nama', 'asc')->first();
        if ($firstLine) {
            $this->selectedLineId = $firstLine->id;
        }
    }

    public function selectLine(int $id): void
    {
        $this->selectedLineId = $id;
    }

    public function openCreateLineModal(): void
    {
        $this->resetValidation();
        $this->reset(['lineId', 'nama']);
        $this->mp_standard = 15;
        $this->pola_kerja = '5_days_2_shift';
        $this->status = 'aktif';
        $this->shift1_start = '07.00';
        $this->shift1_end = '16.00';
        $this->shift2_start = '16.00';
        $this->shift2_end = '01.00';
        $this->shift3_start = '23.00';
        $this->shift3_end = '07.00';
        $this->showLineModal = true;
    }

    public function editLine(int $id): void
    {
        $this->resetValidation();
        $line = Line::findOrFail($id);
        $this->lineId = $line->id;
        $this->nama = $line->nama;
        $this->mp_standard = $line->mp_standard;
        $this->pola_kerja = $line->pola_kerja;
        $this->status = $line->status;

        $hours = $line->effective_shift_hours;
        if (isset($hours[1]['hours'])) {
            $parts = explode('-', $hours[1]['hours']);
            $this->shift1_start = trim($parts[0] ?? '07.00');
            $this->shift1_end = trim($parts[1] ?? '16.00');
        }
        if (isset($hours[2]['hours'])) {
            $parts = explode('-', $hours[2]['hours']);
            $this->shift2_start = trim($parts[0] ?? '16.00');
            $this->shift2_end = trim($parts[1] ?? '01.00');
        }
        if (isset($hours[3]['hours'])) {
            $parts = explode('-', $hours[3]['hours']);
            $this->shift3_start = trim($parts[0] ?? '23.00');
            $this->shift3_end = trim($parts[1] ?? '07.00');
        } else {
            $this->shift3_start = '23.00';
            $this->shift3_end = '07.00';
        }

        $this->showLineModal = true;
    }

    public function saveLine(MasterDataService $service): void
    {
        $this->validate([
            'nama' => 'required|string|max:50|unique:lines,nama,' . ($this->lineId ?? 'NULL'),
            'mp_standard' => 'required|integer|min:1',
            'pola_kerja' => 'required|string',
            'status' => 'required|in:aktif,off,waiting_material',
        ]);

        if ($this->pola_kerja === '6_days_3_shift') {
            $jamShift = [
                1 => ['label' => 'Shift 1', 'hours' => "{$this->shift1_start} - {$this->shift1_end}"],
                2 => ['label' => 'Shift 2', 'hours' => "{$this->shift2_start} - {$this->shift2_end}"],
                3 => ['label' => 'Shift 3', 'hours' => "{$this->shift3_start} - {$this->shift3_end}"],
            ];
        } else {
            $jamShift = [
                1 => ['label' => 'Shift 1', 'hours' => "{$this->shift1_start} - {$this->shift1_end}"],
                2 => ['label' => 'Shift 2', 'hours' => "{$this->shift2_start} - {$this->shift2_end}"],
            ];
        }

        $line = $service->saveLine([
            'nama' => strtoupper(trim($this->nama)),
            'mp_standard' => $this->mp_standard,
            'pola_kerja' => $this->pola_kerja,
            'status' => $this->status,
            'jam_shift' => $jamShift,
        ], $this->lineId);

        $this->selectedLineId = $line->id;
        $this->showLineModal = false;
        $this->setFeedback($this->lineId ? 'Data Line berhasil diperbarui.' : 'Line baru berhasil ditambahkan.');
    }

    public function openCreateStationModal(): void
    {
        $this->resetValidation();
        $this->reset(['stationId', 'station_nama']);
        $this->station_level = 'Basic';
        $this->station_cycle_time = '45s';
        $this->showStationModal = true;
    }

    public function editStation(int $stId): void
    {
        $this->resetValidation();
        $st = Station::findOrFail($stId);
        $this->stationId = $st->id;
        $this->station_nama = $st->nama;
        $this->station_level = $st->level;
        $this->station_cycle_time = (string) $st->cycle_time;
        $this->showStationModal = true;
    }

    public function saveStation(MasterDataService $service): void
    {
        $this->validate([
            'station_nama' => 'required|string|max:100',
            'station_level' => 'required|in:Basic,Basic-Trained,Trained',
            'station_cycle_time' => 'nullable|string|max:50',
        ]);

        if (!$this->selectedLineId) {
            return;
        }

        $service->saveStation($this->selectedLineId, [
            'nama' => trim($this->station_nama),
            'level' => $this->station_level,
            'cycle_time' => $this->station_cycle_time,
        ], $this->stationId);

        $this->showStationModal = false;
        $this->setFeedback($this->stationId ? 'Data stasiun berhasil diperbarui.' : 'Stasiun baru berhasil ditambahkan.');
    }

    public function deleteLine(int $id, MasterDataService $service): void
    {
        $line = Line::find($id);
        if (!$line) {
            return;
        }

        $lineName = $line->nama;
        $service->deleteLine($id);

        if ($this->selectedLineId === $id) {
            $nextLine = Line::orderBy('nama', 'asc')->first();
            $this->selectedLineId = $nextLine ? $nextLine->id : null;
        }

        $this->showLineModal = false;
        $this->setFeedback("Line '{$lineName}' beserta seluruh stasiun dan riwayat roster terkait berhasil dihapus dari sistem.");
    }

    public function deleteStation(int $stId, MasterDataService $service): void
    {
        $service->deleteStation($stId);
        $this->setFeedback('Stasiun berhasil dihapus.');
    }

    public function render(MasterDataService $service)
    {
        $lines = $service->getLines();

        if ($this->selectedLineId && !$lines->contains('id', $this->selectedLineId)) {
            $this->selectedLineId = $lines->first()?->id;
        } elseif (!$this->selectedLineId && $lines->isNotEmpty()) {
            $this->selectedLineId = $lines->first()->id;
        }

        $selectedLine = $this->selectedLineId ? $service->getLineWithStations($this->selectedLineId) : null;

        return view('livewire.master-line', [
            'lines' => $lines,
            'selectedLine' => $selectedLine,
        ])->layout('layouts.app', ['title' => 'Master Line & Station']);
    }
}
