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
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Splits</th>
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
                                @php
                                    $splitsPorDestino = $l->splits->groupBy('tipo_destinatario')->map(fn($s) => $s->sum('valor_absoluto'));
                                @endphp
                                @if($splitsPorDestino->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($splitsPorDestino as $destino => $valor)
                                            <flux:badge size="sm" color="{{ $destino === 'Panorama' ? 'primary' : ($destino === 'Coral' ? 'warning' : 'success') }}">
                                                {{ $destino[0] }}: {{ number_format($valor, 2, ',', '.') }}
                                            </flux:badge>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-zinc-400 text-xs">Sem splits</span>
                                @endif
                            </td>
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
                        <tr><td colspan="10" class="text-center py-12 text-zinc-500">Nenhum lancamento encontrado</td></tr>
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
                            <option value="aguardando_cambio">Aguardando Câmbio</option>
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

                <!-- INT-004: Campos de Câmbio (Admin only) -->
                @if($this->canManageCambio)
                    <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4 mt-4">
                        <flux:heading level="4" class="text-sm font-medium mb-3">Câmbio e Multimoeda</flux:heading>
                        <div class="grid grid-cols-4 gap-4">
                            <div>
                                <flux:field.label>Moeda Original</flux:field.label>
                                <flux:select wire:model="moeda_original">
                                    <option value="BRL">BRL</option>
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                    <option value="GBP">GBP</option>
                                </flux:select>
                            </div>
                            <div>
                                <flux:field.label>Valor USD</flux:field.label>
                                <flux:input wire:model="valor_usd" type="number" step="0.01" min="0" />
                            </div>
                            <div>
                                <flux:field.label>Valor EUR</flux:field.label>
                                <flux:input wire:model="valor_eur" type="number" step="0.01" min="0" />
                            </div>
                            <div>
                                <flux:field.label>Valor GBP</flux:field.label>
                                <flux:input wire:model="valor_gbp" type="number" step="0.01" min="0" />
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 mt-3">
                            <div>
                                <flux:field.label>Taxa Câmbio</flux:field.label>
                                <flux:input wire:model="taxa_cambio" type="number" step="0.000001" />
                            </div>
                            <div>
                                <flux:field.label>Tipo Câmbio</flux:field.label>
                                <flux:select wire:model="tipo_cambio">
                                    <option value="">Selecione...</option>
                                    <option value="oficial">Oficial (BCB)</option>
                                    <option value="manual">Manual</option>
                                </flux:select>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- PROV-003: Splits de Destino -->
                <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4 mt-4">
                    <div class="flex items-center justify-between mb-3">
                        <flux:heading level="4" class="text-sm font-medium">Splits de Destino</flux:heading>
                        <flux:button wire:click="addSplit" variant="outline" size="xs">
                            <flux:icon variant="micro" icon="plus" class="mr-1" />Adicionar Split
                        </flux:button>
                    </div>

                    @if(count($splits) > 0)
                        <div class="space-y-2 mb-3">
                            @foreach($splits as $index => $split)
                                <div class="flex items-end gap-2 p-3 bg-zinc-50 dark:bg-zinc-700/50 rounded-lg">
                                    <div class="flex-1">
                                        <flux:field.label class="text-xs">Destino</flux:field.label>
                                        <flux:select wire:model="splits.{{ $index }}.tipo_destinatario" class="mt-1">
                                            <option value="">Selecione...</option>
                                            <option value="Panorama">Panorama</option>
                                            <option value="Coral">Coral</option>
                                            <option value="Artista">Artista</option>
                                        </flux:select>
                                    </div>
                                    <div class="flex-1">
                                        <flux:field.label class="text-xs">Entidade</flux:field.label>
                                        <flux:select wire:model="splits.{{ $index }}.entidade_id" class="mt-1">
                                            <option value="">Selecione...</option>
                                            @foreach($this->artistas as $entidade)
                                                <option value="{{ $entidade->id }}">{{ $entidade->razao_social }}</option>
                                            @endforeach
                                        </flux:select>
                                    </div>
                                    <div class="w-28">
                                        <flux:field.label class="text-xs">%</flux:field.label>
                                        <flux:input wire:model="splits.{{ $index }}.valor_percentual" type="number" step="0.01" min="0" max="100" class="mt-1" />
                                    </div>
                                    <div class="w-32">
                                        <flux:field.label class="text-xs">Valor (R$)</flux:field.label>
                                        <flux:input wire:model="splits.{{ $index }}.valor_absoluto" type="number" step="0.01" min="0" class="mt-1" />
                                    </div>
                                    <flux:button wire:click="removeSplit({{ $index }})" variant="ghost" size="xs" class="text-red-500 mb-0.5">
                                        <flux:icon variant="micro" icon="trash" />
                                    </flux:button>
                                </div>
                            @endforeach
                        </div>

                        <!-- Resumo dos Splits -->
                        @php
                            $totalSplits = collect($splits)->sum(fn($s) => (float) ($s['valor_absoluto'] ?? 0));
                            $valorPrevisto = (float) ($valor_previsto ?: 0);
                            $diferenca = abs($valorPrevisto - $totalSplits);
                            $splitsValidos = $diferenca < 0.01;
                        @endphp
                        <div class="flex items-center justify-between p-3 rounded-lg {{ $splitsValidos ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' }}">
                            <div>
                                <span class="text-sm font-medium">Total Splits:</span>
                                <span class="font-bold ml-2">R$ {{ number_format($totalSplits, 2, ',', '.') }}</span>
                                <span class="text-zinc-500 mx-2">/</span>
                                <span class="text-sm">Valor Previsto: R$ {{ number_format($valorPrevisto, 2, ',', '.') }}</span>
                            </div>
                            <div>
                                @if($splitsValidos)
                                    <flux:badge color="success" size="sm">✓ Soma bate</flux:badge>
                                @else
                                    <flux:badge color="warning" size="sm">Diferença: R$ {{ number_format($diferenca, 2, ',', '.') }}</flux:badge>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-6 text-zinc-500 text-sm border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
                            Nenhum split adicionado. Clique em "Adicionar Split" para distribuir o valor entre Panorama/Coral/Artista.
                        </div>
                    @endif
                </div>
            </form>
        </flux:modal.body>
        <flux:modal.footer>
            <flux:button wire:click="closeModal" variant="ghost">Cancelar</flux:button>
            <flux:button wire:click="save" variant="primary">{{ $isEditing ? 'Atualizar' : 'Criar' }}</flux:button>
        </flux:modal.footer>
    </flux:modal>
</div>