<?php

namespace App\Livewire;

use App\Models\Leader;
use App\Services\MasterDataService;
use Livewire\Component;

class MasterLeader extends Component
{
    public ?int $leaderId = null;
    public string $badge_id = '';
    public string $nama = '';
    public string $status = 'aktif';

    public bool $showModal = false;
    public string $feedbackMessage = '';
    public int $flashCounter = 0;

    public function setFeedback(string $message): void
    {
        $this->feedbackMessage = $message;
        $this->flashCounter++;
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['leaderId', 'badge_id', 'nama']);
        $this->status = 'aktif';
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $this->resetValidation();
        $ldr = Leader::findOrFail($id);
        $this->leaderId = $ldr->id;
        $this->badge_id = $ldr->badge_id;
        $this->nama = $ldr->nama;
        $this->status = $ldr->status;
        $this->showModal = true;
    }

    public function save(MasterDataService $service): void
    {
        $this->validate([
            'badge_id' => 'required|string|max:50|unique:leaders,badge_id,' . ($this->leaderId ?? 'NULL'),
            'nama' => 'required|string|max:100',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $service->saveLeader([
            'badge_id' => trim($this->badge_id),
            'nama' => trim($this->nama),
            'status' => $this->status,
        ], $this->leaderId);

        $this->showModal = false;
        $this->setFeedback($this->leaderId ? 'Data Leader berhasil diperbarui.' : 'Leader baru berhasil ditambahkan.');
    }

    public function delete(int $id, MasterDataService $service): void
    {
        $service->deleteLeader($id);
        $this->setFeedback('Leader berhasil dihapus.');
    }

    public function render()
    {
        $leaders = Leader::withCount('rosters')->orderBy('nama', 'asc')->get();

        return view('livewire.master-leader', [
            'leaders' => $leaders,
        ])->layout('layouts.app', ['title' => 'Master Leader']);
    }
}
