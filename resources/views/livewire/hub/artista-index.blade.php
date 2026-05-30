<div>
    <flux:separator variant="subtle" class="my-6" />
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading level="2">Hub Artista</flux:heading>
            <flux:subheading>Dashboard de performance e controles do artista</flux:subheading>
        </div>
    </div>

    <!-- HUB-001: Filtros -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <flux:field.label>Artista</flux:field.label>
                <flux:select wire:model.live="artistaId" placeholder="Selecione um artista...">
                    <option value="">Todos os Artistas</option>
                    @foreach($this->artistas as $artista)
                        <option value="{{ $artista->id }}">{{ $artista->razao_social }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <flux:field.label>Ano</flux:field.label>
                <flux:select wire:model.live="filterAno">
                    @foreach($this->anosDisponiveis as $ano)
                        <option value="{{ $ano }}">{{ $ano }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <flux:field.label>Mês</flux:field.label>
                <flux:select wire:model.live="filterMes">
                    <option value="">Todos</option>
                    <option value="1">Janeiro</option>
                    <option value="2">Fevereiro</option>
                    <option value="3">Março</option>
                    <option value="4">Abril</option>
                    <option value="5">Maio</option>
                    <option value="6">Junho</option>
                    <option value="7">Julho</option>
                    <option value="8">Agosto</option>
                    <option value="9">Setembro</option>
                    <option value="10">Outubro</option>
                    <option value="11">Novembro</option>
                    <option value="12">Dezembro</option>
                </flux:select>
            </div>
        </div>
        @if($artistaId || $filterAno || $filterMes)
            <flux:button wire:click="resetFilters" variant="ghost" size="sm" class="mt-3">Limpar Filtros</flux:button>
        @endif
    </div>

    <!-- HUB-001: KPIs -->
    @if($this->artistaSelecionado || $artistaId === '')
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Total Receber</div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">R$ {{ number_format($this->resumoFinanceiro['total_previsto'], 2, ',', '.') }}</div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Recebido</div>
                <div class="text-2xl font-bold text-green-600">R$ {{ number_format($this->resumoFinanceiro['total_recebido'], 2, ',', '.') }}</div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Em Aberto</div>
                <div class="text-2xl font-bold text-yellow-600">R$ {{ number_format($this->resumoFinanceiro['total_aberto'], 2, ',', '.') }}</div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Parcelas</div>
                <div class="text-2xl font-bold">{{ $this->resumoFinanceiro['quantidade_parcelas'] }}</div>
                <div class="flex gap-2 mt-1">
                    <flux:badge color="success" size="xs">{{ $this->resumoFinanceiro['quitados'] }} quit</flux:badge>
                    <flux:badge color="warning" size="xs">{{ $this->resumoFinanceiro['abertos'] }} abert</flux:badge>
                    <flux:badge color="danger" size="xs">{{ $this->resumoFinanceiro['vencidos'] }} vec</flux:badge>
                </div>
            </div>
        </div>

        <!-- Contratos e Lançamentos lado a lado -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Contratos -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                    <flux:heading level="3">Contratos</flux:heading>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($this->contratosDoArtista->take(5) as $contrato)
                        <div class="p-4 hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium">{{ $contrato->nome_evento ?? 'Evento sem nome' }}</div>
                                    <div class="text-sm text-zinc-500">{{ $contrato->codigo_contrato ?? 'Sem código' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold">R$ {{ number_format($contrato->valor_bruto, 2, ',', '.') }}</div>
                                    <flux:badge color="{{ $contrato->status_booking === 'Confirmado' ? 'success' : 'neutral' }}" size="sm">{{ $contrato->status_booking }}</flux:badge>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-zinc-500">Nenhum contrato encontrado</div>
                    @endforelse
                </div>
            </div>

            <!-- Últimos Lançamentos -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                    <flux:heading level="3">Últimos Lançamentos</flux:heading>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($this->lancamentosDoArtista->take(5) as $lanc)
                        <div class="p-4 hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium">{{ $lanc->nome_evento }}</div>
                                    <div class="text-sm text-zinc-500">Venc: {{ $lanc->vencimento_atual?->format('d/m/Y') }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold">R$ {{ number_format($lanc->valor_previsto, 2, ',', '.') }}</div>
                                    <flux:badge color="{{ $lanc->isQuitado ? 'success' : ($lanc->isVencido ? 'danger' : 'warning') }}" size="sm">
                                        {{ $lanc->isQuitado ? 'Quitado' : ($lanc->isVencido ? 'Vencido' : 'Aberto') }}
                                    </flux:badge>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-zinc-500">Nenhum lançamento encontrado</div>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-8 text-center">
            <flux:icon variant="large" icon="user-circle" class="mx-auto text-zinc-400 mb-4" />
            <flux:heading level="3">Selecione um Artista</flux:heading>
            <p class="text-zinc-500 mt-2">Escolha um artista acima para ver seu dashboard de performance.</p>
        </div>
    @endif
</div>
