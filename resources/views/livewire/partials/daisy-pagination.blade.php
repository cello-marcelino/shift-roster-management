@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $pageName = $paginator->getPageName();
        $maxNumbers = 4;

        if ($lastPage <= $maxNumbers) {
            $start = 1;
            $end = $lastPage;
        } else {
            $half = (int) floor($maxNumbers / 2);
            $start = $currentPage - ($half - 1);
            $end = $start + $maxNumbers - 1;

            if ($start < 1) {
                $start = 1;
                $end = $maxNumbers;
            }

            if ($end > $lastPage) {
                $end = $lastPage;
                $start = $lastPage - $maxNumbers + 1;
            }
        }

        $pages = range($start, $end);
        $hasLeftDots = $start > 1;
        $hasRightDots = $end < $lastPage;
    @endphp

    <nav 
        role="navigation" 
        aria-label="Pagination Navigation" 
        class="flex items-center" 
        wire:key="paginator-{{ $pageName }}-nav"
        x-data
        x-on:keydown.window="
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) return;
            if ($event.key === 'ArrowRight') { $wire.nextPage(); }
            if ($event.key === 'ArrowLeft') { $wire.previousPage(); }
        "
    >
        <div class="join shadow-xs">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <button 
                    type="button" 
                    disabled 
                    wire:key="paginator-{{ $pageName }}-prev-disabled"
                    class="join-item btn btn-xs btn-disabled text-base-content/30 cursor-not-allowed" 
                    aria-disabled="true"
                    title="Halaman Pertama"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            @else
                <button 
                    wire:key="paginator-{{ $pageName }}-prev-btn"
                    type="button" 
                    wire:click="previousPage" 
                    class="join-item btn btn-xs hover:btn-neutral/10" 
                    aria-label="{{ __('pagination.previous') }}"
                    title="Halaman Sebelumnya (Panah Kiri)"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            @endif

            {{-- Left Ellipsis Separator --}}
            @if ($hasLeftDots)
                <span 
                    wire:key="paginator-{{ $pageName }}-dots-left"
                    class="join-item btn btn-xs btn-disabled text-base-content/40 cursor-default"
                >
                    ...
                </span>
            @endif

            {{-- 4 Page Navigation Numbers --}}
            @foreach ($pages as $page)
                @if ($page == $currentPage)
                    <span 
                        wire:key="paginator-{{ $pageName }}-page-active-{{ $page }}"
                        class="join-item btn btn-xs btn-neutral font-bold font-mono" 
                        aria-current="page"
                    >
                        {{ $page }}
                    </span>
                @else
                    <button 
                        wire:key="paginator-{{ $pageName }}-page-btn-{{ $page }}"
                        type="button" 
                        wire:click="gotoPage({{ $page }})" 
                        class="join-item btn btn-xs btn-ghost hover:btn-neutral/10 font-mono text-base-content/75"
                    >
                        {{ $page }}
                    </button>
                @endif
            @endforeach

            {{-- Right Ellipsis Separator --}}
            @if ($hasRightDots)
                <span 
                    wire:key="paginator-{{ $pageName }}-dots-right"
                    class="join-item btn btn-xs btn-disabled text-base-content/40 cursor-default"
                >
                    ...
                </span>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button 
                    wire:key="paginator-{{ $pageName }}-next-btn"
                    type="button" 
                    wire:click="nextPage" 
                    class="join-item btn btn-xs hover:btn-neutral/10" 
                    aria-label="{{ __('pagination.next') }}"
                    title="Halaman Berikutnya (Panah Kanan)"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            @else
                <button 
                    type="button" 
                    disabled 
                    wire:key="paginator-{{ $pageName }}-next-disabled"
                    class="join-item btn btn-xs btn-disabled text-base-content/30 cursor-not-allowed" 
                    aria-disabled="true"
                    title="Halaman Terakhir"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            @endif
        </div>
    </nav>
@endif
