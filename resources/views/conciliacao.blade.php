<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-2">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white tracking-tight">Conciliação Bancária</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Integração de extratos bancários, cruzamento de dados e auditoria financeira.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 flex flex-col gap-4">
                <span class="text-xs font-semibold text-zinc-500 uppercase">Transações Pendentes</span>
                <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">12</p>
                <span class="text-xs text-zinc-400">Necessitam de conciliação</span>
            </div>
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 flex flex-col gap-4">
                <span class="text-xs font-semibold text-zinc-500 uppercase">Última Importação OFX</span>
                <p class="text-xl font-bold text-zinc-800 dark:text-zinc-100">Hoje, 09:34</p>
                <span class="text-xs text-green-600">Banco Itaú - Agência 0102</span>
            </div>
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 flex flex-col gap-4 md:col-span-2 lg:col-span-1">
                <span class="text-xs font-semibold text-zinc-500 uppercase">Divergências</span>
                <p class="text-3xl font-bold text-zinc-500">0</p>
                <span class="text-xs text-zinc-400">Tudo em conformidade</span>
            </div>
        </div>

        <div class="relative h-64 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 flex items-center justify-center">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/10 dark:stroke-neutral-100/10" />
            <span class="relative text-sm text-zinc-500 dark:text-zinc-400 font-medium">Fluxo de Caixa Bancário vs Sistema</span>
        </div>
    </div>
</x-layouts.app>
