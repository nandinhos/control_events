{{-- resources/views/components/status-badge.blade.php --}}
@props(['status', 'type' => 'default'])

@php
    $baseClasses = 'px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full';
    $processedStatus = strtolower(trim($status ?? ''));
    $processedType = strtolower(trim($type));

    // Configuração de cores por tipo e status
    $statusConfig = [
        'contract' => [
            'assinado' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
            'concluido' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
            'para_assinatura' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
            'expirado' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
            'cancelado' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
            'n/a' => 'bg-zinc-200 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400',
        ],
        'payment' => [
            'pago' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
            'confirmado' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
            'a_vencer' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
            'vencido' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
            'pendente' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
            'cancelado' => 'bg-zinc-300 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400',
        ],
        'payment-internal' => [
            'pago' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
            'pendente' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
        ],
        'payment-artist' => [
            'pago' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
            'pendente' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
        ],
        'payment-booker' => [
            'pago' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
            'pendente' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
        ],
        'cost-confirmation' => [
            'confirmado' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
            'pendente' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
        ],
        'reimbursement' => [
            'pago' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
            'aguardando_comprovante' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
        ],
    ];

    $defaultClasses = 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300';
    $colorClasses = $statusConfig[$processedType][$processedStatus] ?? $defaultClasses;

    // Preparar o texto a ser exibido
    $statusText = $processedStatus === 'n/a'
        ? 'N/A'
        : ucwords(str_replace(['_', '-'], ' ', $processedStatus));
@endphp

<span {{ $attributes->merge(['class' => $baseClasses . ' ' . $colorClasses]) }}>
    {{ $statusText }}
</span>
