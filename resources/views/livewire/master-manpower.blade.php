<div class="space-y-4">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h1 class="text-xl font-bold tracking-tight">Manajemen Manpower</h1>
            <p class="text-xs text-base-content/60">Daftar seluruh operator lini produksi dan status ketersediaan.</p>
        </div>
        <button wire:click="openCreateModal" class="btn btn-neutral btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Operator
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

    <!-- Filter & Search Bar -->
    <div class="card bg-base-100 border border-base-300 p-3 shadow-xs">
        <div class="flex flex-col sm:flex-row gap-3 items-center justify-between">
            <div class="flex flex-1 w-full gap-2 items-center">
                <div class="relative flex-1">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari badge ID atau nama operator..." 
                        class="input input-bordered input-sm w-full pl-8 text-base sm:text-xs"
                    />
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-2.5 top-2.5 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <select wire:model.live="statusFilter" class="select select-bordered select-sm text-base sm:text-xs">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="cuti">Cuti</option>
                </select>
            </div>
            <div class="text-xs text-base-content/60 font-mono">
                Total: {{ $manpowers->total() }} operator
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card bg-base-100 border border-base-300 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="table table-sm table-zebra w-full text-xs">
                <thead class="bg-base-200/60 font-bold uppercase text-[11px] text-base-content/70">
                    <tr>
                        <th class="w-20">Badge ID</th>
                        <th>Nama Operator</th>
                        <th>Station Utama (Preferensi)</th>
                        <th class="w-28 text-center">Status</th>
                        <th class="w-28 text-center">Toggle</th>
                        <th class="w-24 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($manpowers as $mp)
                        <tr class="hover:bg-base-200/40">
                            <td class="font-mono font-bold">{{ $mp->badge_id }}</td>
                            <td class="font-semibold">{{ $mp->nama }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @if ($mp->station_utama_1)
                                        <span class="badge badge-outline badge-xs">{{ $mp->station_utama_1 }}</span>
                                    @endif
                                    @if ($mp->station_utama_2)
                                        <span class="badge badge-ghost badge-xs">{{ $mp->station_utama_2 }}</span>
                                    @endif
                                    @if ($mp->station_utama_3)
                                        <span class="badge badge-ghost badge-xs">{{ $mp->station_utama_3 }}</span>
                                    @endif
                                    @if (!$mp->station_utama_1 && !$mp->station_utama_2 && !$mp->station_utama_3)
                                        <span class="text-base-content/40 italic">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                @if ($mp->status === 'aktif')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-sm border border-emerald-600 text-emerald-600 bg-transparent text-[11px] font-semibold">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-sm border border-amber-600 text-amber-600 bg-transparent text-[11px] font-semibold">Cuti</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button 
                                    wire:click="toggleStatus({{ $mp->id }})" 
                                    class="btn btn-ghost btn-xs text-[10px]"
                                    title="Klik untuk ubah status"
                                >
                                    {{ $mp->status === 'aktif' ? 'Set Cuti' : 'Set Aktif' }}
                                </button>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <button wire:click="edit({{ $mp->id }})" class="btn btn-ghost btn-xs text-info">
                                        Edit
                                    </button>
                                    <button 
                                        wire:click="delete({{ $mp->id }})" 
                                        wire:confirm="Yakin ingin menghapus operator {{ $mp->nama }}?"
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
                                Tidak ada data operator yang cocok dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination & Showing Counter Footer -->
        <div class="p-3 border-t border-base-200 flex flex-col sm:flex-row justify-between items-center gap-3">
            <!-- Showing Counter (Strict Inline, Clean, No Dropdown) -->
            <div class="text-xs text-base-content/70 whitespace-nowrap shrink-0">
                Showing <strong class="font-mono text-base-content font-semibold">{{ $manpowers->firstItem() ?? 0 }}-{{ $manpowers->lastItem() ?? 0 }}</strong> of <strong class="font-mono text-base-content font-bold">{{ $manpowers->total() }}</strong> list
            </div>

            <!-- Pagination Links (Limited to 4 Navigation Numbers) -->
            @if ($manpowers->hasPages())
                <div class="shrink-0">
                    {{ $manpowers->links('livewire.partials.daisy-pagination') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Form Tambah / Edit -->
    @if ($showModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-lg">
                <h3 class="font-bold text-base mb-3">
                    {{ $manpowerId ? 'Edit Data Manpower' : 'Tambah Manpower Baru' }}
                </h3>
                
                <form wire:submit.prevent="save" class="space-y-3">
                    <div>
                        <label class="label label-text text-xs font-semibold">Badge ID *</label>
                        <input 
                            type="text" 
                            wire:model="badge_id" 
                            placeholder="Contoh: 1042" 
                            class="input input-bordered input-sm w-full font-mono text-base sm:text-xs" 
                        />
                        @error('badge_id') <span class="text-error text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="label label-text text-xs font-semibold">Nama Lengkap *</label>
                        <input 
                            type="text" 
                            wire:model="nama" 
                            placeholder="Nama operator" 
                            class="input input-bordered input-sm w-full text-base sm:text-xs uppercase" 
                        />
                        @error('nama') <span class="text-error text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div>
                            <label class="label label-text text-[11px] font-semibold">Station Utama 1</label>
                            <select wire:model="station_utama_1" class="select select-bordered select-sm w-full text-base sm:text-xs">
                                <option value="">Pilih...</option>
                                @foreach ($stationOptions as $st)
                                    <option value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label label-text text-[11px] font-semibold">Station Utama 2</label>
                            <select wire:model="station_utama_2" class="select select-bordered select-sm w-full text-base sm:text-xs">
                                <option value="">Pilih...</option>
                                @foreach ($stationOptions as $st)
                                    <option value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label label-text text-[11px] font-semibold">Station Utama 3</label>
                            <select wire:model="station_utama_3" class="select select-bordered select-sm w-full text-base sm:text-xs">
                                <option value="">Pilih...</option>
                                @foreach ($stationOptions as $st)
                                    <option value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="label label-text text-xs font-semibold">Status Ketersediaan</label>
                        <select wire:model="status" class="select select-bordered select-sm w-full text-base sm:text-xs">
                            <option value="aktif">Aktif (Dapat Dijadwalkan)</option>
                            <option value="cuti">Cuti (Tidak Muncul di Pilihan Roster)</option>
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
