@php
    $status = $status ?? 'Pending';
    $label = $status === 'Approved' ? ($approvedLabel ?? 'Approved') : $status;
    $classes = match ($status) {
        'Approved' => 'bg-green-100 text-green-700',
        'Rejected' => 'bg-red-100 text-red-700',
        default => 'bg-yellow-100 text-yellow-700',
    };
@endphp

<span class="inline-flex flex-wrap items-center gap-2">
    <span class="inline-flex whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold {{ $classes }}">
        {{ $label }}
    </span>
    @if (! empty($churchReadAt))
        <span class="inline-flex whitespace-nowrap rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
            Read at the church
        </span>
    @endif
</span>
