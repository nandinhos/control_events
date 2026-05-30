<?php

namespace App\Livewire\Contrato;

use App\Models\Contrato;
use App\Models\ContasAReceber;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    public Contrato $contrato;

    public function mount(Contrato $contrato): void
    {
        $this->contrato->load(['artista', 'booker']);
    }

    #[Computed]
    public function parcelas()
    {
        return $this->contrato->contasAReceber()
            ->orderBy('vencimento_atual')
            ->get();
    }

    #[Computed]
    public function totalReceber(): float
    {
        return $this->parcelas->sum('valor_previsto');
    }

    #[Computed]
    public function totalRecebido(): float
    {
        return $this->parcelas->sum('valor_recebido');
    }

    #[Computed]
    public function totalVendido(): float
    {
        return (float) $this->contrato->valor_bruto;
    }

    public function getAguardandoFechamentoAttribute(): bool
    {
        return $this->contrato->status_booking !== 'confirmado';
    }

    public function render()
    {
        return view('livewire.contratos.show');
    }
}