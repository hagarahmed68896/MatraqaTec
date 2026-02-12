@props(['status'])

@php
    $styles = [
        'new' => 'bg-blue-50 text-blue-600 border border-blue-100',
        'accepted' => 'bg-indigo-50 text-indigo-600 border border-indigo-100',
        'in_progress' => 'bg-yellow-50 text-yellow-600 border border-yellow-100',
        'completed' => 'bg-green-50 text-green-600 border border-green-100',
        'rejected' => 'bg-red-50 text-red-600 border border-red-100',
        'cancelled' => 'bg-slate-50 text-slate-600 border border-slate-100',
        'scheduled' => 'bg-orange-50 text-orange-600 border border-orange-100',
    ];

    $labels = [
        'new' => __('New'),
        'accepted' => __('Accepted'),
        'in_progress' => __('In Progress'),
        'completed' => __('Completed'),
        'rejected' => __('Rejected'),
        'cancelled' => __('Cancelled'),
        'scheduled' => __('Scheduled'),
    ];

    $style = $styles[$status] ?? 'bg-slate-50 text-slate-600 border border-slate-100';
    $label = $labels[$status] ?? $status;
@endphp

<span class="px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $style }}">
    {{ $label }}
</span>
