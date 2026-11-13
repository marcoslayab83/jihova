@props([
    'status',
])

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border '.$status->badgeClasses()]) }}>
    {{ $status->label() }}
</span>
