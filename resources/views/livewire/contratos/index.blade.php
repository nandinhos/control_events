<div>
    <flux:separator variant="subtle" class="my-6" />

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading level="2">Contratos</flux:heading>
            <flux:subheading>Gerencie contratos de artistas e intermediação</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" variant="primary">
            <flux:icon variant="micro" icon="plus" class="mr-1.5" />
            Novo Contrato
        </flux:button>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="md:col-span-2">
                <flux:input wire:model.live.debounce.250ms="search" placeholder="Buscar por código ou evento..." icon="magnifying-glass" />
            </div>
            <div>
                <flux:select wire:model="filterStatusBooking" placeholder="Status Booking">
                    <option value="">Todos</option>
                    <option value="aberto">Aberto</option>
                    <option value="em_negociacao">Em Negociação</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="realizado">Realizado</option>
                    <option value="cancelado">Cancelado</option>
                </flux:select>
            </div>
            <div>
                <flux:select wire:model="filterAssinaturaStatus" placeholder="Status Assinatura">
                    <option value="">Todos</option>
                    <option value="pendente">Pendente</option>
                    <option value="enviada">Enviada</option>
                    <option value="assinado">Assinado</option>
                    <option value="recusado">Recusado</option>
                </flux:select>
            </div>
            <div>
                <flux:select wire:model="filterBooker" placeholder="Booker">
                    <option value="">Todos</option>
                    @foreach($this->bookers as $booker)
                        <option value="{{ $booker->id }}">{{ $booker->razao_social }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div class="flex items-center gap-2">
                <flux:button wire:click="resetFilters" variant="ghost" size="sm">Limpar</flux:button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Código</flux:table.column>
                <flux:table.column>Evento</flux:table.column>
                <flux:table.column>Artista</flux:table.column>
                <flux:table.column>Booker</flux:table.column>
                <flux:table.column>Data Evento</flux:table.column>
                <flux:table.column>Valor</flux:table.column>
                <flux:table.column>Booking</flux:table.column>
                <flux:table.column>Assinatura</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->contratos as $contrato)
                    <flux:table.row>
                        <flux:table.cell>
                            <a href="{{ route('contratos.show', $contrato) }}" class="font-mono text-sm text-violet-600 hover:text-violet-700">
                                {{ $contrato->codigo_contrato }}
                            </a>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="font-medium">{{ $contrato->nome_evento }}</div>
                            <div class="text-xs text-zinc-500">{{ $contrato->cidade_evento }}, {{ $contrato->pais_evento }}</div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $contrato->artista->razao_social ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $contrato->booker->razao_social ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $contrato->data_evento?->format('d/m/Y') ?? '-' }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="font-medium">{{ number_format($contrato->valor_bruto, 2, ',', '.') }}</div>
                            <div class="text-xs text-zinc-500">{{ $contrato->moeda }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="statusBadgeColor($contrato->status_booking)" size="sm">
                                {{ statusLabel($contrato->status_booking) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="assinaturaBadgeColor($contrato->assinatura_status)" size="sm">
                                {{ statusLabel($contrato->assinatura_status) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="text-right">
                            <flux:button wire:click="openEdit({{ $contrato->id }})" variant="ghost" size="sm">
                                <flux:icon variant="micro" icon="cog" />
                            </flux:button>
                            <flux:button wire:click="$dispatch('confirm-delete', { id: {{ $contrato->id }} })" variant="ghost" size="sm" class="text-red-500">
                                <flux:icon variant="micro" icon="x-mark" />
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="text-center py-12 text-zinc-500">
                            Nenhum contrato encontrado
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
            {{ $this->contratos->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <flux:modal.trigger name="contrato-modal" wire:ignore>
        @if($showModal)
            <flux:modal name="contrato-modal" class="max-w-2xl">
                <flux:modal.header>
                    {{ $isEditing ? 'Editar Contrato' : 'Novo Contrato' }}
                </flux:modal.header>

                <flux:modal.body>
                    <form wire:submit="save" class="space-y-6">
                        <!-- Dados principais -->
                        <div>
                            <flux:field.label>Agência</flux:field.label>
                            <flux:input wire:model="agencia_sigla" placeholder="SIGLA" class="uppercase" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <flux:field.label>Código do Contrato *</flux:field.label>
                                <flux:input wire:model="codigo_contrato" placeholder="CT-2025-001" />
                            </div>
                            <div>
                                <flux:field.label>Moeda *</flux:field.label>
                                <flux:select wire:model="moeda">
                                    <option value="BRL">BRL - Real</option>
                                    <option value="USD">USD - Dólar</option>
                                    <option value="EUR">EUR - Euro</option>
                                </flux:select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <flux:field.label>Artista *</flux:field.label>
                                <flux:select wire:model="artista_id">
                                    <option value="">Selecione...</option>
                                    @foreach($this->artistas as $artista)
                                        <option value="{{ $artista->id }}">{{ $artista->razao_social }}</option>
                                    @endforeach
                                </flux:select>
                            </div>
                            <div>
                                <flux:field.label>Booker *</flux:field.label>
                                <flux:select wire:model="booker_id">
                                    <option value="">Selecione...</option>
                                    @foreach($this->bookers as $booker)
                                        <option value="{{ $booker->id }}">{{ $booker->razao_social }}</option>
                                    @endforeach
                                </flux:select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <flux:field.label>Valor Bruto *</flux:field.label>
                                <flux:input wire:model="valor_bruto" type="number" step="0.01" min="0" placeholder="0.00" />
                            </div>
                            <div>
                                <flux:field.label>Comissão</flux:field.label>
                                <flux:input wire:model="comissao_valor" type="number" step="0.01" min="0" placeholder="0.00" />
                            </div>
                        </div>

                        <!-- Evento -->
                        <div>
                            <flux:field.label>Nome do Evento *</flux:field.label>
                            <flux:input wire:model="nome_evento" placeholder="Nome completo do evento" />
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <flux:field.label>Cidade</flux:field.label>
                                <flux:input wire:model="cidade_evento" placeholder="Cidade" />
                            </div>
                            <div>
                                <flux:field.label>Estado (UF)</flux:field.label>
                                <flux:input wire:model="estado_evento" placeholder="SP" maxlength="2" class="uppercase" />
                            </div>
                            <div>
                                <flux:field.label>País</flux:field.label>
                                <flux:input wire:model="pais_evento" placeholder="Brasil" />
                            </div>
                        </div>
                        <div>
                            <flux:field.label>Local</flux:field.label>
                            <flux:input wire:model="local_evento" placeholder="Local do evento" />
                        </div>

                        <!-- Datas e status -->
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <flux:field.label>Semana Início *</flux:field.label>
                                <flux:input wire:model="semana_inicio" type="date" />
                            </div>
                            <div>
                                <flux:field.label>Data Venda</flux:field.label>
                                <flux:input wire:model="data_venda" type="date" />
                            </div>
                            <div>
                                <flux:field.label>Data Evento *</flux:field.label>
                                <flux:input wire:model="data_evento" type="date" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <flux:field.label>Status Booking *</flux:field.label>
                                <flux:select wire:model="status_booking">
                                    <option value="aberto">Aberto</option>
                                    <option value="em_negociacao">Em Negociação</option>
                                    <option value="confirmado">Confirmado</option>
                                    <option value="realizado">Realizado</option>
                                    <option value="cancelado">Cancelado</option>
                                </flux:select>
                            </div>
                            <div>
                                <flux:field.label>Status Assinatura *</flux:field.label>
                                <flux:select wire:model="assinatura_status">
                                    <option value="pendente">Pendente</option>
                                    <option value="enviada">Enviada</option>
                                    <option value="assinado">Assinado</option>
                                    <option value="recusado">Recusado</option>
                                </flux:select>
                            </div>
                        </div>
                        <div>
                            <flux:field.label>Região</flux:field.label>
                            <flux:input wire:model="regiao_evento" placeholder="Norte, Sul, Sudeste..." />
                        </div>
                    </form>
                </flux:modal.body>

                <flux:modal.footer>
                    <flux:button wire:click="closeModal" variant="ghost">Cancelar</flux:button>
                    <flux:button wire:click="save" variant="primary">
                        {{ $isEditing ? 'Atualizar' : 'Criar Contrato' }}
                    </flux:button>
                </flux:modal.footer>
            </flux:modal>
        @endif
    </flux:modal.trigger>

    <!-- Delete Confirmation placeholder -->
</div>