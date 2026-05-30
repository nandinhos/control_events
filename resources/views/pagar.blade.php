<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-2">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white tracking-tight">Contas a Pagar</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Gerenciamento de despesas, taxas de fornecedores e cachês de artistas.</p>
            </div>
            <flux:button variant="primary" class="bg-neutral-800 hover:bg-neutral-900 dark:bg-white dark:text-stone-950 dark:hover:bg-zinc-100">
                Nova Despesa
            </flux:button>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <x-kpi-card title="Total Pago" value="R$ 195k" subtitle="Este mês" />
            <x-kpi-card title="A Pagar" value="R$ 82k" subtitle="Próximos 30 dias" />
            <x-kpi-card title="Aprovações Pendentes" value="4" subtitle="Ações administrativas" :threshold="['good' => 1, 'warning' => 3]" thresholdType="max" />
        </div>

        <!-- Tabela de Saídas -->
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden shadow-xs">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                <span class="font-semibold text-zinc-800 dark:text-zinc-200">Próximos Pagamentos de Cachês e Fornecedores</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400 font-medium">
                            <th class="p-4">Identificador</th>
                            <th class="p-4">Favorecido / Serviço</th>
                            <th class="p-4">Valor</th>
                            <th class="p-4">Vencimento</th>
                            <th class="p-4">Tipo</th>
                            <th class="p-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 text-zinc-900 dark:text-zinc-100">
                            <td class="p-4 font-mono text-xs">#PAG-2026-302</td>
                            <td class="p-4 font-medium">DJ Alok (Sinal de Contrato)</td>
                            <td class="p-4">R$ 75.000,00</td>
                            <td class="p-4">02/06/2026</td>
                            <td class="p-4 text-xs font-semibold text-purple-600 dark:text-purple-400">Cachê Artista</td>
                            <td class="p-4">
                                <x-status-badge type="payment-artist" status="pendente" />
                            </td>
                        </tr>
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 text-zinc-900 dark:text-zinc-100">
                            <td class="p-4 font-mono text-xs">#PAG-2026-301</td>
                            <td class="p-4 font-medium">Equipe de Som & Luz LineUp</td>
                            <td class="p-4">R$ 28.000,00</td>
                            <td class="p-4">27/05/2026</td>
                            <td class="p-4 text-xs font-semibold text-blue-600 dark:text-blue-400">Fornecedor</td>
                            <td class="p-4">
                                <x-status-badge type="payment" status="pago" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
