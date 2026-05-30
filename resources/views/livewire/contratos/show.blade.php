<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <flux:heading level="2">{{ $contrato->codigo_contrato }}</flux:heading>
            <flux:subheading>{{ $contrato->nome_evento }}</flux:subheading>
        </div>
        <div class="flex gap-3">
            <flux:button href="{{ route('contratos.index') }}" variant="ghost">
                <flux:icon variant="micro" icon="arrow-right-start-on-rectangle" class="mr-1.5" />
                Voltar
            </flux:button>
            <flux:button wire:click="$dispatch('open-edit', { id: {{ $contrato->id }} })" variant="primary">
                <flux:icon variant="micro" icon="cog" class="mr-1.5" />
                Editar
            </flux:button>
        </div>
    </div>

    <!-- Meta badges -->
    <div class="flex gap-3">
        <flux:badge color="{{ $contrato->status_booking === 'confirmado' ? 'success' : ($contrato->status_booking === 'cancelado' ? 'danger' : 'warning') }}" size="lg">
            {{ ucfirst(str_replace('_', ' ', $contrato->status_booking)) }}
        </flux:badge>
        <flux:badge color="{{ $contrato->assinatura_status === 'assinado' ? 'success' : ($contrato->assinatura_status === 'recusado' ? 'danger' : 'neutral') }}" size="lg">
            Assinatura: {{ ucfirst($contrato->assinatura_status) }}
        </flux:badge>
        <flux:badge color="neutral" size="lg">{{ $contrato->moeda }}</flux:badge>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Valor Total</div>
            <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ number_format($contrato->valor_bruto, 2, ',', '.') }}
            </div>
            <div class="text-xs text-zinc-500">{{ $contrato->moeda }}</div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">A Receber</div>
            <div class="text-2xl font-bold text-violet-600">
                {{ number_format($this->totalReceber, 2, ',', '.') }}
            </div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Recebido</div>
            <div class="text-2xl font-bold text-green-600">
                {{ number_format($this->totalRecebido, 2, ',', '.') }}
            </div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <div class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Parcelas</div>
            <div class="text-2xl font-bold text-zinc-600">{{ $this->parcelas->count() }}</div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <div class="text-xs text-zinc-500 uppercase mb-1">Artista</div>
                <div class="font-medium">{{ $contrato->artista->razao_social ?? '-' }}</div>
            </div>
            <div>
                <div class="text-xs text-zinc-500 uppercase mb-1">Booker</div>
                <div class="font-medium">{{ $contrato->booker->razao_social ?? '-' }}</div>
            </div>
            <div>
                <div class="text-xs text-zinc-500 uppercase mb-1">Data Evento</div>
                <div class="font-medium">{{ $contrato->data_evento?->format('d/m/Y') ?? '-' }}</div>
            </div>
            <div>
                <div class="text-xs text-zinc-500 uppercase mb-1">Local</div>
                <div class="font-medium">{{ $contrato->local_evento ?: '-' }}</div>
            </div>
            <div>
                <div class="text-xs text-zinc-500 uppercase mb-1">Cidade</div>
                <div class="font-medium">{{ $contrato->cidade_evento }}, {{ $contrato->estado_evento }}</div>
            </div>
            <div>
                <div class="text-xs text-zinc-500 uppercase mb-1">País</div>
                <div class="font-medium">{{ $contrato->pais_evento }}</div>
            </div>
            <div>
                <div class="text-xs text-zinc-500 uppercase mb-1">Agência</div>
                <div class="font-medium">{{ $contrato->agencia_sigla }}</div>
            </div>
            <div>
                <div class="text-xs text-zinc-500 uppercase mb-1">Comissão</div>
                <div class="font-medium">{{ $contrato->comissao_valor ? number_format($contrato->comissao_valor, 2, ',', '.') : '-' }}</div>
            </div>
        </div>
    </div>

    <!-- Parcelas Booking -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
            <flux:heading level="3">Parcelas de Booking</flux:heading>
        </div>

        @if($this->parcelas->isEmpty())
            <div class="p-12 text-center text-zinc-500">
                Nenhuma parcela criada para este contrato.
                @if(!$this->aguardandoFechamento)
                    <br><span class="text-xs">Contrato precisa estar com status "confirmado" para criar parcelas.</span>
                @endif
            </div>
        @else
            <table class="w-full">
                <thead class="bg-zinc-50 dark:bg-zinc-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Parcela</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Vencimento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Valor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Recebido</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Booking</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($this->parcelas as $parcela)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                            <td class="px-4 py-3 font-mono text-sm">{{ $parcela->parcela_numero }}</td>
                            <td class="px-4 py-3 text-sm">{{ $parcela->vencimento_atual?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ number_format($parcela->valor_previsto, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-green-600 font-medium">{{ number_format($parcela->valor_recebido, 2, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <flux:badge color="{{ $parcela->status_pagamento === 'quitado' ? 'success' : ($parcela->isVencido ? 'danger' : 'warning') }}" size="sm">
                                    {{ ucfirst($parcela->status_pagamento) }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge color="{{ $parcela->status_booking === 'fechado' ? 'success' : 'neutral' }}" size="sm">
                                    {{ $parcela->status_booking }}
                                </flux:badge>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>