<?php

namespace App\Livewire\International;

use App\Models\ContasAReceber;
use App\Models\Entidade;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $activeTab = 'importacao'; // 'importacao' | 'exportacao'
    public string $filterStatusCambio = '';
    public string $search = '';

    #[Computed]
    public function registrosImportacao()
    {
        // INT-006: Registros com moeda estrangeira (USD/EUR/GBP)
        return ContasAReceber::query()
            ->when($this->search, fn($q) => $q->where('nome_evento', 'like', "%{$this->search}%"))
            ->when($this->filterStatusCambio === 'aguardando', fn($q) => $q->aguardandoCambio())
            ->when($this->filterStatusCambio === 'convertido', fn($q) => $q->where('status_pagamento', '!=', 'aguardando_cambio'))
            ->where(function ($q) {
                $q->where('valor_usd', '>', 0)
                    ->orWhere('valor_eur', '>', 0)
                    ->orWhere('valor_gbp', '>', 0);
            })
            ->with(['artista:id,razao_social', 'contratante:id,razao_social'])
            ->orderByDesc('vencimento_atual')
            ->paginate(15);
    }

    #[Computed]
    public function registrosExportacao()
    {
        // INT-006: Registros origem Brasil (BRL) para controle de remessas
        return ContasAReceber::query()
            ->when($this->search, fn($q) => $q->where('nome_evento', 'like', "%{$this->search}%"))
            ->where('moeda_original', 'BRL')
            ->where(function ($q) {
                $q->where('valor_usd', '>', 0)
                    ->orWhere('valor_eur', '>', 0)
                    ->orWhere('valor_gbp', '>', 0);
            })
            ->orWhere('registro_contabil', 'Artista') // Artistas com receita em BRL
            ->with(['artista:id,razao_social', 'contratante:id,razao_social'])
            ->orderByDesc('vencimento_atual')
            ->paginate(15);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterStatusCambio = '';
    }

    public function render()
    {
        return view('livewire.international.index');
    }
}
