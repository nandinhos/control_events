<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-2">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white tracking-tight">Módulo Internacional</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Gerenciamento de contratos em moeda estrangeira (USD/EUR), taxas cambiais e impostos internacionais de turnês.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 flex flex-col gap-2">
                <span class="text-xs font-semibold text-zinc-500 uppercase">Cotação Comercial USD</span>
                <p class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">R$ 5,221</p>
                <span class="text-xs text-green-600 font-semibold">-0.12% hoje</span>
            </div>
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 flex flex-col gap-2">
                <span class="text-xs font-semibold text-zinc-500 uppercase">Cotação Comercial EUR</span>
                <p class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">R$ 5,684</p>
                <span class="text-xs text-red-600 font-semibold">+0.24% hoje</span>
            </div>
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 flex flex-col gap-2">
                <span class="text-xs font-semibold text-zinc-500 uppercase">Projetos no Exterior</span>
                <p class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">2 Ativos</p>
                <span class="text-xs text-zinc-400">Miami & Lisboa</span>
            </div>
        </div>

        <div class="relative h-64 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 flex items-center justify-center">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/10 dark:stroke-neutral-100/10" />
            <span class="relative text-sm text-zinc-500 dark:text-zinc-400 font-medium">Histórico de Fechamento de Câmbio</span>
        </div>
    </div>
</x-layouts.app>
