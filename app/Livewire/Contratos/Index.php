<?php

namespace App\Livewire\Contratos;

use App\Models\Contrato;
use App\Models\Entidade;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatusBooking = '';
    public string $filterAssinaturaStatus = '';
    public string $filterBooker = '';
    public ?string $filterDataInicio = null;
    public ?string $filterDataFim = null;

    public ?Contrato $contrato = null;
    public bool $showModal = false;
    public bool $isEditing = false;

    // Form fields
    public string $codigo_contrato = '';
    public string $semana_inicio = '';
    public string $data_venda = '';
    public string $data_evento = '';
    public string $artista_id = '';
    public string $booker_id = '';
    public string $valor_bruto = '';
    public string $moeda = 'BRL';
    public string $comissao_valor = '';
    public string $local_evento = '';
    public string $nome_evento = '';
    public string $cidade_evento = '';
    public string $estado_evento = '';
    public string $regiao_evento = '';
    public string $pais_evento = 'Brasil';
    public string $agencia_sigla = '';
    public string $status_booking = 'aberto';
    public string $assinatura_status = 'pendente';

    protected $listeners = ['contrato-created' => '$refresh', 'contrato-updated' => '$refresh', 'contrato-deleted' => '$refresh'];

    // =====================================================
    // COMPUTED / QUERIES
    // =====================================================

    #[Computed]
    public function contratos()
    {
        return Contrato::query()
            ->when($this->search, fn($q) => $q->where('codigo_contrato', 'like', "%{$this->search}%")
                ->orWhere('nome_evento', 'like', "%{$this->search}%"))
            ->when($this->filterStatusBooking, fn($q) => $q->where('status_booking', $this->filterStatusBooking))
            ->when($this->filterAssinaturaStatus, fn($q) => $q->where('assinatura_status', $this->filterAssinaturaStatus))
            ->when($this->filterBooker, fn($q) => $q->where('booker_id', $this->filterBooker))
            ->when($this->filterDataInicio, fn($q) => $q->whereDate('data_evento', '>=', $this->filterDataInicio))
            ->when($this->filterDataFim, fn($q) => $q->whereDate('data_evento', '<=', $this->filterDataFim))
            ->with(['artista:id,razao_social,nome_fantasia', 'booker:id,razao_social'])
            ->orderByDesc('created_at')
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

    // =====================================================
    // MOUNT / RESET
    // =====================================================

    public function mount(): void
    {
        $this->resetFilters();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterStatusBooking = '';
        $this->filterAssinaturaStatus = '';
        $this->filterBooker = '';
        $this->filterDataInicio = null;
        $this->filterDataFim = null;
    }

    // =====================================================
    // OPEN / CLOSE MODAL
    // =====================================================

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(Contrato $contrato): void
    {
        $this->resetValidation();
        $this->contrato = $contrato;
        $this->codigo_contrato = $contrato->codigo_contrato;
        $this->semana_inicio = $contrato->semana_inicio?->format('Y-m-d') ?? '';
        $this->data_venda = $contrato->data_venda?->format('Y-m-d') ?? '';
        $this->data_evento = $contrato->data_evento?->format('Y-m-d') ?? '';
        $this->artista_id = (string) $contrato->artista_id;
        $this->booker_id = (string) $contrato->booker_id;
        $this->valor_bruto = (string) $contrato->valor_bruto;
        $this->moeda = $contrato->moeda;
        $this->comissao_valor = (string) ($contrato->comissao_valor ?? '');
        $this->local_evento = $contrato->local_evento ?? '';
        $this->nome_evento = $contrato->nome_evento;
        $this->cidade_evento = $contrato->cidade_evento ?? '';
        $this->estado_evento = $contrato->estado_evento ?? '';
        $this->regiao_evento = $contrato->regiao_evento ?? '';
        $this->pais_evento = $contrato->pais_evento;
        $this->agencia_sigla = $contrato->agencia_sigla;
        $this->status_booking = $contrato->status_booking;
        $this->assinatura_status = $contrato->assinatura_status;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    // =====================================================
    // STORE / UPDATE
    // =====================================================

    public function save(): void
    {
        $data = $this->validate([
            'codigo_contrato' => 'required|string|max:50|unique:contratos,codigo_contrato' . ($this->isEditing ? ",{$this->contrato->id}" : ''),
            'semana_inicio' => 'required|date',
            'data_venda' => 'nullable|date',
            'data_evento' => 'required|date',
            'artista_id' => 'required|exists:entidades,id',
            'booker_id' => 'required|exists:entidades,id',
            'valor_bruto' => 'required|numeric|min:0',
            'moeda' => 'required|in:BRL,USD,EUR',
            'comissao_valor' => 'nullable|numeric|min:0',
            'local_evento' => 'nullable|string|max:255',
            'nome_evento' => 'required|string|max:255',
            'cidade_evento' => 'nullable|string|max:100',
            'estado_evento' => 'nullable|string|max:2',
            'regiao_evento' => 'nullable|string|max:50',
            'pais_evento' => 'nullable|string|max:100',
            'agencia_sigla' => 'required|string|max:20',
            'status_booking' => 'required|in:aberto,em_negociacao,confirmado,realizado,cancelado',
            'assinatura_status' => 'required|in:pendente,enviada,assinado,recusado',
        ]);

        $data['data_venda'] = $data['data_venda'] ?? null;
        $data['comissao_valor'] = $data['comissao_valor'] ?? null;

        if ($this->isEditing) {
            $this->contrato->update($data);
            $this->dispatch('contrato-updated');
            $this->notify('Contrato atualizado com sucesso.');
        } else {
            Contrato::create($data);
            $this->dispatch('contrato-created');
            $this->notify('Contrato criado com sucesso.');
        }

        $this->closeModal();
    }

    #[On('confirm-delete')]
    public function delete(Contrato $contrato): void
    {
        $contrato->delete();
        $this->dispatch('contrato-deleted');
        $this->notify('Contrato removido.', 'error');
    }

    // =====================================================
    // HELPERS
    // =====================================================

    public function statusBadgeColor(string $status): string
    {
        return match($status) {
            'aberto' => 'neutral',
            'em_negociacao' => 'warning',
            'confirmado' => 'success',
            'realizado' => 'primary',
            'cancelado' => 'danger',
            default => 'neutral',
        };
    }

    public function assinaturaBadgeColor(string $status): string
    {
        return match($status) {
            'pendente' => 'neutral',
            'enviada' => 'warning',
            'assinado' => 'success',
            'recusado' => 'danger',
            default => 'neutral',
        };
    }

    public function statusLabel(string $status): string
    {
        return match($status) {
            'aberto' => 'Aberto',
            'em_negociacao' => 'Em Negociação',
            'confirmado' => 'Confirmado',
            'realizado' => 'Realizado',
            'cancelado' => 'Cancelado',
            'pendente' => 'Pendente',
            'enviada' => 'Enviada',
            default => $status,
        };
    }

    private function resetForm(): void
    {
        $this->contrato = null;
        $this->codigo_contrato = '';
        $this->semana_inicio = '';
        $this->data_venda = '';
        $this->data_evento = '';
        $this->artista_id = '';
        $this->booker_id = '';
        $this->valor_bruto = '';
        $this->moeda = 'BRL';
        $this->comissao_valor = '';
        $this->local_evento = '';
        $this->nome_evento = '';
        $this->cidade_evento = '';
        $this->estado_evento = '';
        $this->regiao_evento = '';
        $this->pais_evento = 'Brasil';
        $this->agencia_sigla = '';
        $this->status_booking = 'aberto';
        $this->assinatura_status = 'pendente';
    }

    private function notify(string $message, string $type = 'success'): void
    {
        $this->dispatch('notify', ['message' => $message, 'type' => $type]);
    }

    public function render()
    {
        return view('livewire.contratos.index');
    }
}