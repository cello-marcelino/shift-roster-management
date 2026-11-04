<div class="space-y-4">
    @if ($lines->isEmpty())
        <!-- Centered Empty State for Landing Page when No Lines Exist -->
        <div class="flex flex-col items-center justify-center min-h-[65vh] max-w-xl mx-auto text-center px-4 py-8">
            <div class="card bg-base-100 border border-base-300 p-8 sm:p-10 shadow-xs w-full">
                <div class="w-14 h-14 rounded-full bg-base-200 border border-base-300 flex items-center justify-center mx-auto mb-4 text-base-content/60">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>

                <h2 class="text-lg sm:text-xl font-bold tracking-tight text-base-content mb-2">
                    Belum Ada Lini Produksi Terdaftar
                </h2>
                <p class="text-xs sm:text-sm text-base-content/65 max-w-md mx-auto mb-6">
                    Sistem penjadwalan roster memerlukan minimal satu lini produksi aktif sebelum Anda dapat menyusun penugasan operator per shift.
                </p>

                <!-- Instruksi Singkat -->
                <div class="bg-base-200/50 border border-base-200 rounded-lg p-4 mb-6 text-left">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-base-content/70 block mb-2.5">
                        Instruksi Pengisian Data Line:
                    </span>
                    <ol class="space-y-2 text-xs text-base-content/80 list-decimal list-inside">
                        <li>
                            <strong>Buka Menu Master Line</strong> dengan menekan tombol navigasi di bawah atau menu di bagian atas.
                        </li>
                        <li>
                            <strong>Buat Lini Produksi</strong>: tentukan nama line (contoh: <em>EV10</em>, <em>NTC</em>), standar kebutuhan manpower (MP), serta acuan jam kerja shift (Shift 1 & Shift 2).
                        </li>
                        <li>
                            <strong>Daftarkan Stasiun Kerja</strong> di dalam line tersebut (contoh: <em>Crimping</em>, <em>Packaging</em>, dll).
                        </li>
                    </ol>
                </div>

                <div class="flex justify-center">
                    <a href="{{ route('master.lines') }}" class="btn btn-neutral btn-sm gap-2 px-5 font-semibold shadow-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Ke Menu Master Line</span>
                    </a>
                </div>
            </div>
        </div>
    @else
    <!-- Top Control Bar (Single Layer Section Card) -->
    <div class="card bg-base-100 border border-base-300 p-4 shadow-xs">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Line Selector -->
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/60 block mb-1">Pilih Line</label>
                    <div class="flex items-center gap-1.5">
                        <select wire:model.live="selectedLineId" class="select select-bordered select-sm font-bold text-base sm:text-xs min-w-[150px]">
                            @foreach ($lines as $ln)
                                <option value="{{ $ln->id }}">{{ $ln->nama }} ({{ $ln->shift_count }} Shift)</option>
                            @endforeach
                        </select>
                        @if ($selectedLineId)
                            <button 
                                type="button"
                                wire:click="openEditLineModal" 
                                class="btn btn-ghost btn-sm text-base-content/60 hover:text-neutral hover:bg-base-200 border border-base-300 px-2"
                                title="Edit Line ini"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Week Date Picker -->
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/60 block mb-1">Periode Minggu (Senin)</label>
                    <input 
                        type="date" 
                        wire:model.live="weekStart" 
                        class="input input-bordered input-sm font-mono text-base sm:text-xs"
                    />
                </div>

                <!-- Leader Selector -->
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/60 block mb-1">Leader</label>
                    <select wire:model.live="selectedLeaderId" class="select select-bordered select-sm text-base sm:text-xs min-w-[150px]">
                        @foreach ($leaders as $ldr)
                            <option value="{{ $ldr->id }}">{{ $ldr->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Badge (Informative Dirty & Saved State by Color Accent) -->
                <div class="pt-5">
                    @if ($this->hasUnsavedChanges)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-sm border border-slate-500 text-slate-600 dark:text-slate-400 bg-transparent text-[11px] font-bold tracking-wider uppercase" title="Perubahan terdeteksi (belum disimpan)">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-slate-500 animate-pulse"></span>
                            DRAFT
                        </span>
                    @elseif ($rosterStatus === 'final')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-sm border border-emerald-600 text-emerald-600 bg-transparent text-[11px] font-bold tracking-wider uppercase">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                            FINAL
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-sm border border-amber-500 text-amber-600 dark:text-amber-500 bg-transparent text-[11px] font-bold tracking-wider uppercase" title="Data draft tersimpan">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            DRAFT
                        </span>
                    @endif
                </div>
            </div>

            <!-- Action Buttons Bar -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Cross-Line Check -->
                <button wire:click="$set('showCrossLineModal', true)" class="btn btn-outline btn-sm gap-1 text-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Cek Semua Line
                </button>

                <!-- Save Draft -->
                <button wire:click="saveDraft" class="btn {{ $this->hasUnsavedChanges ? 'btn-neutral font-semibold' : 'btn-outline' }} btn-sm text-xs">
                    Simpan Draft
                </button>

                <!-- Finalize / Reopen -->
                @if ($rosterStatus === 'draft')
                    <button wire:click="setFinal" class="btn btn-neutral btn-sm text-xs">
                        Tetapkan Final
                    </button>
                @else
                    <button wire:click="reopenDraft" class="btn btn-ghost btn-sm text-xs text-warning">
                        Buka Draft
                    </button>
                @endif

                <!-- PDF Actions: View & Download -->
                @if ($currentRosterId)
                    <div class="join shadow-xs">
                        <a 
                            href="{{ route('roster.view.single', $currentRosterId) }}" 
                            target="_blank"
                            class="btn btn-neutral btn-sm gap-1.5 text-xs font-bold join-item"
                            title="Buka dan lihat tampilan PDF di tab baru tanpa mengunduh"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>Lihat PDF</span>
                        </a>
                        <a 
                            href="{{ route('roster.export.single', $currentRosterId) }}" 
                            class="btn btn-outline btn-sm gap-1 text-xs join-item hover:bg-base-200"
                            title="Unduh langsung file PDF ke perangkat"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Unduh</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Floating Toast Notifications (Temporary Feedback & Error Alerts) -->
    @if ($feedbackMessage || $errorMessage)
        <div class="toast toast-top toast-end z-50 p-4 pointer-events-none space-y-2">
            @if ($feedbackMessage)
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
            @endif

            @if ($errorMessage)
                <div 
                    wire:key="toast-error-{{ $flashCounter }}-{{ md5($errorMessage) }}"
                    x-data="{ show: true }" 
                    x-init="setTimeout(() => show = false, 4500)" 
                    x-show="show" 
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="alert alert-error alert-soft text-xs shadow-lg border border-error/30 py-2.5 px-3 pointer-events-auto flex items-center justify-between gap-2 max-w-sm rounded-lg"
                >
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-4 w-4 text-error" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="font-medium">{{ $errorMessage }}</span>
                    </div>
                    <button type="button" @click="show = false" class="text-base-content/40 hover:text-base-content text-xs font-bold ml-1 cursor-pointer" title="Tutup">✕</button>
                </div>
            @endif
        </div>
    @endif

    <!-- Minimal Warnings Notification (Dismissible) -->
    @php
        $skillCount = count($validationResults['skill_warnings'] ?? []);
        $mpCount = count($validationResults['mp_warnings'] ?? []);
        $hasWarnings = ($skillCount > 0) || ($mpCount > 0);
    @endphp

    @if ($showWarnings && $hasWarnings)
        <div class="flex items-center justify-between px-3 py-2 bg-base-100 border border-base-300 rounded-lg text-xs shadow-xs">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-warning shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-base-content/80 text-[11px]">
                    @if (!empty($validationResults['mp_warnings']))
                        @foreach ($validationResults['mp_warnings'] as $mpw)
                            <span class="text-base-content/70">{{ $mpw['message'] }}</span>
                        @endforeach
                    @endif
                    @if ($skillCount > 0)
                        <span class="text-base-content/60">{{ $skillCount }} stasiun di luar keahlian</span>
                    @endif
                </div>
            </div>
            <button wire:click="toggleWarnings" class="btn btn-ghost btn-xs text-base-content/40 hover:text-base-content ml-2" title="Sembunyikan peringatan">
                ✕
            </button>
        </div>
    @elseif (!$showWarnings && $hasWarnings)
        <div class="flex justify-end">
            <button wire:click="toggleWarnings" class="text-[11px] text-base-content/50 hover:text-base-content flex items-center gap-1 font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Tampilkan peringatan</span>
            </button>
        </div>
    @endif

    <!-- Shift Tabs Navigation -->
    @if ($currentLine)
        @php
            $shiftCount = $currentLine->shift_count;
            $effectiveHours = $currentLine->effective_shift_hours;
        @endphp

        <!-- Shift Tabs Navigation & Quick Shift Actions (Tabs Border Bottom) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-base-300 gap-2">
            <!-- Left: Shift Tabs Navigation -->
            <div class="tabs tabs-border overflow-x-auto">
                <button 
                    type="button"
                    wire:click="setActiveShift(0)" 
                    class="tab text-xs gap-1.5 py-3 h-auto {{ $activeShift === 0 ? 'tab-active font-bold text-neutral' : 'text-base-content/70 hover:text-base-content' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                    <span>Semua Shift</span>
                </button>

                @for ($s = 1; $s <= $shiftCount; $s++)
                    @php
                        $sLabel = $effectiveHours[$s]['label'] ?? "Shift {$s}";
                        $sHours = $effectiveHours[$s]['hours'] ?? "";
                        $isActive = ($activeShift === $s);
                    @endphp
                    <button 
                        type="button"
                        wire:click="setActiveShift({{ $s }})" 
                        class="tab text-xs gap-1.5 py-3 h-auto {{ $isActive ? 'tab-active font-bold text-neutral' : 'text-base-content/70 hover:text-base-content' }}"
                    >
                        <span>{{ strtoupper($sLabel) }}</span>
                        <span class="font-normal text-[10px] opacity-70">({{ $sHours }})</span>
                    </button>
                @endfor
            </div>

            <!-- Right: Action Buttons Group (Quick Action) -->
            <div class="flex flex-wrap items-center gap-1.5 pb-2 sm:pb-0 px-1 sm:px-0">
                <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 select-none mr-0.5">Quick Action</span>

                <!-- 1. Manpower Only -->
                <button 
                    type="button"
                    wire:click="quickFillManpowerOnly" 
                    class="btn btn-xs gap-1 text-[11px] font-semibold border border-emerald-600 text-emerald-600 bg-transparent hover:bg-emerald-50 dark:hover:bg-emerald-950/20"
                    title="Isi otomatis operator hingga standar MP (stasiun ditentukan manual)"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Manpower Only</span>
                </button>

                <!-- 2. Kosongkan Slot -->
                <button 
                    type="button"
                    wire:click="clearAllSlots" 
                    wire:confirm="Yakin ingin mengosongkan seluruh slot jadwal line ini?"
                    class="btn btn-xs gap-1 text-[11px] font-semibold border border-rose-600 text-rose-600 bg-transparent hover:bg-rose-50 dark:hover:bg-rose-950/20"
                    title="Hapus semua penugasan operator di line ini"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Kosongkan Slot</span>
                </button>

                <!-- 3. Draft Minggu Lalu -->
                <div class="dropdown dropdown-end">
                    <button tabindex="0" class="btn btn-outline btn-xs gap-1 text-[11px]" title="Salin Jadwal Dari Draft Minggu Lalu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span>Draft Minggu Lalu</span>
                    </button>
                    <ul tabindex="0" class="dropdown-content menu p-2 shadow-lg bg-base-100 rounded-box w-56 text-xs z-50 border border-base-200 mt-1">
                        <li>
                            <button wire:click="copyFromPreviousWeek(false)" class="text-left py-2">
                                <strong>Salin Sama Persis</strong>
                                <span class="text-[10px] text-base-content/60">Posisi shift & stasiun sama seperti minggu lalu</span>
                            </button>
                        </li>
                        <li>
                            <button wire:click="copyFromPreviousWeek(true)" class="text-left py-2">
                                <strong>Salin + Rotasi Mundur</strong>
                                <span class="text-[10px] text-base-content/60">Rotasi shift otomatis (Shift 1 ↔ Shift 2 bergantian)</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Render Shift Tables (Horizontal Layout) -->
        @php
            $shiftsToRender = ($activeShift === 0) ? range(1, $shiftCount) : [$activeShift];
            $renderedCount = count($shiftsToRender);
        @endphp

        <div class="grid grid-cols-1 {{ $renderedCount === 2 ? 'lg:grid-cols-2' : ($renderedCount >= 3 ? 'xl:grid-cols-3 lg:grid-cols-2' : 'grid-cols-1') }} gap-4 items-start">
            @foreach ($shiftsToRender as $s)
                @php
                    $sLabel = $effectiveHours[$s]['label'] ?? "Shift {$s}";
                    $sHours = $effectiveHours[$s]['hours'] ?? '07:00 - 16:00';
                    $timeParts = explode('-', $sHours);
                    $defaultStart = $this->normalizeTime(trim($timeParts[0] ?? '07:00')) ?: '07:00';
                    $defaultEnd = $this->normalizeTime(trim($timeParts[1] ?? '16:00')) ?: '16:00';
                    $assignedOperatorsInShift = $shiftAssignments[$s] ?? [];
                    $operatorCount = count($assignedOperatorsInShift);
                    $isMpMet = ($currentLine->mp_standard > 0) ? ($operatorCount >= $currentLine->mp_standard) : true;
                @endphp

                <div class="card bg-base-100 border border-base-300 shadow-xs overflow-hidden flex flex-col">
                    <!-- Shift Header Bar -->
                    <div class="p-2.5 bg-base-200/50 border-b border-base-200 flex justify-between items-center gap-2">
                        <div class="min-w-0">
                            <h2 class="font-bold text-xs leading-none">{{ strtoupper($sLabel) }}</h2>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="font-mono text-xs text-base-content/60 font-medium mr-1">
                                {{ $operatorCount }} / {{ $currentLine->mp_standard }} MP
                            </span>

                            @if ($operatorCount < $currentLine->mp_standard)
                                <button 
                                    wire:click="quickFillManpowerOnly({{ $s }})" 
                                    class="btn btn-ghost btn-xs border border-base-300 shadow-xs gap-1 text-[11px] font-semibold hover:border-teal-600 hover:text-teal-700"
                                    title="Isi otomatis operator hingga standar MP untuk Shift {{ $s }} (tanpa station)"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <span>Auto MP</span>
                                </button>
                            @endif

                            <button 
                                wire:click="openAddOperatorModal({{ $s }})" 
                                class="btn btn-neutral btn-xs shadow-xs gap-1 font-semibold"
                                title="Tambah Operator ke Shift {{ $s }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Operator</span>
                            </button>
                        </div>
                    </div>

                    <!-- Operator Table -->
                    <div class="overflow-x-auto">
                        <table class="table table-xs table-zebra w-full text-xs">
                            <thead class="bg-base-200/80 font-bold uppercase text-[10px] text-base-content/70">
                                <tr>
                                    <th class="w-8 text-center px-1">No</th>
                                    <th class="w-14 px-1 whitespace-nowrap">Badge</th>
                                    <th class="min-w-[100px] px-2">Nama</th>
                                    <th class="px-2">Station</th>
                                    <th class="w-24 text-center px-1">Start Shift</th>
                                    <th class="w-24 text-center px-1">End Shift</th>
                                    <th class="w-8 text-center px-1"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-base-200">
                                @forelse ($assignedOperatorsInShift as $mpId => $assignedStationIds)
                                    @php
                                        $op = $assignedMap[$mpId] ?? null;
                                        $opKey = "{$s}_{$mpId}";
                                        $opStatus = $validationResults['operator_statuses'][$opKey] ?? null;

                                        $hasSkillMismatch = false;
                                        if ($op && !empty($assignedStationIds)) {
                                             foreach ($assignedStationIds as $stCheckId) {
                                                $stObj = $stationsMap[$stCheckId] ?? null;
                                                if ($stObj && !$op->isRelevantToStation($stObj->nama)) {
                                                    $hasSkillMismatch = true;
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    <tr class="hover:bg-base-200/30">
                                        <!-- No -->
                                        <td class="text-center font-mono text-base-content/50 px-1 text-[11px]">
                                            {{ $loop->iteration }}
                                        </td>

                                        <!-- Badge ID -->
                                        <td class="font-mono font-bold text-primary text-[11px] px-1 whitespace-nowrap">
                                            {{ $op->badge_id ?? "ID #{$mpId}" }}
                                        </td>

                                        <!-- Nama Operator -->
                                        <td class="px-2">
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold text-xs" title="{{ $op->nama ?? '' }}">{{ $op->nama ?? 'Unknown Operator' }}</span>

                                                @if ($hasSkillMismatch || ($opStatus && in_array($opStatus['type'], ['warning', 'info'])))
                                                    <div class="dropdown dropdown-top dropdown-end inline-block">
                                                        <button 
                                                            tabindex="0" 
                                                            type="button" 
                                                            class="btn btn-ghost btn-circle btn-xs text-warning/70 hover:text-warning w-4 h-4 min-h-0 p-0"
                                                            title="Peringatan station"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        </button>
                                                        <div tabindex="0" class="dropdown-content z-30 menu p-2 shadow-lg bg-base-100 rounded-box border border-warning/30 text-xs w-max whitespace-nowrap text-warning font-medium">
                                                            <div class="flex items-center gap-1.5">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                                </svg>
                                                                <span>tidak sesuai station utama</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Penugasan Station (Badges + Dropdown List) -->
                                        <td class="p-1.5">
                                            <div class="flex flex-wrap items-center gap-1">
                                                <!-- Currently Assigned Station Badges -->
                                                @foreach ($assignedStationIds as $stId)
                                                    @php
                                                        $stModel = $stationsMap[$stId] ?? null;
                                                        $stName = $stModel->nama ?? "Station #{$stId}";
                                                        $isRel = $op ? $op->isRelevantToStation($stName) : false;
                                                        
                                                        // Hitung berapa orang yang menjalankan station ini di shift ini
                                                        $operatorsAtThisStation = $stationCoverage[$s][$stId] ?? [];
                                                        $countAtStation = count($operatorsAtThisStation);
                                                    @endphp
                                                    <div class="badge badge-neutral badge-xs gap-1 py-2 font-medium shadow-xs">
                                                        <span>{{ $stName }}</span>
                                                        @if ($countAtStation > 1)
                                                            <span class="badge badge-ghost badge-xs bg-black/20 text-white text-[8px] px-1" title="{{ $countAtStation }} operator di station ini">
                                                                {{ $countAtStation }}P
                                                            </span>
                                                        @endif
                                                        <button 
                                                            wire:click="removeStationFromOperator({{ $s }}, {{ $mpId }}, {{ $stId }})" 
                                                            class="hover:text-white ml-0.5 font-bold"
                                                            title="Hapus penugasan station ini"
                                                        >
                                                            ✕
                                                        </button>
                                                    </div>
                                                @endforeach

                                                <!-- Dropdown to Add / Assign Station (Dynamic Position, Auto-flip, Fixed Viewport) -->
                                                <div 
                                                    x-data="{
                                                        open: false,
                                                        dropStyle: {},
                                                        init() {
                                                            window.addEventListener('scroll', (e) => {
                                                                if (this.open && this.$refs.menu && !this.$refs.menu.contains(e.target)) {
                                                                    this.open = false;
                                                                }
                                                            }, true);
                                                        },
                                                        toggle(btn) {
                                                            if (this.open) {
                                                                this.open = false;
                                                                return;
                                                            }
                                                            this.open = true;
                                                            this.$nextTick(() => {
                                                                const rect = btn.getBoundingClientRect();
                                                                const menu = this.$refs.menu;
                                                                const menuHeight = menu ? menu.offsetHeight : 180;
                                                                const menuWidth = menu ? menu.offsetWidth : 192;
                                                                const spaceBelow = window.innerHeight - rect.bottom;
                                                                const spaceAbove = rect.top;

                                                                let top = 'auto', bottom = 'auto';
                                                                if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
                                                                    bottom = (window.innerHeight - rect.top + 4) + 'px';
                                                                } else {
                                                                    top = (rect.bottom + 4) + 'px';
                                                                }

                                                                let left = 'auto', right = 'auto';
                                                                if (rect.left + menuWidth > window.innerWidth - 12) {
                                                                    right = Math.max(8, window.innerWidth - rect.right) + 'px';
                                                                } else {
                                                                    left = Math.max(8, rect.left) + 'px';
                                                                }

                                                                this.dropStyle = {
                                                                    position: 'fixed',
                                                                    top: top,
                                                                    bottom: bottom,
                                                                    left: left,
                                                                    right: right,
                                                                    zIndex: 9999
                                                                };
                                                            });
                                                        }
                                                    }"
                                                    @click.outside="open = false"
                                                    @keydown.escape.window="open = false"
                                                    class="relative inline-block"
                                                >
                                                    @if (empty($assignedStationIds))
                                                        <button 
                                                            type="button" 
                                                            @click="toggle($el)"
                                                            class="btn btn-ghost btn-xs text-[10px] font-medium text-base-content/60 hover:text-primary border border-dashed border-base-300 hover:border-primary/50 h-5 min-h-0 px-2 py-0 normal-case gap-1"
                                                            title="Set station untuk operator ini"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                                            </svg>
                                                            <span>set station</span>
                                                        </button>
                                                    @else
                                                        <button 
                                                            type="button" 
                                                            @click="toggle($el)"
                                                            class="btn btn-ghost btn-xs btn-circle border border-base-300 hover:border-neutral hover:bg-base-200 text-base-content/60 hover:text-base-content w-5 h-5 min-h-0 p-0"
                                                            title="Tambah station"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                                            </svg>
                                                        </button>
                                                    @endif

                                                    <div 
                                                        x-show="open" 
                                                        x-ref="menu"
                                                        x-cloak
                                                        :style="dropStyle"
                                                        class="shadow-xl bg-base-100 rounded-box border border-base-200 w-48 max-h-56 overflow-y-auto py-1"
                                                        style="display: none;"
                                                    >
                                                        <ul class="menu menu-xs p-1 gap-0.5">
                                                            @php
                                                                $availableStations = $currentLine->stations->reject(fn($st) => in_array($st->id, $assignedStationIds, true));
                                                            @endphp
                                                            @forelse ($availableStations as $optSt)
                                                                @php
                                                                    $othersAtStation = count($stationCoverage[$s][$optSt->id] ?? []);
                                                                @endphp
                                                                <li>
                                                                    <button 
                                                                        type="button"
                                                                        wire:click="addStationToOperator({{ $s }}, {{ $mpId }}, {{ $optSt->id }})" 
                                                                        @click="open = false"
                                                                        class="flex justify-between items-center py-1.5 px-2 text-xs hover:bg-base-200 rounded gap-2"
                                                                    >
                                                                        <span class="font-medium truncate">{{ $optSt->nama }}</span>
                                                                        @if ($othersAtStation > 0)
                                                                            <span class="badge badge-ghost badge-xs font-mono text-[9px] opacity-70 shrink-0">
                                                                                {{ $othersAtStation }} MP
                                                                            </span>
                                                                        @endif
                                                                    </button>
                                                                </li>
                                                            @empty
                                                                <li class="px-2 py-2 text-[10px] text-base-content/40 italic text-center">
                                                                    Semua station sudah dipilih
                                                                </li>
                                                            @endforelse
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        @php
                                            $startVal = $shiftTimes[$s][$mpId]['start'] ?? '';
                                            $endVal = $shiftTimes[$s][$mpId]['end'] ?? '';

                                            $startToParse = $startVal ?: $defaultStart;
                                            $startParts = explode(':', str_replace('.', ':', $startToParse));
                                            $startH = (int) ($startParts[0] ?? 7);
                                            $startM = (int) ($startParts[1] ?? 0);

                                            $endToParse = $endVal ?: $defaultEnd;
                                            $endParts = explode(':', str_replace('.', ':', $endToParse));
                                            $endH = (int) ($endParts[0] ?? 16);
                                            $endM = (int) ($endParts[1] ?? 0);
                                        @endphp

                                        <!-- Start Shift (Number Input Jam 24 & Menit 60 tanpa button up-down) -->
                                        <td class="px-1 text-center">
                                            <div 
                                                x-data="{
                                                    h: '{{ sprintf('%02d', $startH) }}',
                                                    m: '{{ sprintf('%02d', $startM) }}',
                                                    sync() {
                                                        let hour = parseInt(this.h);
                                                        if (isNaN(hour)) hour = {{ $startH }};
                                                        hour = Math.min(23, Math.max(0, hour));
                                                        this.h = String(hour).padStart(2, '0');

                                                        let minute = parseInt(this.m);
                                                        if (isNaN(minute)) minute = {{ $startM }};
                                                        minute = Math.min(59, Math.max(0, minute));
                                                        this.m = String(minute).padStart(2, '0');

                                                        $wire.set('shiftTimes.{{ $s }}.{{ $mpId }}.start', `${this.h}:${this.m}`);
                                                        let currentEnd = $wire.get('shiftTimes.{{ $s }}.{{ $mpId }}.end');
                                                        if (!currentEnd) {
                                                            $wire.set('shiftTimes.{{ $s }}.{{ $mpId }}.end', '{{ $defaultEnd }}');
                                                        }
                                                    },
                                                    stepH(d) {
                                                        let hour = parseInt(this.h);
                                                        if (isNaN(hour)) hour = {{ $startH }};
                                                        hour = (hour + d + 24) % 24;
                                                        this.h = String(hour).padStart(2, '0');
                                                        this.sync();
                                                    },
                                                    stepM(d) {
                                                        let minute = parseInt(this.m);
                                                        if (isNaN(minute)) minute = {{ $startM }};
                                                        minute = (minute + d + 60) % 60;
                                                        this.m = String(minute).padStart(2, '0');
                                                        this.sync();
                                                    }
                                                }"
                                                class="inline-flex items-center justify-center h-6 w-20 px-1 bg-base-100 rounded border border-base-300 focus-within:border-black focus-within:border-[1px] transition-colors"
                                                style="outline: none !important; box-shadow: none !important;"
                                                title="Jam Mulai Shift (default: {{ $defaultStart }})"
                                            >
                                                <input 
                                                    type="number" 
                                                    min="0" 
                                                    max="23" 
                                                    x-model="h"
                                                    @keydown.up.prevent="stepH(1)"
                                                    @keydown.down.prevent="stepH(-1)"
                                                    @change="sync()" 
                                                    @blur="sync()"
                                                    @keydown.enter.prevent="$el.blur()"
                                                    class="w-6 text-center font-mono text-xs bg-transparent border-none p-0 focus:outline-none focus:ring-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                                    style="outline: none !important; box-shadow: none !important;"
                                                />
                                                <span class="font-bold text-xs text-base-content/40 px-0.5 select-none">:</span>
                                                <input 
                                                    type="number" 
                                                    min="0" 
                                                    max="59" 
                                                    x-model="m"
                                                    @keydown.up.prevent="stepM(1)"
                                                    @keydown.down.prevent="stepM(-1)"
                                                    @change="sync()" 
                                                    @blur="sync()"
                                                    @keydown.enter.prevent="$el.blur()"
                                                    class="w-6 text-center font-mono text-xs bg-transparent border-none p-0 focus:outline-none focus:ring-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                                    style="outline: none !important; box-shadow: none !important;"
                                                />
                                            </div>
                                        </td>

                                        <!-- End Shift (Number Input Jam 24 & Menit 60 tanpa button up-down) -->
                                        <td class="px-1 text-center">
                                            <div 
                                                x-data="{
                                                    h: '{{ sprintf('%02d', $endH) }}',
                                                    m: '{{ sprintf('%02d', $endM) }}',
                                                    sync() {
                                                        let hour = parseInt(this.h);
                                                        if (isNaN(hour)) hour = {{ $endH }};
                                                        hour = Math.min(23, Math.max(0, hour));
                                                        this.h = String(hour).padStart(2, '0');

                                                        let minute = parseInt(this.m);
                                                        if (isNaN(minute)) minute = {{ $endM }};
                                                        minute = Math.min(59, Math.max(0, minute));
                                                        this.m = String(minute).padStart(2, '0');

                                                        $wire.set('shiftTimes.{{ $s }}.{{ $mpId }}.end', `${this.h}:${this.m}`);
                                                        let currentStart = $wire.get('shiftTimes.{{ $s }}.{{ $mpId }}.start');
                                                        if (!currentStart) {
                                                            $wire.set('shiftTimes.{{ $s }}.{{ $mpId }}.start', '{{ $defaultStart }}');
                                                        }
                                                    },
                                                    stepH(d) {
                                                        let hour = parseInt(this.h);
                                                        if (isNaN(hour)) hour = {{ $endH }};
                                                        hour = (hour + d + 24) % 24;
                                                        this.h = String(hour).padStart(2, '0');
                                                        this.sync();
                                                    },
                                                    stepM(d) {
                                                        let minute = parseInt(this.m);
                                                        if (isNaN(minute)) minute = {{ $endM }};
                                                        minute = (minute + d + 60) % 60;
                                                        this.m = String(minute).padStart(2, '0');
                                                        this.sync();
                                                    }
                                                }"
                                                class="inline-flex items-center justify-center h-6 w-20 px-1 bg-base-100 rounded border border-base-300 focus-within:border-black focus-within:border-[1px] transition-colors"
                                                style="outline: none !important; box-shadow: none !important;"
                                                title="Jam Selesai Shift (default: {{ $defaultEnd }})"
                                            >
                                                <input 
                                                    type="number" 
                                                    min="0" 
                                                    max="23" 
                                                    x-model="h"
                                                    @keydown.up.prevent="stepH(1)"
                                                    @keydown.down.prevent="stepH(-1)"
                                                    @change="sync()" 
                                                    @blur="sync()"
                                                    @keydown.enter.prevent="$el.blur()"
                                                    class="w-6 text-center font-mono text-xs bg-transparent border-none p-0 focus:outline-none focus:ring-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                                    style="outline: none !important; box-shadow: none !important;"
                                                />
                                                <span class="font-bold text-xs text-base-content/40 px-0.5 select-none">:</span>
                                                <input 
                                                    type="number" 
                                                    min="0" 
                                                    max="59" 
                                                    x-model="m"
                                                    @keydown.up.prevent="stepM(1)"
                                                    @keydown.down.prevent="stepM(-1)"
                                                    @change="sync()" 
                                                    @blur="sync()"
                                                    @keydown.enter.prevent="$el.blur()"
                                                    class="w-6 text-center font-mono text-xs bg-transparent border-none p-0 focus:outline-none focus:ring-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                                    style="outline: none !important; box-shadow: none !important;"
                                                />
                                            </div>
                                        </td>

                                        <!-- Aksi Hapus Operator dari Shift -->
                                        <td class="text-center px-1">
                                            <button 
                                                wire:click="removeOperatorFromShift({{ $s }}, {{ $mpId }})" 
                                                class="btn btn-ghost btn-xs text-base-content/40 hover:text-error p-0.5"
                                                title="Hapus operator dari shift ini"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-6 text-base-content/40 text-xs">
                                            Belum ada operator di Shift {{ $s }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Modal: Tambah Operator ke Shift -->
    @if ($showAddOperatorModal && $currentLine)
        <div class="modal modal-open">
            <div class="modal-box max-w-xl max-h-[85vh] flex flex-col p-4">
                <!-- Modal Header -->
                <div class="flex justify-between items-start pb-3 border-b border-base-200">
                    <div>
                        <h3 class="font-bold text-sm">Pilih Operator untuk Shift {{ $targetShiftForAdd }}</h3>
                        <p class="text-[11px] text-base-content/60 mt-0.5">
                            Line <strong class="text-base-content">{{ $currentLine->nama }}</strong> · 
                            Pilih operator aktif untuk dimasukkan ke shift ini.
                        </p>
                    </div>
                    <button wire:click="$set('showAddOperatorModal', false)" class="btn btn-sm btn-ghost btn-circle">✕</button>
                </div>

                <!-- Search Input -->
                <div class="py-3">
                    <div class="relative">
                        <input 
                            type="text" 
                            wire:model.live.debounce.250ms="operatorSearch" 
                            placeholder="Cari badge ID atau nama operator..." 
                            class="input input-bordered input-sm w-full pl-8 text-base sm:text-xs"
                            autofocus
                        />
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-2.5 top-2.5 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Operators List -->
                <div class="flex-1 overflow-y-auto divide-y divide-base-200 pr-1">
                    @forelse ($activeOperators as $op)
                        @php
                            $shiftInThisLine = $assignedInThisLine[$op->id] ?? null;
                            $otherLineInfo = $assignedInOtherLines[$op->id] ?? null;
                            $isOccupied = ($shiftInThisLine !== null) || ($otherLineInfo !== null);
                        @endphp
                        <div 
                            class="p-2.5 rounded flex justify-between items-center transition-colors {{ $isOccupied ? 'opacity-40 cursor-not-allowed bg-base-200/30' : 'cursor-pointer hover:bg-base-200/60' }}"
                            @if (!$isOccupied) wire:click="addOperatorToShift({{ $op->id }})" @endif
                        >
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-xs {{ $isOccupied ? 'text-base-content/50' : 'text-primary' }}">{{ $op->badge_id }}</span>
                                    <span class="font-semibold text-xs {{ $isOccupied ? 'text-base-content/60' : 'text-base-content' }}">{{ $op->nama }}</span>
                                    @if ($shiftInThisLine !== null)
                                        @if ($shiftInThisLine === $targetShiftForAdd)
                                            <span class="badge badge-ghost badge-xs text-base-content/60">Sudah Ada di Shift Ini</span>
                                        @else
                                            <span class="badge badge-ghost badge-xs text-base-content/60">Sudah di Shift {{ $shiftInThisLine }}</span>
                                        @endif
                                    @elseif ($otherLineInfo !== null)
                                        <span class="badge badge-ghost badge-xs text-base-content/60">Sudah di Line {{ $otherLineInfo['line_name'] }} (Shift {{ $otherLineInfo['shift'] }})</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-base-content/60 flex gap-2">
                                    <span>Preferensi: {{ implode(', ', $op->station_preferences) ?: 'Semua Station' }}</span>
                                </div>
                            </div>
                            <div>
                                @if (!$isOccupied)
                                    <button class="btn btn-neutral btn-xs">
                                        + Tambah
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-base-content/50 text-xs">
                            Tidak ada operator aktif yang cocok dengan pencarian.
                        </div>
                    @endforelse
                </div>

                <!-- Modal Footer -->
                <div class="pt-3 border-t border-base-200 flex justify-between items-center text-[11px] text-base-content/60">
                    <span>Operator yang sudah terjadwal di shift atau line lain otomatis dinonaktifkan.</span>
                    <button wire:click="$set('showAddOperatorModal', false)" class="btn btn-sm btn-ghost">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="$set('showAddOperatorModal', false)"></div>
        </div>
    @endif

    <!-- Modal: Cross-Line Overview -->
    @if ($showCrossLineModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-4xl max-h-[85vh] flex flex-col p-4">
                <div class="flex justify-between items-start pb-3 border-b border-base-200">
                    <div>
                        <h3 class="font-bold text-base">Rekap Penugasan Seluruh Line</h3>
                        <p class="text-xs text-base-content/60 mt-0.5">
                            Periode Minggu: {{ Carbon\Carbon::parse($weekStart)->format('d M Y') }} s/d {{ Carbon\Carbon::parse($weekStart)->endOfWeek()->format('d M Y') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <a 
                            href="{{ route('roster.view.merged', ['week_start' => $weekStart]) }}" 
                            target="_blank" 
                            class="btn btn-neutral btn-xs font-semibold gap-1"
                            title="Lihat PDF gabungan di tab baru tanpa unduh"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>Lihat PDF</span>
                        </a>
                        <a 
                            href="{{ route('roster.export.merged', ['week_start' => $weekStart]) }}" 
                            class="btn btn-outline btn-xs font-semibold gap-1"
                            title="Unduh file PDF gabungan ke perangkat"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Unduh PDF</span>
                        </a>
                        <button wire:click="$set('showCrossLineModal', false)" class="btn btn-sm btn-ghost btn-circle">✕</button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto py-3 px-1.5 space-y-4">
                    @forelse ($crossLineRosters as $clr)
                        <div class="card bg-base-100 border p-3 shadow-xs transition-colors {{ $clr->line->id === $selectedLineId ? 'border-teal-600 bg-teal-50/20' : 'border-base-300' }}">
                            <div class="flex flex-wrap justify-between items-center mb-2 pb-1.5 border-b border-base-200 gap-2">
                                <div class="flex items-center gap-2">
                                    <button 
                                        type="button" 
                                        wire:click="navigateToLine({{ $clr->line->id }})"
                                        class="font-bold text-sm hover:text-teal-700 transition-colors text-left flex items-center gap-1.5 cursor-pointer"
                                        title="Buka dan edit Line {{ $clr->line->nama }}"
                                    >
                                        <span>Line {{ $clr->line->nama }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 opacity-60 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </button>
                                    @if ($clr->status === 'final')
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded-sm border border-emerald-600 text-emerald-600 bg-transparent text-[10px] font-bold uppercase">FINAL</span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded-sm border border-base-content/40 text-base-content/70 bg-transparent text-[10px] font-bold uppercase">DRAFT</span>
                                    @endif
                                </div>
                                <div class="text-xs text-base-content/60">
                                    Leader: <strong>{{ $clr->leader->nama ?? '-' }}</strong>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="table table-xs table-zebra w-full text-[11px]">
                                    <thead>
                                        <tr class="bg-base-200/50">
                                            <th class="w-16">Shift</th>
                                            <th class="w-20">Badge</th>
                                            <th>Nama Operator</th>
                                            <th>Station</th>
                                            <th class="w-28 text-center">Waktu Kerja</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($clr->entries as $clEntry)
                                            <tr>
                                                <td class="font-mono">Shift {{ $clEntry->shift }}</td>
                                                <td class="font-mono font-bold">{{ $clEntry->manpower->badge_id ?? '-' }}</td>
                                                <td class="font-semibold">{{ $clEntry->manpower->nama ?? '-' }}</td>
                                                <td>{{ $clEntry->station->nama ?? '-' }}</td>
                                                <td class="text-center font-mono text-[10px]">
                                                    @php
                                                        $clLine = $clr->line;
                                                        $clShiftHours = $clLine ? ($clLine->effective_shift_hours[$clEntry->shift]['hours'] ?? 'Default') : 'Default';
                                                        $clDefStart = '07:00';
                                                        $clDefEnd = '16:00';
                                                        if ($clShiftHours !== 'Default' && str_contains($clShiftHours, '-')) {
                                                            $clParts = explode('-', $clShiftHours);
                                                            $clDefStart = trim(str_replace('.', ':', $clParts[0]));
                                                            $clDefEnd = trim(str_replace('.', ':', $clParts[1]));
                                                        }
                                                        $hasClCustom = !empty($clEntry->start_shift) || !empty($clEntry->end_shift);
                                                        $clStart = $clEntry->start_shift ?: $clDefStart;
                                                        $clEnd = $clEntry->end_shift ?: $clDefEnd;
                                                    @endphp
                                                    @if ($hasClCustom)
                                                        <span class="text-black font-bold">{{ $clStart }} - {{ $clEnd }}</span>
                                                    @else
                                                        <span class="text-base-content/40">Default</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-base-content/40 py-2">Belum ada entri jadwal.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-base-content/50 text-xs">
                            Belum ada line yang menyusun roster pada minggu ini.
                        </div>
                    @endforelse
                </div>

                <div class="pt-3 border-t border-base-200 text-right">
                    <button wire:click="$set('showCrossLineModal', false)" class="btn btn-sm btn-ghost">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="$set('showCrossLineModal', false)"></div>
        </div>
    @endif

    <!-- Modal: Quick Edit Line -->
    @if ($showEditLineModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-lg">
                <div class="flex justify-between items-center mb-3 pb-2 border-b border-base-200">
                    <h3 class="font-bold text-base">Edit Line Produksi</h3>
                    <button type="button" wire:click="$set('showEditLineModal', false)" class="btn btn-xs btn-ghost btn-circle">✕</button>
                </div>
                
                <form wire:submit.prevent="saveEditedLine" class="space-y-3">
                    <div>
                        <label class="label label-text text-xs font-semibold">Nama Line *</label>
                        <input type="text" wire:model="editLineNama" placeholder="Contoh: EV10, NTC" class="input input-bordered input-sm w-full text-base sm:text-xs uppercase" />
                        @error('editLineNama') <span class="text-error text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="label label-text text-xs font-semibold">MP Standard (Operator) *</label>
                            <input type="number" wire:model="editLineMpStandard" min="1" class="input input-bordered input-sm w-full text-base sm:text-xs font-mono" />
                            @error('editLineMpStandard') <span class="text-error text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="label label-text text-xs font-semibold">Status Line</label>
                            <select wire:model="editLineStatus" class="select select-bordered select-sm w-full text-base sm:text-xs">
                                <option value="aktif">Aktif</option>
                                <option value="off">Off</option>
                                <option value="waiting_material">Waiting Material</option>
                            </select>
                            @error('editLineStatus') <span class="text-error text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="label label-text text-xs font-semibold">Pola Kerja</label>
                        <select wire:model.live="editLinePolaKerja" class="select select-bordered select-sm w-full text-base sm:text-xs">
                            <option value="5_days_2_shift">5 Hari Kerja (2 Shift)</option>
                            <option value="6_days_3_shift">6 Hari Kerja (3 Shift)</option>
                        </select>
                        @error('editLinePolaKerja') <span class="text-error text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Jam Shift Config -->
                    <div class="bg-base-200/50 p-3 rounded-lg space-y-2 border border-base-200">
                        <span class="text-[11px] font-bold uppercase text-base-content/60">Pengaturan Jam Kerja Shift</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] text-base-content/60">Shift 1 (Mulai - Selesai)</label>
                                <div class="flex gap-1 items-center">
                                    <input type="text" wire:model="editLineShift1Start" class="input input-bordered input-xs w-full font-mono text-xs" />
                                    <span>-</span>
                                    <input type="text" wire:model="editLineShift1End" class="input input-bordered input-xs w-full font-mono text-xs" />
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] text-base-content/60">Shift 2 (Mulai - Selesai)</label>
                                <div class="flex gap-1 items-center">
                                    <input type="text" wire:model="editLineShift2Start" class="input input-bordered input-xs w-full font-mono text-xs" />
                                    <span>-</span>
                                    <input type="text" wire:model="editLineShift2End" class="input input-bordered input-xs w-full font-mono text-xs" />
                                </div>
                            </div>
                        </div>

                        @if ($editLinePolaKerja === '6_days_3_shift')
                            <div class="pt-1">
                                <label class="text-[10px] text-base-content/60">Shift 3 (Mulai - Selesai)</label>
                                <div class="flex gap-1 items-center max-w-[240px]">
                                    <input type="text" wire:model="editLineShift3Start" class="input input-bordered input-xs w-full font-mono text-xs" />
                                    <span>-</span>
                                    <input type="text" wire:model="editLineShift3End" class="input input-bordered input-xs w-full font-mono text-xs" />
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="modal-action mt-4 flex justify-between items-center">
                        <div>
                            @if ($selectedLineId)
                                <button 
                                    type="button" 
                                    wire:click="deleteCurrentLine" 
                                    wire:confirm="Yakin ingin menghapus Line ini dari sistem? Seluruh stasiun dan riwayat roster pada line ini akan ikut terhapus!" 
                                    class="btn btn-xs btn-error btn-outline"
                                >
                                    Hapus Line
                                </button>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <button type="button" wire:click="$set('showEditLineModal', false)" class="btn btn-xs btn-ghost">Batal</button>
                            <button type="submit" class="btn btn-xs btn-neutral">Simpan Line</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="$set('showEditLineModal', false)"></div>
        </div>
    @endif
    @endif
</div>
