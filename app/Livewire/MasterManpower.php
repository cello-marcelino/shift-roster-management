<?php

namespace App\Livewire;

use App\Models\Manpower;
use App\Models\Station;
use App\Services\MasterDataService;
use Livewire\Component;
use Livewire\WithPagination;

class MasterManpower extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public int $perPage = 25;

    // Form fields
    public ?int $manpowerId = null;
    public string $badge_id = '';
    public string $nama = '';
    public string $station_utama_1 = '';
    public string $station_utama_2 = '';
    public string $station_utama_3 = '';
    public string $status = 'aktif';

    public bool $showModal = false;
    public string $feedbackMessage = '';
    public int $flashCounter = 0;

    public function setFeedback(string $message): void
    {
        $this->feedbackMessage = $message;
        $this->flashCounter++;
    }

    protected function rules(): array
    {
        return [
            'badge_id' => 'required|string|max:50|unique:manpowers,badge_id,' . ($this->manpowerId ?? 'NULL'),
            'nama' => 'required|string|max:100',
            'station_utama_1' => 'nullable|string|max:100',
            'station_utama_2' => 'nullable|string|max:100',
            'station_utama_3' => 'nullable|string|max:100',
            'status' => 'required|in:aktif,cuti',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function previousPage($pageName = 'page'): void
    {
        $this->setPage(max($this->getPage($pageName) - 1, 1), $pageName);
    }

    public function nextPage($pageName = 'page'): void
    {
        $this->setPage($this->getPage($pageName) + 1, $pageName);
    }

    public function gotoPage(int $page, $pageName = 'page'): void
    {
        $this->setPage($page, $pageName);
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['manpowerId', 'badge_id', 'nama', 'station_utama_1', 'station_utama_2', 'station_utama_3']);
        $this->status = 'aktif';
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $this->resetValidation();
        $mp = Manpower::findOrFail($id);
        $this->manpowerId = $mp->id;
        $this->badge_id = $mp->badge_id;
        $this->nama = $mp->nama;
        $this->station_utama_1 = (string) $mp->station_utama_1;
        $this->station_utama_2 = (string) $mp->station_utama_2;
        $this->station_utama_3 = (string) $mp->station_utama_3;
        $this->status = $mp->status;
        $this->showModal = true;
    }

    public function save(MasterDataService $service): void
    {
        $validated = $this->validate();

        $service->saveManpower([
            'badge_id' => trim($this->badge_id),
            'nama' => strtoupper(trim($this->nama)),
            'station_utama_1' => $this->station_utama_1 ?: null,
            'station_utama_2' => $this->station_utama_2 ?: null,
            'station_utama_3' => $this->station_utama_3 ?: null,
            'status' => $this->status,
        ], $this->manpowerId);

        $this->showModal = false;
        $this->setFeedback($this->manpowerId ? 'Data manpower berhasil diperbarui.' : 'Manpower baru berhasil ditambahkan.');
    }

    public function toggleStatus(int $id, MasterDataService $service): void
    {
        $updated = $service->toggleManpowerStatus($id);
        $statusLabel = $updated->status === 'aktif' ? 'Aktif' : 'Cuti';
        $this->setFeedback("Status operator {$updated->nama} diubah menjadi {$statusLabel}.");
    }

    public function delete(int $id, MasterDataService $service): void
    {
        $service->deleteManpower($id);
        $this->setFeedback('Data manpower berhasil dihapus.');
    }

    public function render(MasterDataService $service)
    {
        $manpowers = $service->getManpowers($this->search, $this->statusFilter, $this->perPage);
        $stationOptions = Station::select('nama')->distinct()->orderBy('nama', 'asc')->pluck('nama');

        return view('livewire.master-manpower', [
            'manpowers' => $manpowers,
            'stationOptions' => $stationOptions,
        ])->layout('layouts.app', ['title' => 'Master Manpower']);
    }
}
