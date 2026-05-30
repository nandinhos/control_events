<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-2">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white tracking-tight">Gestão de Contratos</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Gerencie todos os contratos ativos e pendentes de eventos e artistas.</p>
            </div>
            <flux:button href="#" variant="primary" class="bg-neutral-800 hover:bg-neutral-900 dark:bg-white dark:text-stone-950 dark:hover:bg-zinc-100">
                Novo Contrato
            </flux:button>
        </div>

        <!-- KPIs de Contratos -->
        <div class="grid gap-4 md:grid-cols-3">
            <x-kpi-card 
                title="Contratos Assinados" 
                value="24" 
                subtitle="+3 este mês"
                :threshold="['good' => 20, 'warning' => 10]"
                thresholdType="min"
            />
            <x-kpi-card 
                title="Aguardando Assinatura" 
                value="5" 
                subtitle="Ações requeridas"
                :threshold="['good' => 2, 'warning' => 5]"
                thresholdType="max"
            />
            <x-kpi-card 
                title="Contratos Expirados" 
                value="1" 
                subtitle="Revisão pendente"
                :threshold="['good' => 0, 'warning' => 2]"
                thresholdType="max"
            />
        </div>

        <!-- Lista de Contratos -->
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden shadow-xs">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-900/50">
                <span class="font-semibold text-zinc-800 dark:text-zinc-200">Últimos Contratos Modificados</span>
                <flux:button size="sm" variant="subtle">Ver Todos</flux:button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400 font-medium">
                            <th class="p-4">Identificador</th>
                            <th class="p-4">Evento / Artista</th>
                            <th class="p-4">Valor</th>
                            <th class="p-4">Data Emissão</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 text-zinc-900 dark:text-zinc-100">
                            <td class="p-4 font-mono text-xs">#CTR-2026-001</td>
                            <td class="p-4 font-medium">Festival de Inverno & DJ Alok</td>
                            <td class="p-4">R$ 150.000,00</td>
                            <td class="p-4">28/05/2026</td>
                            <td class="p-4">
                                <x-status-badge type="contract" status="assinado" />
                            </td>
                            <td class="p-4 text-right">
                                <flux:button size="sm" icon="eye" variant="ghost" />
                            </td>
                        </tr>
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 text-zinc-900 dark:text-zinc-100">
                            <td class="p-4 font-mono text-xs">#CTR-2026-002</td>
                            <td class="p-4 font-medium">Arena Sertaneja & Gusttavo Lima</td>
                            <td class="p-4">R$ 420.000,00</td>
                            <td class="p-4">29/05/2026</td>
                            <td class="p-4">
                                <x-status-badge type="contract" status="para_assinatura" />
                            </td>
                            <td class="p-4 text-right">
                                <flux:button size="sm" icon="eye" variant="ghost" />
                            </td>
                        </tr>
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 text-zinc-900 dark:text-zinc-100">
                            <td class="p-4 font-mono text-xs">#CTR-2026-003</td>
                            <td class="p-4 font-medium">Show Corporativo & Anitta</td>
                            <td class="p-4">R$ 300.000,00</td>
                            <td class="p-4">15/04/2026</td>
                            <td class="p-4">
                                <x-status-badge type="contract" status="expirado" />
                            </td>
                            <td class="p-4 text-right">
                                <flux:button size="sm" icon="eye" variant="ghost" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
