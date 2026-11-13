@props([
    'size' => 'md',
    'showText' => true,
    'subtitle' => null,
])

@php
    $uid = uniqid('jh_');
    $iconDimensions = match ($size) {
        'sm' => 'w-9 h-7',
        'lg' => 'w-14 h-11',
        default => 'w-11 h-8',
    };
    $wordmarkClass = match ($size) {
        'sm' => 'text-base',
        'lg' => 'text-2xl',
        default => 'text-lg',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 select-none']) }}>
    <svg viewBox="0 0 88 56" fill="none" xmlns="http://www.w3.org/2000/svg" class="{{ $iconDimensions }} shrink-0 drop-shadow-[0_0_12px_rgba(0,240,255,0.35)]" aria-hidden="true">
        <defs>
            <linearGradient id="{{ $uid }}_cv" x1="6" y1="28" x2="82" y2="28" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#6C28FE" />
                <stop offset="48%" stop-color="#EC4899" />
                <stop offset="100%" stop-color="#00F0FF" />
            </linearGradient>
            <linearGradient id="{{ $uid }}_orb" x1="44" y1="4" x2="44" y2="52" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#6C28FE" />
                <stop offset="50%" stop-color="#00F0FF" />
                <stop offset="100%" stop-color="#6C28FE" />
            </linearGradient>
        </defs>
        <ellipse cx="44" cy="28" rx="14" ry="23" stroke="#6C28FE" stroke-width="5.5" opacity="0.88" />
        <path
            d="M23 14C14.163 14 7 20.268 7 28C7 35.732 14.163 42 23 42C34 42 54 14 65 14C73.837 14 81 20.268 81 28C81 35.732 73.837 42 65 42C54 42 34 14 23 14Z"
            stroke="url(#{{ $uid }}_cv)"
            stroke-width="6.5"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <path
            d="M44 5C51.732 5 58 15.297 58 28C58 40.703 51.732 51 44 51"
            stroke="url(#{{ $uid }}_orb)"
            stroke-width="5.5"
            stroke-linecap="round"
        />
    </svg>

    @if ($showText)
        <span class="min-w-0">
            <span class="block font-display font-extrabold tracking-[0.06em] leading-none jihova-gradient-text {{ $wordmarkClass }}">
                JIHOVA
            </span>
            @if ($subtitle)
                <span class="block text-[10px] font-medium tracking-wide text-slate-400 truncate mt-0.5">
                    {{ $subtitle }}
                </span>
            @endif
        </span>
    @endif
</span>
