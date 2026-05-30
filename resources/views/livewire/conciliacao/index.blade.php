<div>
    <flux:separator variant="subtle" class="my-6" />
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading level="2">Conciliacao Bancaria</flux:heading>
            <flux:subheading>Importacao de extratos e matching automatico</flux:subheading>
        </div>
        <flux:button wire:click="openImportModal" variant="primary">
            <flux:icon variant="micro" icon="arrow-down-left" class="mr-1.5" />Importar Extrato
        </flux:button>
    </div>

    <!-- KPI Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        @php
            $total = $this->transacoesBancarias->total();
            $pendentes = $this->transacoesBancarias->where('conciliado_status', null)->count();
            $conciliados = $this->transacoesBancarias->where('conciliado_status', 'R')->count();
        @endphp
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Total Transacoes</div>
            <div class="text-2xl font-bold">{{ number_format($total, 0) }}</div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Pendentes</div>
            <div class="text-2xl font-bold text-yellow-600">{{ $pendentes }}</div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Conciliados</div>
            <div class="text-2xl font-bold text-green-600">{{ $conciliados }}</div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Contas</div>
            <div class="text-2xl font-bold">{{ count($this->contasBancariasDisponiveis) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <flux:input wire:model.live.debounce.250ms="searchTransacao" placeholder="Buscar transacao..." icon="magnifying-glass" />
            </div>
            <flux:select wire:model="filterContaBancaria" placeholder="Conta Bancaria">
                <option value="">Todas</option>
                @foreach($this->contasBancariasDisponiveis as $conta)
                    <option value="{{ $conta }}">{{ $conta }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model="filterStatus" placeholder="Status">
                <option value="">Todos</option>
                <option value="pendente">Pendente</option>
                <option value="conciliado">Conciliado</option>
                <option value="excluido">Excluido</option>
            </flux:select>
            <flux:button wire:click="resetFilters" variant="ghost" size="sm">Limpar</flux:button>
        </div>
        @if($filterContaBancaria)
            <div class="flex gap-2 mt-3">
                <flux:button wire:click="runAutoMatch('{{ $filterContaBancaria }}')" variant="outline" size="sm">
                    <flux:icon variant="micro" icon="arrows-right-left" class="mr-1" />Auto-Match
                </flux:button>
            </div>
        @endif
    </div>

    <!-- Transactions Table -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-zinc-50 dark:bg-zinc-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Data</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Conta</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Descricao</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Valor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($this->transacoesBancarias as $t)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                            <td class="px-4 py-3 text-sm">{{ $t->data_transacao?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $t->conta_bancaria }}</td>
                            <td class="px-4 py-3 text-sm max-w-xs truncate">{{ $t->descricao }}</td>
                            <td class="px-4 py-3 font-medium {{ $t->valor >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($t->valor, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge color="{{ $t->tipo === 'credito' ? 'success' : ($t->tipo === 'debito' ? 'danger' : 'neutral') }}" size="sm">{{ $t->tipo }}</flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                @if($t->conciliado_status === 'R')
                                    <flux:badge color="success" size="sm">Conciliado</flux:badge>
                                    @if($t->conciliacaoLinks->isNotEmpty())
                                        <span class="text-xs text-zinc-500">{{ $t->conciliacaoLinks->first()->lancamento_type === 'App\\Models\\ContasAReceber' ? 'Receber' : 'Pagar' }}</span>
                                    @endif
                                @elseif($t->conciliado_status === 'E')
                                    <flux:badge color="neutral" size="sm">Excluido</flux:badge>
                                @else
                                    <flux:badge color="warning" size="sm">Pendente</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if(!$t->conciliado_status)
                                    <flux:button wire:click="openNpara1({{ $t->id }})" variant="ghost" size="sm" title="N-para-1">
                                        <flux:icon variant="micro" icon="adjustments-horizontal" />
                                    </flux:button>
                                    <flux:button wire:click="openManualLink({{ $t->id }})" variant="ghost" size="sm" title="Link manual">
                                        <flux:icon variant="micro" icon="arrows-right-left" />
                                    </flux:button>
                                    <flux:button wire:click="excludeTransacao({{ $t->id }})" variant="ghost" size="sm" class="text-yellow-600" title="Excluir">
                                        <flux:icon variant="micro" icon="x-mark" />
                                    </flux:button>
                                @elseif($t->conciliado_status === 'R' && $t->conciliacaoLinks->isNotEmpty())
                                    <flux:button wire:click="unlink({{ $t->conciliacaoLinks->first()->id }})" variant="ghost" size="sm" title="Desfazer link">
                                        <flux:icon variant="micro" icon="x-mark" class="text-red-500" />
                                    </flux:button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-12 text-zinc-500">
                            Nenhuma transacao. <button wire:click="openImportModal" class="text-violet-600 hover:underline">Importar extrato</button>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">{{ $this->transacoesBancarias->links() }}</div>
    </div>

    <!-- N-para-1 Panel -->
    @if($transacaoParaNpara1)
        <div class="fixed inset-y-0 right-0 z-50 w-96 bg-white dark:bg-zinc-800 border-l border-zinc-200 dark:border-zinc-700 shadow-xl flex flex-col">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-lg">N-para-1</h3>
                    <p class="text-sm text-zinc-500">Selecione lançamentos para conciliar</p>
                </div>
                <flux:button wire:click="$set('transacaoParaNpara1', null); $set('selectedLancamentos', [])" variant="ghost" size="sm">
                    <flux:icon variant="micro" icon="x-mark" />
                </flux:button>
            </div>

            <!-- Transação Selecionada -->
            <div class="p-4 bg-violet-50 dark:bg-violet-900/20 border-b border-zinc-200 dark:border-zinc-700">
                <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Transação Bancária</div>
                <div class="font-medium">{{ $transacaoParaNpara1->descricao }}</div>
                <div class="text-2xl font-bold text-violet-600">
                    R$ {{ number_format($transacaoParaNpara1->valor, 2, ',', '.') }}
                </div>
            </div>

            <!-- Soma e Diferença -->
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-zinc-600">Soma selecionada:</span>
                    <span class="font-medium">R$ {{ number_format($this->somaSelecionados, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-zinc-600">Diferença:</span>
                    <span class="font-medium {{ $this->diferencaNpara1 < 0.01 ? 'text-green-600' : 'text-red-600' }}">
                        R$ {{ number_format($this->diferencaNpara1, 2, ',', '.') }}
                    </span>
                </div>
                @if($this->diferencaNpara1 < 0.01)
                    <flux:badge color="success" class="w-full justify-center">Soma bate!</flux:badge>
                @else
                    <flux:badge color="danger" class="w-full justify-center">Diferença detectada</flux:badge>
                @endif
            </div>

            <!-- Ações de Seleção -->
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 flex gap-2">
                <flux:button wire:click="selectAllVisible" variant="outline" size="sm" class="flex-1">
                    Selecionar Todos
                </flux:button>
                <flux:button wire:click="clearSelection" variant="ghost" size="sm">
                    Limpar
                </flux:button>
            </div>

            <!-- Lista de Lançamentos -->
            <div class="flex-1 overflow-y-auto p-4 space-y-2">
                @forelse($this->lancamentosNaoConciliados as $lancamento)
                    <div class="flex items-start gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                        <input
                            type="checkbox"
                            wire:change="toggleLancamento({{ json_encode([
                                'id' => $lancamento->id,
                                'tipo' => $lancamento->tipo,
                                'label' => $lancamento->label,
                                'valor' => $lancamento->valor,
                            ]) }})"
                            :checked="$this->isSelected({{ $lancamento->id }})"
                            class="mt-1 rounded border-zinc-300 text-violet-600 focus:ring-violet-500"
                        />
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium truncate">{{ $lancamento->label }}</div>
                            <div class="flex items-center gap-2 mt-1">
                                <flux:badge size="sm" color="{{ $lancamento->tipo === 'ContasAReceber' ? 'success' : 'warning' }}">
                                    {{ $lancamento->tipo === 'ContasAReceber' ? 'Receber' : 'Pagar' }}
                                </flux:badge>
                                <span class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                    R$ {{ number_format($lancamento->valor, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        <!-- REC-004: Quick Edit -->
                        <flux:button wire:click="enableEdit({{ json_encode([
                            'id' => $lancamento->id,
                            'tipo' => $lancamento->tipo,
                            'valor' => $lancamento->valor,
                        ]) }})" variant="ghost" size="xs" title="Editar valor">
                            <flux:icon variant="micro" icon="pencil" />
                        </flux:button>
                    </div>
                @empty
                    <div class="text-center py-8 text-zinc-500">
                        <flux:icon variant="large" icon="inbox" class="mx-auto mb-2 opacity-50" />
                        <p>Nenhum lançamento disponível</p>
                    </div>
                @endforelse
            </div>

            <!-- Footer com Ações -->
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700 space-y-2">
                <!-- REC-006: Novo Lançamento -->
                <flux:button wire:click="openNewLancamentoModal" variant="outline" size="sm" class="w-full">
                    <flux:icon variant="micro" icon="plus" class="mr-1.5" />Novo Lançamento
                </flux:button>

                <!-- REC-005: Botão Confirmar (só habilitado se soma bater) -->
                <flux:button
                    wire:click="conciliarNpara1"
                    variant="primary"
                    class="w-full"
                    :disabled="!$this->canConciliarNpara1()"
                >
                    <flux:icon variant="micro" icon="check" class="mr-1.5" />
                    Conciliar N-para-1 ({{ count($selectedLancamentos) }})
                </flux:button>
            </div>
        </div>
    @endif

    <!-- Import Modal -->
    <flux:modal name="import-modal" class="max-w-md">
        <flux:modal.header>Importar Extrato</flux:modal.header>
        <flux:modal.body>
            <div class="space-y-4">
                <flux:select wire:model="importTipo" label="Tipo de Arquivo">
                    <option value="ofx">OFX</option>
                    <option value="csv">CSV</option>
                </flux:select>
                <div>
                    <flux:field.label>Conta Bancaria *</flux:field.label>
                    <flux:input wire:model="contaBancariaImport" placeholder="Ex: BTG Pactual, Nubank..." />
                </div>
                <div>
                    <flux:field.label>Arquivo *</flux:field.label>
                    <input type="file" wire:model="fileImport" accept=".ofx,.csv" class="w-full text-sm" />
                    @error('fileImport') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </flux:modal.body>
        <flux:modal.footer>
            <flux:button wire:click="closeImportModal" variant="ghost">Cancelar</flux:button>
            <flux:button wire:click="importFile" variant="primary">Importar</flux:button>
        </flux:modal.footer>
    </flux:modal>

    <!-- Manual Link Modal -->
    <flux:modal name="link-modal" class="max-w-md">
        <flux:modal.header>Link Manual</flux:modal.header>
        <flux:modal.body>
            @if($transacaoParaLinkar)
                <div class="bg-zinc-100 dark:bg-zinc-700 rounded p-3 mb-4">
                    <div class="text-xs text-zinc-500">Transacao</div>
                    <div class="font-medium">{{ $transacaoParaLinkar->descricao }}</div>
                    <div class="text-sm text-zinc-600">{{ $transacaoParaLinkar->data_transacao?->format('d/m/Y') }} | {{ number_format($transacaoParaLinkar->valor, 2, ',', '.') }}</div>
                </div>
                <div class="space-y-4">
                    <flux:select wire:model="linkLancamentoType" label="Tipo Lancamento">
                        <option value="">Selecione...</option>
                        <option value="ContasAReceber">Contas a Receber</option>
                        <option value="ContasAPagar">Contas a Pagar</option>
                    </flux:select>
                    @if($linkLancamentoType)
                        <div>
                            <flux:field.label>Lancamento *</flux:field.label>
                            <flux:select wire:model="linkLancamentoId">
                                <option value="">Selecione...</option>
                                @foreach($this->lancamentosNaoConciliados->where('tipo', $linkLancamentoType) as $l)
                                    <option value="{{ $l->id }}">{{ $l->label }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                    @endif
                    <div>
                        <flux:field.label>Valor Conciliado</flux:field.label>
                        <flux:input wire:model="linkValor" type="number" step="0.01" />
                    </div>
                </div>
            @endif
        </flux:modal.body>
        <flux:modal.footer>
            <flux:button wire:click="closeManualLink" variant="ghost">Cancelar</flux:button>
            <flux:button wire:click="saveManualLink" variant="primary">Linkar</flux:button>
        </flux:modal.footer>
    </flux:modal>

    <!-- REC-006: Novo Lançamento Modal -->
    <flux:modal name="new-lancamento-modal" class="max-w-md">
        <flux:modal.header>Criar Novo Lançamento</flux:modal.header>
        <flux:modal.body>
            <div class="space-y-4">
                <flux:select wire:model="newLancamentoTipo" label="Tipo">
                    <option value="ContasAReceber">Contas a Receber</option>
                    <option value="ContasAPagar">Contas a Pagar</option>
                </flux:select>

                @if($newLancamentoTipo === 'ContasAReceber')
                    <flux:input wire:model="newReceivableEvento" label="Nome do Evento" placeholder="Ex: Show São Paulo" />
                    <flux:input wire:model="newReceivableValor" label="Valor" type="number" step="0.01" placeholder="0.00" />
                    <flux:input wire:model="newReceivableVencimento" label="Vencimento" type="date" />
                    <flux:input wire:model="newReceivableContrato" label="Contrato (opcional)" placeholder="ID do contrato" />
                @else
                    <flux:input wire:model="newPayableDescricao" label="Descrição" placeholder="Ex: Aluguel HQ" />
                    <flux:input wire:model="newPayableValor" label="Valor" type="number" step="0.01" placeholder="0.00" />
                    <flux:input wire:model="newPayableDataDevida" label="Data de Vencimento" type="date" />
                    <flux:input wire:model="newPayableFornecedor" label="Fornecedor (opcional)" placeholder="Nome do fornecedor" />
                @endif
            </div>
        </flux:modal.body>
        <flux:modal.footer>
            <flux:button wire:click="closeNewLancamentoModal" variant="ghost">Cancelar</flux:button>
            <flux:button wire:click="createAndLinkLancamento" variant="primary">
                Criar e Vincular
            </flux:button>
        </flux:modal.footer>
    </flux:modal>
</div>