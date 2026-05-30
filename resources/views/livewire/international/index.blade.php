<div>
    <flux:separator variant="subtle" class="my-6" />
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading level="2">Módulo Internacional</flux:heading>
            <flux:subheading>Controle de operações de importação e exportação</flux:subheading>
        </div>
    </div>

    <!-- INT-006: Tabs Importação/Exportação -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <!-- Tab Headers -->
        <div class="border-b border-zinc-200 dark:border-zinc-700">
            <nav class="flex -mb-px">
                <button
                    wire:click="setTab('importacao')"
                    class="px-6 py-3 text-sm font-medium border-b-2 {{ $activeTab === 'importacao' ? 'border-violet-500 text-violet-600' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}"
                >
                    <flux:icon variant="micro" icon="arrow-down-tray" class="inline mr-2" />
                    Importação
                    @if($activeTab === 'importacao')
                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-violet-100 text-violet-600 dark:bg-violet-900 dark:text-violet-300">
                            {{ $this->registrosImportacao->total() }}
                        </span>
                    @endif
                </button>
                <button
                    wire:click="setTab('exportacao')"
                    class="px-6 py-3 text-sm font-medium border-b-2 {{ $activeTab === 'exportacao' ? 'border-violet-500 text-violet-600' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}"
                >
                    <flux:icon variant="micro" icon="arrow-up-tray" class="inline mr-2" />
                    Exportação
                    @if($activeTab === 'exportacao')
                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-violet-100 text-violet-600 dark:bg-violet-900 dark:text-violet-300">
                            {{ $this->registrosExportacao->total() }}
                        </span>
                    @endif
                </button>
            </nav>
        </div>

        <!-- Filters -->
        <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <flux:input wire:model.live.debounce.250ms="search" placeholder="Buscar evento..." icon="magnifying-glass" />
                </div>
                <flux:select wire:model="filterStatusCambio" placeholder="Status Câmbio">
                    <option value="">Todos</option>
                    <option value="aguardando">Aguardando Câmbio</option>
                    <option value="convertido">Convertido</option>
                </flux:select>
                @if($search || $filterStatusCambio)
                    <flux:button wire:click="resetFilters" variant="ghost" size="sm">Limpar</flux:button>
                @endif
            </div>
        </div>

        <!-- Tab Content -->
        <div class="p-4">
            @if($activeTab === 'importacao')
                <!-- INT-001/INT-006: Importação - valores em moeda estrangeira -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Evento</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Artista</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Valor (R$)</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase">USD</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase">EUR</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase">GBP</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase">Moeda</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase">Taxa</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($this->registrosImportacao as $reg)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                                    <td class="px-4 py-3">
                                        <div class="font-medium">{{ $reg->nome_evento }}</div>
                                        <div class="text-xs text-zinc-500">{{ $reg->parcela_numero }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ $reg->artista->razao_social ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right font-medium">{{ number_format($reg->valor_previsto, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right {{ $reg->valor_usd > 0 ? 'text-blue-600 font-medium' : 'text-zinc-400' }}">
                                        {{ $reg->valor_usd > 0 ? number_format($reg->valor_usd, 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-right {{ $reg->valor_eur > 0 ? 'text-green-600 font-medium' : 'text-zinc-400' }}">
                                        {{ $reg->valor_eur > 0 ? number_format($reg->valor_eur, 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-right {{ $reg->valor_gbp > 0 ? 'text-purple-600 font-medium' : 'text-zinc-400' }}">
                                        {{ $reg->valor_gbp > 0 ? number_format($reg->valor_gbp, 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <flux:badge size="sm" color="{{ $reg->moeda_original === 'USD' ? 'primary' : ($reg->moeda_original === 'EUR' ? 'success' : 'warning') }}">
                                            {{ $reg->moeda_original }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm">
                                        @if($reg->taxa_cambio)
                                            {{ number_format($reg->taxa_cambio, 4, ',', '.') }}
                                            <span class="text-xs text-zinc-500">({{ $reg->tipo_cambio }})</span>
                                        @else
                                            <span class="text-zinc-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($reg->isAguardandoCambio)
                                            <flux:badge color="warning" size="sm">Aguard. Câmbio</flux:badge>
                                        @elseif($reg->isQuitado)
                                            <flux:badge color="success" size="sm">Convertido</flux:badge>
                                        @else
                                            <flux:badge color="neutral" size="sm">{{ ucfirst($reg->status_pagamento) }}</flux:badge>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-8 text-zinc-500">
                                        Nenhum registro de importação encontrado
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $this->registrosImportacao->links() }}
                </div>

            @else
                <!-- Exportação -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Evento</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Destinatário</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Valor (R$)</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase">Reg. Contábil</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Vencimento</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($this->registrosExportacao as $reg)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                                    <td class="px-4 py-3">
                                        <div class="font-medium">{{ $reg->nome_evento }}</div>
                                        <div class="text-xs text-zinc-500">{{ $reg->parcela_numero }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($reg->registro_contabil === 'Artista')
                                            {{ $reg->artista->razao_social ?? '-' }}
                                        @else
                                            {{ $reg->contratante->razao_social ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium">{{ number_format($reg->valor_previsto, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <flux:badge size="sm" color="{{ $reg->registro_contabil === 'Artista' ? 'success' : ($reg->registro_contabil === 'Coral' ? 'warning' : 'primary') }}">
                                            {{ $reg->registro_contabil }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ $reg->vencimento_atual?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <flux:badge color="{{ $reg->isQuitado ? 'success' : ($reg->isVencido ? 'danger' : 'warning') }}" size="sm">
                                            {{ ucfirst($reg->status_pagamento) }}
                                        </flux:badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-zinc-500">
                                        Nenhum registro de exportação encontrado
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $this->registrosExportacao->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
