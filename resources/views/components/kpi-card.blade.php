@props([
    'title',
    'value',
    'subtitle' => '',
    'threshold' => null,
    'thresholdType' => 'min', // 'min' or 'max'
    'icon' => null,
    'tooltip' => null,
])

@php
// Determina a cor baseada no threshold
$statusColor = 'gray';
if ($threshold !== null && is_numeric($value)) {
    $numericValue = (float) str_replace([',', '.'], ['', '.'], str_replace('.', '', $value));
    if ($thresholdType === 'min') {
        // Valores maiores que threshold são bons
        if ($numericValue >= $threshold['good']) {
            $statusColor = 'green';
        } elseif ($numericValue >= $threshold['warning']) {
            $statusColor = 'yellow';
        } else {
            $statusColor = 'red';
        }
    } else {
        // Valores menores que threshold são bons (ex: taxa de inadimplência)
        if ($numericValue <= $threshold['good']) {
            $statusColor = 'green';
        } elseif ($numericValue <= $threshold['warning']) {
            $statusColor = 'yellow';
        } else {
            $statusColor = 'red';
        }
    }
}

$borderColors = [
    'green' => 'border-green-500',
    'yellow' => 'border-yellow-500',
    'red' => 'border-red-500',
    'gray' => 'border-zinc-400 dark:border-zinc-600',
];

$iconBgColors = [
    'green' => 'bg-green-50 dark:bg-green-950/30',
    'yellow' => 'bg-yellow-50 dark:bg-yellow-950/30',
    'red' => 'bg-red-50 dark:bg-red-950/30',
    'gray' => 'bg-zinc-100 dark:bg-zinc-800/50',
];

$iconColors = [
    'green' => 'text-green-600 dark:text-green-400',
    'yellow' => 'text-yellow-600 dark:text-yellow-400',
    'red' => 'text-red-600 dark:text-red-400',
    'gray' => 'text-zinc-600 dark:text-zinc-400',
];

$borderClass = $borderColors[$statusColor];
$iconBgClass = $iconBgColors[$statusColor];
$iconColorClass = $iconColors[$statusColor];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-zinc-900 rounded-xl shadow-xs hover:shadow-md transition-all duration-300 p-6 border-l-4 border-y border-r border-y-zinc-200 border-r-zinc-200 dark:border-y-zinc-800 dark:border-r-zinc-800 ' . $borderClass]) }}>
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <div class="flex items-center gap-2">
                <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                    {{ $title }}
                </p>
                @if($tooltip)
                    <div x-data="{ show: false }" class="relative">
                        <button @mouseenter="show = true" @mouseleave="show = false" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 focus:outline-hidden">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                            </svg>
                        </button>
                        <div x-show="show"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute z-10 w-64 px-3 py-2 text-xs text-zinc-700 dark:text-zinc-200 bg-white dark:bg-zinc-800 rounded-lg shadow-lg border border-zinc-200 dark:border-zinc-700 bottom-full mb-2 left-1/2 transform -translate-x-1/2"
                             style="display: none;">
                            {{ $tooltip }}
                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                <div class="border-4 border-transparent border-t-white dark:border-t-zinc-800"></div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <p class="text-3xl font-bold mt-2 text-zinc-900 dark:text-white tracking-tight">
                {{ $value }}
            </p>
            @if($subtitle)
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        <div class="ml-4">
            <div class="w-14 h-14 rounded-xl {{ $iconBgClass }} flex items-center justify-center transition-colors">
                @if($icon)
                    <div class="{{ $iconColorClass }} [&>svg]:size-6">
                        {{ $icon }}
                    </div>
                @else
                    <svg class="w-6 h-6 {{ $iconColorClass }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v5.25c0 .621-.504 1.125-1.125 1.125h-2.25A1.125 1.125 0 013 18.375v-5.25zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125v-9.75zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v14.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                @endif
            </div>
        </div>
    </div>
</div>
