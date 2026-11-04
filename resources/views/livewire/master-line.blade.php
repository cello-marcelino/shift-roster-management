<div class="space-y-4">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h1 class="text-xl font-bold tracking-tight">Manajemen Line & Station</h1>
            <p class="text-xs text-base-content/60">Kelola lini produksi, standar kebutuhan manpower (MP), shift, dan stasiun kerja.</p>
        </div>
        <button wire:click="openCreateLineModal" class="btn btn-neutral btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Line Baru
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

    <!-- Main Grid: Lines on Left, Stations on Right -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <!-- Lines List (5 Cols) -->
        <div class="lg:col-span-5 space-y-3">
            <div class="card bg-base-100 border border-base-300 shadow-xs">
                <div class="p-3 border-b border-base-200 flex justify-between items-center">
                    <span class="text-xs font-bold uppercase tracking-wider text-base-content/70">Daftar Lini Produksi</span>
                    <span class="badge badge-neutral badge-xs font-mono">{{ $lines->count() }} Line</span>
                </div>
                <div class="divide-y divide-base-200 overflow-y-auto max-h-[600px]">
                    @forelse ($lines as $ln)
                        <div 
                            wire:click="selectLine({{ $ln->id }})" 
                            class="p-3 cursor-pointer hover:bg-base-200/50 transition-colors flex justify-between items-center {{ $selectedLineId === $ln->id ? 'bg-base-200 border-l-4 border-l-neutral' : '' }}"
                        >
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm">{{ $ln->nama }}</span>
                                    @if ($ln->status === 'aktif')
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded-sm border border-emerald-600 text-emerald-600 bg-transparent text-[10px] font-semibold">Aktif</span>
                                    @elseif ($ln->status === 'waiting_material')
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded-sm border border-amber-600 text-amber-600 bg-transparent text-[10px] font-semibold">Waiting Material</span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded-sm border border-base-content/40 text-base-content/60 bg-transparent text-[10px] font-semibold">Off</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-base-content/60 flex gap-3">
                                    <span>Pola: <strong class="text-base-content">{{ $ln->pola_kerja_label }}</strong></span>
                                    <span>MP Std: <strong class="text-base-content font-mono">{{ $ln->mp_standard }}</strong></span>
                                    <span>Stasiun: <strong class="text-base-content font-mono">{{ $ln->stations_count }}</strong></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button wire:click.stop="editLine({{ $ln->id }})" class="btn btn-ghost btn-xs text-info px-1.5" title="Edit Line">
                                    Edit
                                </button>
                                <button 
                                    wire:click.stop="deleteLine({{ $ln->id }})" 
                                    wire:confirm="Yakin ingin menghapus Line {{ $ln->nama }}? Seluruh stasiun dan riwayat roster pada line ini akan ikut terhapus!" 
                                    class="btn btn-ghost btn-xs text-error px-1.5 hover:bg-error/10" 
                                    title="Hapus Line"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-base-content/50 space-y-2">
                            <p>Belum ada lini produksi (line) terdaftar di sistem.</p>
                            <button wire:click="openCreateLineModal" class="btn btn-neutral btn-xs">
                                + Tambah Line Pertama
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Selected Line & Stations List (7 Cols) -->
        <div class="lg:col-span-7 space-y-3">
            @if ($selectedLine)
                <div class="card bg-base-100 border border-base-300 shadow-xs">
                    <!-- Line Summary Header -->
                    <div class="p-4 border-b border-base-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 bg-base-200/30">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-bold">{{ $selectedLine->nama }}</h2>
                                <span class="badge badge-neutral badge-xs">{{ $selectedLine->pola_kerja_label }}</span>
                            </div>
                            <div class="text-[11px] text-base-content/60 mt-0.5">
                                Jam Kerja: 
                                @foreach ($selectedLine->effective_shift_hours as $sIdx => $sData)
                                    <span class="font-mono text-base-content">{{ $sData['label'] }}: {{ $sData['hours'] }}</span>@if(!$loop->last) · @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button wire:click="editLine({{ $selectedLine->id }})" class="btn btn-ghost btn-xs text-info border border-base-300">
                                Edit Line
                            </button>
                            <button 
                                wire:click="deleteLine({{ $selectedLine->id }})" 
                                wire:confirm="Yakin ingin menghapus Line {{ $selectedLine->nama }}? Seluruh stasiun dan riwayat roster pada line ini akan ikut terhapus!" 
                                class="btn btn-ghost btn-xs text-error border border-base-300 hover:bg-error/10"
                            >
                                Hapus Line
                            </button>
                            <button wire:click="openCreateStationModal" class="btn btn-outline btn-xs gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah Station
                            </button>
                        </div>
                    </div>

                    <!-- Stations Table -->
                    <div class="overflow-x-auto">
                        <table class="table table-sm table-zebra w-full text-xs">
                            <thead class="bg-base-200/60 font-bold uppercase text-[10px] text-base-content/70">
                                <tr>
                                    <th class="w-12">No</th>
                                    <th>Nama Station</th>
                                    <th class="w-32">Level Station</th>
                                    <th class="w-24">Cycle Time</th>
                                    <th class="w-20 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($selectedLine->stations as $idx => $st)
                                    <tr class="hover:bg-base-200/40">
                                        <td class="font-mono text-base-content/50">{{ $idx + 1 }}</td>
                                        <td class="font-semibold">{{ $st->nama }}</td>
                                        <td>
                                            @if ($st->level === 'Trained')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-sm border border-rose-600 text-rose-600 bg-transparent text-[11px] font-semibold">Trained</span>
                                            @elseif ($st->level === 'Basic-Trained')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-sm border border-amber-600 text-amber-600 bg-transparent text-[11px] font-semibold">Basic-Trained</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-sm border border-sky-600 text-sky-600 bg-transparent text-[11px] font-semibold">Basic</span>
                                            @endif
                                        </td>
                                        <td class="font-mono text-base-content/70">{{ $st->cycle_time ?: '-' }}</td>
                                        <td class="text-right">
                                            <div class="flex justify-end gap-1">
                                                <button wire:click="editStation({{ $st->id }})" class="btn btn-ghost btn-xs text-info p-1">Edit</button>
                                                <button wire:click="deleteStation({{ $st->id }})" wire:confirm="Hapus stasiun {{ $st->nama }}?" class="btn btn-ghost btn-xs text-error p-1">Hapus</button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-6 text-base-content/50">
                                            Belum ada station terdaftar pada line ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="card bg-base-100 border border-base-300 shadow-xs p-8 text-center text-xs text-base-content/50">
                    Belum ada line yang dipilih atau terdaftar di sistem.
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Form Line -->
    @if ($showLineModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-lg">
                <h3 class="font-bold text-base mb-3">
                    {{ $lineId ? 'Edit Line Produksi' : 'Tambah Line Produksi Baru' }}
                </h3>
                
                <form wire:submit.prevent="saveLine" class="space-y-3">
                    <div>
                        <label class="label label-text text-xs font-semibold">Nama Line *</label>
                        <input type="text" wire:model="nama" placeholder="Contoh: EV10, NTC, WEBASTO" class="input input-bordered input-sm w-full text-base sm:text-xs uppercase" />
                        @error('nama') <span class="text-error text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="label label-text text-xs font-semibold">MP Standard (Operator) *</label>
                            <input type="number" wire:model="mp_standard" min="1" class="input input-bordered input-sm w-full text-base sm:text-xs font-mono" />
                            @error('mp_standard') <span class="text-error text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="label label-text text-xs font-semibold">Status Line</label>
                            <select wire:model="status" class="select select-bordered select-sm w-full text-base sm:text-xs">
                                <option value="aktif">Aktif</option>
                                <option value="off">Off</option>
                                <option value="waiting_material">Waiting Material</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="label label-text text-xs font-semibold">Pola Kerja</label>
                        <select wire:model.live="pola_kerja" class="select select-bordered select-sm w-full text-base sm:text-xs">
                            <option value="5_days_2_shift">5 Hari Kerja (2 Shift)</option>
                            <option value="6_days_3_shift">6 Hari Kerja (3 Shift)</option>
                        </select>
                    </div>

                    <!-- Jam Shift Config -->
                    <div class="bg-base-200/50 p-3 rounded-lg space-y-2 border border-base-200">
                        <span class="text-[11px] font-bold uppercase text-base-content/60">Pengaturan Jam Kerja Shift</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] text-base-content/60">Shift 1 (Mulai - Selesai)</label>
                                <div class="flex gap-1 items-center">
                                    <input type="text" wire:model="shift1_start" class="input input-bordered input-xs w-full font-mono text-xs" />
                                    <span>-</span>
                                    <input type="text" wire:model="shift1_end" class="input input-bordered input-xs w-full font-mono text-xs" />
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] text-base-content/60">Shift 2 (Mulai - Selesai)</label>
                                <div class="flex gap-1 items-center">
                                    <input type="text" wire:model="shift2_start" class="input input-bordered input-xs w-full font-mono text-xs" />
                                    <span>-</span>
                                    <input type="text" wire:model="shift2_end" class="input input-bordered input-xs w-full font-mono text-xs" />
                                </div>
                            </div>
                        </div>

                        @if ($pola_kerja === '6_days_3_shift')
                            <div class="pt-1">
                                <label class="text-[10px] text-base-content/60">Shift 3 (Mulai - Selesai)</label>
                                <div class="flex gap-1 items-center max-w-[240px]">
                                    <input type="text" wire:model="shift3_start" class="input input-bordered input-xs w-full font-mono text-xs" />
                                    <span>-</span>
                                    <input type="text" wire:model="shift3_end" class="input input-bordered input-xs w-full font-mono text-xs" />
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="modal-action mt-4 flex justify-between items-center">
                        <div>
                            @if ($lineId)
                                <button 
                                    type="button" 
                                    wire:click="deleteLine({{ $lineId }})" 
                                    wire:confirm="Yakin ingin menghapus Line ini? Seluruh stasiun dan riwayat roster pada line ini akan ikut terhapus!" 
                                    class="btn btn-sm btn-error btn-outline"
                                >
                                    Hapus Line
                                </button>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <button type="button" wire:click="$set('showLineModal', false)" class="btn btn-sm btn-ghost">Batal</button>
                            <button type="submit" class="btn btn-sm btn-neutral">Simpan Line</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="$set('showLineModal', false)"></div>
        </div>
    @endif

    <!-- Modal Form Station -->
    @if ($showStationModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-md">
                <h3 class="font-bold text-base mb-3">
                    {{ $stationId ? 'Edit Station' : 'Tambah Station Baru' }}
                </h3>
                
                <form wire:submit.prevent="saveStation" class="space-y-3">
                    <div>
                        <label class="label label-text text-xs font-semibold">Nama Station *</label>
                        <input type="text" wire:model="station_nama" placeholder="Contoh: Crimping, Packaging" class="input input-bordered input-sm w-full text-base sm:text-xs" />
                        @error('station_nama') <span class="text-error text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="label label-text text-xs font-semibold">Level Station *</label>
                        <select wire:model="station_level" class="select select-bordered select-sm w-full text-base sm:text-xs">
                            <option value="Basic">Basic (Penempatan Fleksibel)</option>
                            <option value="Basic-Trained">Basic-Trained (Memerlukan Relevansi Station)</option>
                            <option value="Trained">Trained (Khusus Operator Terlatih)</option>
                        </select>
                        <p class="text-[10px] text-base-content/50 mt-1">
                            Tingkat keahlian digunakan sistem untuk mengecek kesesuaian penempatan operator.
                        </p>
                    </div>

                    <div>
                        <label class="label label-text text-xs font-semibold">Cycle Time (Opsional / Referensi)</label>
                        <input type="text" wire:model="station_cycle_time" placeholder="Contoh: 45s, 60s" class="input input-bordered input-sm w-full text-base sm:text-xs font-mono" />
                    </div>

                    <div class="modal-action mt-4">
                        <button type="button" wire:click="$set('showStationModal', false)" class="btn btn-sm btn-ghost">Batal</button>
                        <button type="submit" class="btn btn-sm btn-neutral">Simpan Station</button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="$set('showStationModal', false)"></div>
        </div>
    @endif
</div>
