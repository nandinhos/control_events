<div>
    <flux:separator variant="subtle" class="my-6" />
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading level="2">Contas a Pagar</flux:heading>
            <flux:subheading>Gerencie obrigacoes e pagamentos</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" variant="primary">
            <flux:icon variant="micro" icon="plus" class="mr-1.5" />Nova Conta
        </flux:button>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <div class="md:col-span-2">
                <flux:input wire:model.live.debounce.250ms="search" placeholder="Buscar descricao..." icon="magnifying-glass" />
            </div>
            <flux:select wire:model="filterStatus" placeholder="Status">
                <option value="">Todos</option>
                <option value="pendente">Pendente</option>
                <option value="processando">Processando</option>
                <option value="pago">Pago</option>
            </flux:select>
            <flux:select wire:model="filterAno" placeholder="Ano">
                <option value="0">Todos</option>
                @foreach($this->anosDisponiveis as $y)
                    <option value="{{ $y['id'] }}">{{ $y['label'] }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model="filterMes" placeholder="Mes">
                <option value="0">Todos</option>
                @foreach($this->mesesDisponiveis as $m)
                    <option value="{{ $m['id'] }}">{{ $m['label'] }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model="filterContraparte" placeholder="Contraparte">
                <option value="">Todos</option>
                @foreach($this->contrapartes as $c)
                    <option value="{{ $c->id }}">{{ $c->razao_social }}</option>
                @endforeach
            </flux:select>
        </div>
        <div class="flex gap-2 mt-3">
            <flux:button wire:click="resetFilters" variant="ghost" size="sm">Limpar</flux:button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-zinc-50 dark:bg-zinc-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Descricao</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Contraparte</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Vencimento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Valor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Pago</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Conciliado</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($this->contas as $c)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $c->descricao ?: $c->conta_origem }}</div>
                                <div class="text-xs text-zinc-500">{{ $c->plano_contas_id }} | {{ $c->tipo_doc_fiscal }} {{ $c->num_doc_fiscal ? '#'.$c->num_doc_fiscal : '' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $c->contraparte->razao_social ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $c->data_devida?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ number_format($c->valor_devido, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 font-medium text-green-600">{{ number_format($c->valor_pago ?? 0, 2, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <flux:badge color="{{ $c->status_pagamento === 'pago' ? 'success' : ($c->status_pagamento === 'processando' ? 'warning' : 'neutral') }}" size="sm">{{ ucfirst($c->status_pagamento) }}</flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge color="{{ $c->conciliado_status === 'R' ? 'success' : 'neutral' }}" size="sm">{{ $c->conciliado_status ?: 'N' }}</flux:badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <flux:button wire:click="openEdit({{ $c->id }})" variant="ghost" size="sm"><flux:icon variant="micro" icon="cog" /></flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-12 text-zinc-500">Nenhuma conta encontrada</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">{{ $this->contas->links() }}</div>
    </div>

    <!-- Modal -->
    <flux:modal name="payable-modal" class="max-w-2xl">
        <flux:modal.header>{{ $isEditing ? 'Editar Conta' : 'Nova Conta' }}</flux:modal.header>
        <flux:modal.body>
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <flux:field.label>Ano Vencimento *</flux:field.label>
                        <flux:input wire:model="ano_vencimento" type="number" min="2020" max="2100" />
                    </div>
                    <div>
                        <flux:field.label>Mes Vencimento *</flux:field.label>
                        <flux:input wire:model="mes_vencimento" type="number" min="1" max="12" />
                    </div>
                    <div>
                        <flux:field.label>Conta Origem *</flux:field.label>
                        <flux:input wire:model="conta_origem" placeholder="Origem da despesa" />
                    </div>
                </div>
                <div>
                    <flux:field.label>Contraparte *</flux:field.label>
                    <flux:select wire:model="contraparte_id">
                        <option value="">Selecione...</option>
                        @foreach($this->contrapartes as $c)
                            <option value="{{ $c->id }}">{{ $c->razao_social }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <flux:field.label>Descricao</flux:field.label>
                    <flux:textarea wire:model="descricao" rows="2" placeholder="Descricao da despesa" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:field.label>Valor Devido *</flux:field.label>
                        <flux:input wire:model="valor_devido" type="number" step="0.01" min="0" />
                    </div>
                    <div>
                        <flux:field.label>Valor Pago</flux:field.label>
                        <flux:input wire:model="valor_pago" type="number" step="0.01" min="0" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:field.label>Data Devida *</flux:field.label>
                        <flux:input wire:model="data_devida" type="date" />
                    </div>
                    <div>
                        <flux:field.label>Data Pagamento</flux:field.label>
                        <flux:input wire:model="data_pagamento" type="date" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:field.label>Data Emissao</flux:field.label>
                        <flux:input wire:model="data_emissao" type="date" />
                    </div>
                    <div>
                        <flux:field.label>Data Evento</flux:field.label>
                        <flux:input wire:model="data_evento" type="date" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:field.label>Tipo Doc Fiscal</flux:field.label>
                        <flux:input wire:model="tipo_doc_fiscal" placeholder="NF, RPA..." />
                    </div>
                    <div>
                        <flux:field.label>Num Doc Fiscal</flux:field.label>
                        <flux:input wire:model="num_doc_fiscal" placeholder="Numero do documento" />
                    </div>
                </div>
                <div>
                    <flux:field.label>Plano de Contas *</flux:field.label>
                    <flux:select wire:model="plano_contas_id">
                        <option value="">Selecione...</option>
                        @foreach($this->planoContas as $pc)
                            <option value="{{ $pc->codigo ?? $pc->id }}">{{ $pc->nome }} {{ $pc->codigo ? '('.$pc->codigo.')' : '' }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <flux:field.label>Status Pagamento *</flux:field.label>
                        <flux:select wire:model="status_pagamento">
                            <option value="pendente">Pendente</option>
                            <option value="processando">Processando</option>
                            <option value="pago">Pago</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:field.label>Meio Pagamento</flux:field.label>
                        <flux:input wire:model="meio_pagamento" placeholder="TED, DOC, PIX..." />
                    </div>
                    <div>
                        <flux:field.label>Contrato Ref</flux:field.label>
                        <flux:input wire:model="contrato_ref_id" type="number" placeholder="ID do contrato" />
                    </div>
                </div>
                <div>
                    <flux:field.label>Observacoes</flux:field.label>
                    <flux:textarea wire:model="observacoes" rows="2" />
                </div>
            </form>
        </flux:modal.body>
        <flux:modal.footer>
            <flux:button wire:click="closeModal" variant="ghost">Cancelar</flux:button>
            <flux:button wire:click="save" variant="primary">{{ $isEditing ? 'Atualizar' : 'Criar' }}</flux:button>
        </flux:modal.footer>
    </flux:modal>
</div>