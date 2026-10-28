@props([
    'status',
    'text' => null,
])

@php
    $statusConfig = [
        'pending' => ['bg' => 'bg-warning-subtle', 'textColor' => 'text-warning-emphasis', 'border' => 'border-warning-subtle', 'label' => $text ?? 'Belum Diproses'],
        'processed' => ['bg' => 'bg-success-subtle', 'textColor' => 'text-success-emphasis', 'border' => 'border-success-subtle', 'label' => $text ?? 'Sudah Diproses'],
        'active' => ['bg' => 'bg-primary-subtle', 'textColor' => 'text-primary-emphasis', 'border' => 'border-primary-subtle', 'label' => $text ?? 'Aktif'],
        'closed' => ['bg' => 'bg-success-subtle', 'textColor' => 'text-success-emphasis', 'border' => 'border-success-subtle', 'label' => $text ?? 'Selesai'],
    ];
    $config = $statusConfig[$status] ?? $statusConfig['pending'];
@endphp

<span class="badge {{ $config['bg'] }} {{ $config['textColor'] }} border {{ $config['border'] }} px-2 py-1 fw-semibold">
    {{ $config['label'] }}
</span>