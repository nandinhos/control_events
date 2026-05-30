<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-2">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white tracking-tight">Contas a Receber</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Controle de faturamento, emissão de cobranças e recebíveis de eventos.</p>
            </div>
            <flux:button variant="primary" class="bg-neutral-800 hover:bg-neutral-900 dark:bg-white dark:text-stone-950 dark:hover:bg-zinc-100">
                Nova Cobrança
            </flux:button>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <x-kpi-card title="Total Recebido" value="R$ 380k" subtitle="Este mês" :threshold="['good' => 300000, 'warning' => 200000]" thresholdType="min" />
            <x-kpi-card title="A Receber" value="R$ 145k" subtitle="Próximos 30 dias" />
            <x-kpi-card title="Pendente" value="R$ 12k" subtitle="Vencendo esta semana" :threshold="['good' => 5000, 'warning' => 15000]" thresholdType="max" />
            <x-kpi-card title="Vencido" value="R$ 3.5k" subtitle="Ações de cobrança" :threshold="['good' => 1000, 'warning' => 5000]" thresholdType="max" />
        </div>

        <!-- Tabela Financeira -->
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden shadow-xs">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                <span class="font-semibold text-zinc-800 dark:text-zinc-200">Faturamentos Recentes</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400 font-medium">
                            <th class="p-4">Fatura</th>
                            <th class="p-4">Cliente / Evento</th>
                            <th class="p-4">Valor</th>
                            <th class="p-4">Vencimento</th>
                            <th class="p-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 text-zinc-900 dark:text-zinc-100">
                            <td class="p-4 font-mono text-xs">#FAT-2026-104</td>
                            <td class="p-4 font-medium">Formatura Faculdade de Direito (Turma B)</td>
                            <td class="p-4">R$ 45.000,00</td>
                            <td class="p-4">15/06/2026</td>
                            <td class="p-4">
                                <x-status-badge type="payment" status="a_vencer" />
                            </td>
                        </tr>
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 text-zinc-900 dark:text-zinc-100">
                            <td class="p-4 font-mono text-xs">#FAT-2026-103</td>
                            <td class="p-4 font-medium">Corporativo Shell Brasil</td>
                            <td class="p-4">R$ 112.000,00</td>
                            <td class="p-4">30/05/2026</td>
                            <td class="p-4">
                                <x-status-badge type="payment" status="pago" />
                            </td>
                        </tr>
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 text-zinc-900 dark:text-zinc-100">
                            <td class="p-4 font-mono text-xs">#FAT-2026-102</td>
                            <td class="p-4 font-medium">Casamento Juliana & Rodrigo</td>
                            <td class="p-4">R$ 18.000,00</td>
                            <td class="p-4">25/05/2026</td>
                            <td class="p-4">
                                <x-status-badge type="payment" status="pendente" />
                            </td>
                        </tr>
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 text-zinc-900 dark:text-zinc-100">
                            <td class="p-4 font-mono text-xs">#FAT-2026-101</td>
                            <td class="p-4 font-medium">Show Aniversário de Município</td>
                            <td class="p-4">R$ 80.000,00</td>
                            <td class="p-4">10/05/2026</td>
                            <td class="p-4">
                                <x-status-badge type="payment" status="vencido" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
