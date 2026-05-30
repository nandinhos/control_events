<div>
    <flux:separator variant="subtle" class="my-6" />
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading level="2">Contas a Receber</flux:heading>
            <flux:subheading>Gerencie parcelas de booking e lancamentos extra contratuais</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" variant="primary">
            <flux:icon variant="micro" icon="plus" class="mr-1.5" />
            Novo Lancamento
        </flux:button>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <div class="md:col-span-2">
                <flux:input wire:model.live.debounce.250ms="search" placeholder="Buscar evento..." icon="magnifying-glass" />
            </div>
            <flux:select wire:model="filterStatusPagamento" placeholder="Status Pagamento">
                <option value="">Todos</option>
                <option value="aberto">Aberto</option>
                <option value="quitado">Quitado</option>
                <option value="vencido">Vencido</option>
                <option value="cancelado">Cancelado</option>
            </flux:select>
            <flux:select wire:model="filterStatusBooking" placeholder="Status Booking">
                <option value="">Todos</option>
                <option value="aberto">Aberto</option>
                <option value="fechado">Fechado</option>
            </flux:select>
            <flux:select wire:model="filterBooker" placeholder="Booker">
                <option value="">Todos</option>
                @foreach($this->bookers as $b)
                    <option value="{{ $b->id }}">{{ $b->razao_social }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model="filterRegistroContabil" placeholder="Reg. Contabil">
                <option value="">Todos</option>
                @foreach($this->registroContabilOptions as $opt)
                    <option value="{{ $opt }}">{{ $opt }}</option>
                @endforeach
            </flux:select>
        </div>
        <div class="flex gap-2 mt-3">
            <flux:input wire:model="filterMesBase" type="month" placeholder="Mes Base" class="w-40" />
            <flux:button wire:click="resetFilters" variant="ghost" size="sm">Limpar</flux:button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-zinc-50 dark:bg-zinc-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Evento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Booker</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Parcela</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Vencimento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Valor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Recebido</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Booking</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($this->lancamentos as $l)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $l->nome_evento }}</div>
                                <div class="text-xs text-zinc-500">{{ $l->registro_contabil }} | {{ $l->tipo_lancamento }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $l->booker->razao_social ?? '-' }}</td>
                            <td class="px-4 py-3 font-mono text-sm">{{ $l->parcela_numero }}</td>
                            <td class="px-4 py-3 text-sm">{{ $l->vencimento_atual?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ number_format($l->valor_previsto, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-green-600 font-medium">{{ number_format($l->valor_recebido, 2, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <flux:badge color="{{ $l->status_pagamento === 'quitado' ? 'success' : ($l->isVencido ? 'danger' : 'warning') }}" size="sm">{{ ucfirst($l->status_pagamento) }}</flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge color="{{ $l->status_booking === 'fechado' ? 'success' : 'neutral' }}" size="sm">{{ $l->status_booking }}</flux:badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <flux:button wire:click="openEdit({{ $l->id }})" variant="ghost" size="sm"><flux:icon variant="micro" icon="cog" /></flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-12 text-zinc-500">Nenhum lancamento encontrado</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">{{ $this->lancamentos->links() }}</div>
    </div>

    <!-- Modal -->
    <flux:modal name="receivable-modal" class="max-w-2xl">
        <flux:modal.header>{{ $isEditing ? 'Editar Lancamento' : 'Novo Lancamento' }}</flux:modal.header>
        <flux:modal.body>
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:field.label>Contrato (Booking) *</flux:field.label>
                        <flux:select wire:model="contrato_id">
                            <option value="">Selecione...</option>
                            @foreach($this->contratosConfirmados as $c)
                                <option value="{{ $c->id }}">{{ $c->codigo_contrato }} - {{ $c->nome_evento }}</option>
                            @endforeach
                        </flux:select>
                        <flux:field.description>Contrato deve estar com status "Confirmado"</flux:field.description>
                    </div>
                    <div>
                        <flux:field.label>Mes Base *</flux:field.label>
                        <flux:input wire:model="mes_base" type="month" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:field.label>Booker *</flux:field.label>
                        <flux:select wire:model="booker_id">
                            <option value="">Selecione...</option>
                            @foreach($this->bookers as $b)
                                <option value="{{ $b->id }}">{{ $b->razao_social }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div>
                        <flux:field.label>Artista *</flux:field.label>
                        <flux:select wire:model="artista_id">
                            <option value="">Selecione...</option>
                            @foreach($this->artistas as $a)
                                <option value="{{ $a->id }}">{{ $a->razao_social }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
                <div>
                    <flux:field.label>Contratante *</flux:field.label>
                    <flux:select wire:model="contratante_id">
                        <option value="">Selecione...</option>
                        @foreach($this->contratantes as $c)
                            <option value="{{ $c->id }}">{{ $c->razao_social }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <flux:field.label>Nome do Evento *</flux:field.label>
                    <flux:input wire:model="nome_evento" placeholder="Nome do evento" />
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <flux:field.label>Parcela *</flux:field.label>
                        <flux:input wire:model="parcela_numero" placeholder="1/3" />
                    </div>
                    <div>
                        <flux:field.label>Tipo *</flux:field.label>
                        <flux:select wire:model="tipo_lancamento">
                            <option value="Booking">Booking</option>
                            <option value="Extra Contratual">Extra Contratual</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:field.label>Reg. Contabil *</flux:field.label>
                        <flux:select wire:model="registro_contabil">
                            <option value="Panorama">Panorama</option>
                            <option value="Coral">Coral</option>
                            <option value="Artista">Artista</option>
                        </flux:select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:field.label>Valor Previsto *</flux:field.label>
                        <flux:input wire:model="valor_previsto" type="number" step="0.01" min="0" />
                    </div>
                    <div>
                        <flux:field.label>Data Evento *</flux:field.label>
                        <flux:input wire:model="data_evento" type="date" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:field.label>Vencimento Original *</flux:field.label>
                        <flux:input wire:model="vencimento_original" type="date" />
                    </div>
                    <div>
                        <flux:field.label>Vencimento Atual *</flux:field.label>
                        <flux:input wire:model="vencimento_atual" type="date" />
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <flux:field.label>Status Pagamento *</flux:field.label>
                        <flux:select wire:model="status_pagamento">
                            <option value="aberto">Aberto</option>
                            <option value="quitado">Quitado</option>
                            <option value="vencido">Vencido</option>
                            <option value="cancelado">Cancelado</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:field.label>Status Booking *</flux:field.label>
                        <flux:select wire:model="status_booking">
                            <option value="aberto">Aberto</option>
                            <option value="fechado">Fechado</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:field.label>Cashflow Categoria</flux:field.label>
                        <flux:input wire:model="cashflow_categoria" placeholder="Categoria" />
                    </div>
                </div>
            </form>
        </flux:modal.body>
        <flux:modal.footer>
            <flux:button wire:click="closeModal" variant="ghost">Cancelar</flux:button>
            <flux:button wire:click="save" variant="primary">{{ $isEditing ? 'Atualizar' : 'Criar' }}</flux:button>
        </flux:modal.footer>
    </flux:modal>
</div>