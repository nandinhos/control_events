<?php

namespace App\Livewire\Hub;

use App\Models\Entidade;
use App\Models\Contrato;
use App\Models\ContasAReceber;
use App\Services\CambioService;
use Livewire\Component;
use Livewire\Attributes\Computed;

class ArtistaIndex extends Component
{
    public ?int $artistaId = null;
    public ?int $filterAno = null;
    public ?int $filterMes = null;

    public function mount(): void
    {
        $this->filterAno = (int) date('Y');
        $this->filterMes = null;
    }

    #[Computed]
    public function artistas()
    {
        return Entidade::query()
            ->orderBy('razao_social')
            ->get(['id', 'razao_social', 'cnpj_cpf']);
    }

    #[Computed]
    public function artistaSelecionado()
    {
        if (!$this->artistaId) return null;
        return Entidade::with(['contratos' => fn($q) => $q->whereYear('data_evento', $this->filterAno ?? now()->year)])->find($this->artistaId);
    }

    #[Computed]
    public function contratosDoArtista()
    {
        if (!$this->artistaId) return collect();
        $query = Contrato::where('artista_id', $this->artistaId);
        if ($this->filterAno) $query->whereYear('data_evento', $this->filterAno);
        return $query->with(['booker:id,razao_social'])->orderByDesc('data_evento')->get();
    }

    #[Computed]
    public function lancamentosDoArtista()
    {
        if (!$this->artistaId) return collect();
        $query = ContasAReceber::where('artista_id', $this->artistaId);
        if ($this->filterAno) $query->whereYear('data_evento', $this->filterAno);
        if ($this->filterMes) $query->whereMonth('data_evento', $this->filterMes);
        return $query->with(['booker:id,razao_social', 'contratante:id,razao_social'])->orderByDesc('vencimento_atual')->get();
    }

    #[Computed]
    public function resumoFinanceiro(): array
    {
        $lancamentos = $this->lancamentosDoArtista;
        return [
            'total_previsto' => $lancamentos->sum('valor_previsto'),
            'total_recebido' => $lancamentos->sum('valor_recebido'),
            'total_aberto' => $lancamentos->sum(fn($l) => (float) $l->valor_previsto - (float) $l->valor_recebido),
            'total_juros' => $lancamentos->sum('juros_multas'),
            'quantidade_parcelas' => $lancamentos->count(),
            'vencidos' => $lancamentos->filter(fn($l) => $l->isVencido)->count(),
            'quitados' => $lancamentos->filter(fn($l) => $l->isQuitado)->count(),
            'abertos' => $lancamentos->filter(fn($l) => $l->isAberto)->count(),
        ];
    }

    #[Computed]
    public function anosDisponiveis(): array
    {
        $current = (int) date('Y');
        return array_map(fn($y) => $y, range($current - 5, $current));
    }

    public function render()
    {
        return view('hub-artista');
    }
}