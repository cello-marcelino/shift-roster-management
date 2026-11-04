<div class="space-y-4 max-w-4xl">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h1 class="text-xl font-bold tracking-tight">Manajemen Leader</h1>
            <p class="text-xs text-base-content/60">Daftar Leader penyusunan jadwal yang dicantumkan pada cetakan PDF.</p>
        </div>
        <button wire:click="openCreateModal" class="btn btn-neutral btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Leader
        </button>
    </div>

    <!-- Floating Toast Notification (Temporary Feedback) -->
    @if ($feedbackMessage)
        <div class="toast toast-top toast-end z-50 p-4 pointer-events-none">
            <div 
                wire:key="toast-feedback-{{ $flashCounter }}-{{ md5($feedbackMessage) }}"
                x-data="{ show: true }" 
                x-init="setTimeout(() => show = false, 3500)" 
                x-show="show" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="alert alert-success alert-soft text-xs shadow-lg border border-success/30 py-2.5 px-3 pointer-events-auto flex items-center justify-between gap-2 max-w-sm rounded-lg"
            >
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-4 w-4 text-success" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="font-medium">{{ $feedbackMessage }}</span>
                </div>
                <button type="button" @click="show = false" class="text-base-content/40 hover:text-base-content text-xs font-bold ml-1 cursor-pointer" title="Tutup">✕</button>
            </div>
        </div>
    @endif

    <!-- Table Section -->
    <div class="card bg-base-100 border border-base-300 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="table table-sm table-zebra w-full text-xs">
                <thead class="bg-base-200/60 font-bold uppercase text-[11px] text-base-content/70">
                    <tr>
                        <th class="w-16">No</th>
                        <th class="w-28">Badge ID</th>
                        <th>Nama Leader</th>
                        <th class="w-32 text-center">Status</th>
                        <th class="w-32 text-center">Total Roster</th>
                        <th class="w-24 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leaders as $idx => $ldr)
                        <tr class="hover:bg-base-200/40">
                            <td class="font-mono text-base-content/50">{{ $idx + 1 }}</td>
                            <td class="font-mono font-bold">{{ $ldr->badge_id }}</td>
                            <td class="font-semibold text-sm">{{ $ldr->nama }}</td>
                            <td class="text-center">
                                @if ($ldr->status === 'aktif')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-sm border border-emerald-600 text-emerald-600 bg-transparent text-[11px] font-semibold">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-sm border border-base-content/40 text-base-content/60 bg-transparent text-[11px] font-semibold">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center font-mono text-base-content/70">
                                {{ $ldr->rosters_count }}
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <button wire:click="edit({{ $ldr->id }})" class="btn btn-ghost btn-xs text-info">
                                        Edit
                                    </button>
                                    <button 
                                        wire:click="delete({{ $ldr->id }})" 
                                        wire:confirm="Yakin ingin menghapus leader {{ $ldr->nama }}?"
                                        class="btn btn-ghost btn-xs text-error"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-base-content/50">
                                Belum ada data leader terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Leader -->
    @if ($showModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-sm">
                <h3 class="font-bold text-base mb-3">
                    {{ $leaderId ? 'Edit Data Leader' : 'Tambah Leader Baru' }}
                </h3>
                
                <form wire:submit.prevent="save" class="space-y-3">
                    <div>
                        <label class="label label-text text-xs font-semibold">Badge ID *</label>
                        <input 
                            type="text" 
                            wire:model="badge_id" 
                            placeholder="Contoh: LDR-007" 
                            class="input input-bordered input-sm w-full font-mono text-base sm:text-xs uppercase" 
                        />
                        @error('badge_id') <span class="text-error text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="label label-text text-xs font-semibold">Nama Lengkap (Tercetak di PDF) *</label>
                        <input 
                            type="text" 
                            wire:model="nama" 
                            placeholder="Contoh: Mr. John Doe" 
                            class="input input-bordered input-sm w-full text-base sm:text-xs" 
                        />
                        @error('nama') <span class="text-error text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="label label-text text-xs font-semibold">Status</label>
                        <select wire:model="status" class="select select-bordered select-sm w-full text-base sm:text-xs">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>

                    <div class="modal-action mt-4">
                        <button type="button" wire:click="$set('showModal', false)" class="btn btn-sm btn-ghost">Batal</button>
                        <button type="submit" class="btn btn-sm btn-neutral">Simpan</button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="$set('showModal', false)"></div>
        </div>
    @endif
</div>
