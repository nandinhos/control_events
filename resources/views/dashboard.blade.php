<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-2">

        <!-- Header com filtros -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white tracking-tight">Painel Financeiro</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Visao geral do faturamento, despesas de turnê e conformidade de contratos.</p>
            </div>
            <div class="flex items-center gap-3">
                <select wire:model.live="ano" class="text-sm border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-1.5 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                    @foreach($anosDisponiveis as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endforeach
                </select>
                <select wire:model.live="mes" class="text-sm border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-1.5 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                    <option value="">Ano completo</option>
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}">{{ ucfirst(strftime('%B', mktime(0,0,0,$m,1))) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- KPIs -->
        <div class="grid gap-4 md:grid-cols-4">
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Receita do Mes</div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-white">R$ {{ number_format($kpis['receitas']['total_previsto'] / 1000, 1) }}k</div>
                <div class="text-xs text-zinc-500 mt-1">{{ $kpis['receitas']['taxa_recebimento'] }}% recebido</div>
                <div class="w-full bg-zinc-100 dark:bg-zinc-700 rounded-full h-1 mt-2">
                    <div class="bg-green-500 h-1 rounded-full" style="width: {{ $kpis['receitas']['taxa_recebimento'] }}%"></div>
                </div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Recebido</div>
                <div class="text-2xl font-bold text-green-600">R$ {{ number_format($kpis['receitas']['total_recebido'] / 1000, 1) }}k</div>
                <div class="text-xs text-zinc-500 mt-1">total recebido no periodo</div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Despesas</div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-white">R$ {{ number_format($kpis['despesas']['total_devido'] / 1000, 1) }}k</div>
                <div class="text-xs text-zinc-500 mt-1">{{ $kpis['despesas']['taxa_pagamento'] }}% pago</div>
                <div class="w-full bg-zinc-100 dark:bg-zinc-700 rounded-full h-1 mt-2">
                    <div class="bg-yellow-500 h-1 rounded-full" style="width: {{ $kpis['despesas']['taxa_pagamento'] }}%"></div>
                </div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Resultado</div>
                <div class="text-2xl font-bold {{ $kpis['resultado'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    R$ {{ number_format($kpis['resultado'] / 1000, 1) }}k
                </div>
                <div class="text-xs text-zinc-500 mt-1">margem {{ $kpis['margem'] }}%</div>
            </div>
        </div>

        <!-- Alertas -->
        @if($kpis['vencidos_receber'] > 0 || $kpis['vencidos_pagar'] > 0)
            <div class="flex gap-4 flex-wrap">
                @if($kpis['vencidos_receber'] > 0)
                    <div class="flex items-center gap-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg px-4 py-2">
                        <flux:icon icon="exclamation" variant="micro" class="text-red-600" />
                        <span class="text-sm text-red-700 dark:text-red-400">{{ $kpis['vencidos_receber'] }} contas a receber vencidas</span>
                    </div>
                @endif
                @if($kpis['vencidos_pagar'] > 0)
                    <div class="flex items-center gap-2 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg px-4 py-2">
                        <flux:icon icon="exclamation" variant="micro" class="text-yellow-600" />
                        <span class="text-sm text-yellow-700 dark:text-yellow-400">{{ $kpis['vencidos_pagar'] }} pagamentos pendentes vencidos</span>
                    </div>
                @endif
            </div>
        @endif

        <!-- Conteudo Principal -->
        <div class="grid gap-6 lg:grid-cols-2">

            <!-- Contratos Recentes -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                        <span class="font-semibold text-zinc-800 dark:text-zinc-200 text-sm">Eventos Recentes</span>
                    </div>
                    <div class="p-4 flex flex-col gap-3">
                        @forelse($eventosMes as $evento)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-600 dark:text-zinc-400">{{ $evento['nome_evento'] ?? ($evento['codigo_contrato'] ?? 'Sem nome') }}</span>
                                <x-status-badge type="contract" status="{{ $evento['status_booking'] ?? 'draft' }}" />
                            </div>
                        @empty
                            <p class="text-sm text-zinc-400 text-center py-4">Nenhum evento neste periodo</p>
                        @endforelse
                    </div>
                </div>
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 text-right">
                    <flux:button href="{{ route('contratos.index') }}" size="sm" variant="subtle" wire:navigate>Gerenciar Contratos</flux:button>
                </div>
            </div>

            <!-- DRE Evolucao -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 flex items-center justify-between">
                        <span class="font-semibold text-zinc-800 dark:text-zinc-200 text-sm">Evolucao DRE (6 meses)</span>
                    </div>
                    <div class="p-4">
                        @php $receitas = array_column($evolucaoDRE, 'receita'); $despesas = array_column($evolucaoDRE, 'despesa'); $maxVal = max(array_merge($receitas, $despesas)); $maxVal = $maxVal > 0 ? $maxVal : 1; @endphp
                        <div class="flex items-end gap-2 h-40">
                            @foreach($evolucaoDRE as $mes)
                                <div class="flex-1 flex flex-col items-center gap-1">
                                    <div class="w-full flex flex-col-reverse gap-0.5">
                                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-t-sm" style="height: {{ max(($mes['despesa'] / $maxVal) * 120, 2) }}px;" title="Despesa: R$ {{ number_format($mes['despesa'], 0) }}">
                                            <div class="w-full bg-green-500 rounded-t-sm" style="height: {{ max(($mes['receita'] / $maxVal) * 120, 2) }}px;" title="Receita: R$ {{ number_format($mes['receita'], 0) }}"></div>
                                        </div>
                                    </div>
                                    <span class="text-xs text-zinc-500">{{ $mes['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex items-center gap-4 mt-3 justify-center">
                            <span class="flex items-center gap-1 text-xs"><span class="w-3 h-2 bg-green-500 rounded-sm inline-block"></span>Receita</span>
                            <span class="flex items-center gap-1 text-xs"><span class="w-3 h-2 bg-zinc-300 dark:bg-zinc-600 rounded-sm inline-block"></span>Despesa</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Taxas Cambio -->
        @if($taxasCambio['USD_BRL'] || $taxasCambio['EUR_BRL'])
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 flex items-center gap-6">
                <span class="text-xs text-zinc-500 uppercase tracking-wide">Taxas de Cambio (BCB)</span>
                @if($taxasCambio['USD_BRL'])
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-zinc-600">USD/BRL:</span>
                        <span class="font-mono text-sm font-medium">R$ {{ number_format($taxasCambio['USD_BRL'], 3) }}</span>
                    </div>
                @endif
                @if($taxasCambio['EUR_BRL'])
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-zinc-600">EUR/BRL:</span>
                        <span class="font-mono text-sm font-medium">R$ {{ number_format($taxasCambio['EUR_BRL'], 3) }}</span>
                    </div>
                @endif
                @if($taxasCambio['USD_EUR'])
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-zinc-600">USD/EUR:</span>
                        <span class="font-mono text-sm font-medium">{{ number_format($taxasCambio['USD_EUR'], 4) }}</span>
                    </div>
                @endif
            </div>
        @endif

    </div>
</x-layouts.app>
