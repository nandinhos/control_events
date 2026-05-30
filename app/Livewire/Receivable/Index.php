<?php

namespace App\Livewire\Receivable;

use App\Models\ContasAReceber;
use App\Models\Contrato;
use App\Models\Entidade;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatusBooking = '';
    public string $filterStatusPagamento = '';
    public string $filterMesBase = '';
    public string $filterBooker = '';
    public string $filterRegistroContabil = '';

    public bool $showModal = false;
    public bool $isEditing = false;
    public ?ContasAReceber $lancamento = null;

    // Form fields
    public string $contrato_id = '';
    public string $mes_base = '';
    public string $booker_id = '';
    public string $status_booking = 'aberto';
    public string $registro_contabil = '';
    public string $data_evento = '';
    public string $artista_id = '';
    public string $nome_evento = '';
    public string $contratante_id = '';
    public string $tipo_lancamento = 'Booking';
    public string $parcela_numero = '';
    public string $valor_previsto = '';
    public string $vencimento_original = '';
    public string $vencimento_atual = '';
    public string $status_pagamento = 'aberto';
    public string $cashflow_categoria = '';

    // INT-004: Multicurrency fields
    public string $moeda_original = 'BRL';
    public string $valor_usd = '';
    public string $valor_eur = '';
    public string $valor_gbp = '';
    public string $taxa_cambio = '';
    public string $tipo_cambio = '';

    // Splits
    public array $splits = [];

    protected $listeners = ['lancamento-created' => '$refresh', 'lancamento-updated' => '$refresh'];

    #[Computed]
    public function lancamentos()
    {
        return ContasAReceber::query()
            ->when($this->search, fn($q) => $q->where('nome_evento', 'like', "%{$this->search}%")
                ->orWhere('parcela_numero', 'like', "%{$this->search}%"))
            ->when($this->filterStatusBooking, fn($q) => $q->where('status_booking', $this->filterStatusBooking))
            ->when($this->filterStatusPagamento, fn($q) => $q->where('status_pagamento', $this->filterStatusPagamento))
            ->when($this->filterMesBase, fn($q) => $q->where('mes_base', $this->filterMesBase))
            ->when($this->filterBooker, fn($q) => $q->where('booker_id', $this->filterBooker))
            ->when($this->filterRegistroContabil, fn($q) => $q->where('registro_contabil', $this->filterRegistroContabil))
            ->with(['artista:id,razao_social', 'booker:id,razao_social', 'contratante:id,razao_social', 'contrato:id,codigo_contrato'])
            ->orderByDesc('vencimento_atual')
            ->paginate(15);
    }

    #[Computed]
    public function bookers()
    {
        return Entidade::query()->orderBy('razao_social')->get(['id', 'razao_social']);
    }

    #[Computed]
    public function artistas()
    {
        return Entidade::query()->orderBy('razao_social')->get(['id', 'razao_social']);
    }

    #[Computed]
    public function contratantes()
    {
        return Entidade::query()->orderBy('razao_social')->get(['id', 'razao_social']);
    }

    #[Computed]
    public function contratosConfirmados()
    {
        return Contrato::query()
            ->where('status_booking', 'confirmado')
            ->with(['artista:id,razao_social', 'booker:id,razao_social'])
            ->orderByDesc('created_at')
            ->get(['id', 'codigo_contrato', 'nome_evento', 'artista_id', 'booker_id']);
    }

    #[Computed]
    public function registroContabilOptions()
    {
        return ['Panorama', 'Coral', 'Artista'];
    }

    // INT-004: Admin-only exchange rate management
    #[Computed]
    public function canManageCambio(): bool
    {
        return auth()->check() && auth()->user()->isAdminFinance();
    }

    public function resetFilters(): void
    {
        [$this->search, $this->filterStatusBooking, $this->filterStatusPagamento, $this->filterMesBase, $this->filterBooker, $this->filterRegistroContabil] = ['', '', '', '', '', ''];
    }

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(ContasAReceber $lancamento): void
    {
        $this->resetValidation();
        $this->lancamento = $lancamento;
        $this->contrato_id = (string) $lancamento->contrato_id;
        $this->mes_base = $lancamento->mes_base?->format('Y-m') ?? '';
        $this->booker_id = (string) $lancamento->booker_id;
        $this->status_booking = $lancamento->status_booking;
        $this->registro_contabil = $lancamento->registro_contabil;
        $this->data_evento = $lancamento->data_evento?->format('Y-m-d') ?? '';
        $this->artista_id = (string) $lancamento->artista_id;
        $this->nome_evento = $lancamento->nome_evento;
        $this->contratante_id = (string) $lancamento->contratante_id;
        $this->tipo_lancamento = $lancamento->tipo_lancamento;
        $this->parcela_numero = $lancamento->parcela_numero;
        $this->valor_previsto = (string) $lancamento->valor_previsto;
        $this->vencimento_original = $lancamento->vencimento_original?->format('Y-m-d') ?? '';
        $this->vencimento_atual = $lancamento->vencimento_atual?->format('Y-m-d') ?? '';
        $this->status_pagamento = $lancamento->status_pagamento;
        $this->cashflow_categoria = $lancamento->cashflow_categoria ?? '';

        // INT-004: Multicurrency fields
        $this->moeda_original = $lancamento->moeda_original ?? 'BRL';
        $this->valor_usd = (string) $lancamento->valor_usd;
        $this->valor_eur = (string) $lancamento->valor_eur;
        $this->valor_gbp = (string) $lancamento->valor_gbp;
        $this->taxa_cambio = (string) ($lancamento->taxa_cambio ?? '');
        $this->tipo_cambio = $lancamento->tipo_cambio ?? '';

        $this->splits = $lancamento->splits->map(fn($s) => [
            'tipo_destinatario' => $s->tipo_destinatario,
            'entidade_id' => (string) $s->entidade_id,
            'valor_percentual' => (string) $s->valor_percentual,
            'valor_absoluto' => (string) $s->valor_absoluto,
        ])->toArray();
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $rules = [
            'contrato_id' => 'nullable|exists:contratos,id',
            'mes_base' => 'required|date',
            'booker_id' => 'required|exists:entidades,id',
            'status_booking' => 'required|in:aberto,fechado',
            'registro_contabil' => 'required|in:Panorama,Coral,Artista',
            'data_evento' => 'required|date',
            'artista_id' => 'required|exists:entidades,id',
            'nome_evento' => 'required|string|max:255',
            'contratante_id' => 'required|exists:entidades,id',
            'tipo_lancamento' => 'required|in:Booking,Extra Contratual',
            'parcela_numero' => 'required|string|max:10',
            'valor_previsto' => 'required|numeric|min:0',
            'vencimento_original' => 'required|date',
            'vencimento_atual' => 'required|date',
            'status_pagamento' => 'required|in:aberto,quitado,vencido,cancelado,aguardando_cambio',
            'cashflow_categoria' => 'nullable|string|max:100',
            // INT-004: Multicurrency validation
            'moeda_original' => 'nullable|in:BRL,USD,EUR,GBP',
            'valor_usd' => 'nullable|numeric|min:0',
            'valor_eur' => 'nullable|numeric|min:0',
            'valor_gbp' => 'nullable|numeric|min:0',
            'taxa_cambio' => 'nullable|numeric|min:0',
            'tipo_cambio' => 'nullable|in:oficial,manual',
        ];

        if (!$this->isEditing) {
            $rules['contrato_id'] = 'required|exists:contratos,id';
        }

        $data = $this->validate($rules);

        // Validate: if contrato_id provided, status must be "confirmado"
        if (!empty($data['contrato_id'])) {
            $contrato = Contrato::find($data['contrato_id']);
            if ($contrato && $contrato->status_booking !== 'confirmado') {
                $this->addError('contrato_id', 'Contrato precisa ter status "Confirmado" para criar parcelas Booking.');
                return;
            }
        }

        // Auto-calculate aging_dias
        $data['aging_dias'] = (int) now()->diffInDays($data['vencimento_atual'], false);

        if ($this->isEditing) {
            $this->lancamento->update($data);
        } else {
            $lancamento = ContasAReceber::create($data);
            $this->lancamento = $lancamento;
        }

        // Save splits with validation
        if (!empty($this->splits)) {
            // SECURITY: Validate tipo_destinatario against allowlist
            $validDestinos = \App\Services\SplitsValidationService::DESTINOS_VALIDOS;
            foreach ($this->splits as $index => $split) {
                if (empty($split['tipo_destinatario'])) continue;
                if (!in_array($split['tipo_destinatario'], $validDestinos, true)) {
                    $this->addError("splits.{$index}.tipo_destinatario", "Destino '{$split['tipo_destinatario']}' inválido. Use: " . implode(', ', $validDestinos));
                    return;
                }
            }

            // FINANCIAL INTEGRITY: Validate split sum matches valor_previsto
            $somaSplits = array_sum(array_column(array_filter($this->splits, fn($s) => !empty($s['tipo_destinatario'])), 'valor_absoluto'));
            $valorPrevisto = (float) ($data['valor_previsto'] ?? 0);
            if (count($this->splits) > 0 && abs($somaSplits - $valorPrevisto) >= 0.01) {
                $this->addError('valor_previsto', "Soma dos splits (R$ " . number_format($somaSplits, 2, ',', '.') . ") não corresponde ao valor previsto (R$ " . number_format($valorPrevisto, 2, ',', '.') . ").");
                return;
            }

            $this->lancamento->splits()->delete();
            foreach ($this->splits as $split) {
                if (empty($split['tipo_destinatario']) || empty($split['entidade_id'])) continue;
                $this->lancamento->splits()->create([
                    'tipo_destinatario' => $split['tipo_destinatario'],
                    'entidade_id' => (int) $split['entidade_id'],
                    'valor_percentual' => (float) ($split['valor_percentual'] ?? 0),
                    'valor_absoluto' => (float) ($split['valor_absoluto'] ?? 0),
                ]);
            }
        }

        $msg = $this->isEditing ? 'Lancamento atualizado.' : 'Lancamento criado.';
        $this->dispatch('lancamento-' . ($this->isEditing ? 'updated' : 'created'));
        $this->notify($msg);
        $this->closeModal();
    }

    public function addSplit(): void
    {
        $this->splits[] = ['tipo_destinatario' => '', 'entidade_id' => '', 'valor_percentual' => '', 'valor_absoluto' => ''];
    }

    public function removeSplit(int $index): void
    {
        array_splice($this->splits, $index, 1);
    }

    private function resetForm(): void
    {
        $this->lancamento = null;
        $this->contrato_id = '';
        $this->mes_base = '';
        $this->booker_id = '';
        $this->status_booking = 'aberto';
        $this->registro_contabil = 'Panorama';
        $this->data_evento = '';
        $this->artista_id = '';
        $this->nome_evento = '';
        $this->contratante_id = '';
        $this->tipo_lancamento = 'Booking';
        $this->parcela_numero = '';
        $this->valor_previsto = '';
        $this->vencimento_original = '';
        $this->vencimento_atual = '';
        $this->status_pagamento = 'aberto';
        $this->cashflow_categoria = '';
        $this->moeda_original = 'BRL';
        $this->valor_usd = '';
        $this->valor_eur = '';
        $this->valor_gbp = '';
        $this->taxa_cambio = '';
        $this->tipo_cambio = '';
        $this->splits = [];
    }

    private function notify(string $message): void
    {
        $this->dispatch('notify', ['message' => $message, 'type' => 'success']);
    }

    public function render()
    {
        return view('livewire.receivable.index');
    }
}