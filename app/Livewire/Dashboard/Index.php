<?php

namespace App\Livewire\Dashboard;

use App\Models\ContasAReceber;
use App\Models\ContasAPagar;
use App\Models\Contrato;
use App\Models\ExtratoBancarioTransacao;
use App\Services\CambioService;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Index extends Component
{
    public int $ano = 0;
    public ?int $mes = null;
    private ?CambioService $cambioService = null;

    public function mount(): void
    {
        $this->ano = (int) date('Y');
    }

    private function getCambioService(): CambioService
    {
        if (!$this->cambioService) {
            $this->cambioService = new CambioService();
        }
        return $this->cambioService;
    }

    #[Computed]
    public function kpis(): array
    {
        $ano = $this->ano ?: (int) date('Y');
        $mes = $this->mes ?? (int) date('m');
        $start = sprintf('%04d-%02d-01', $ano, $mes);
        $end = date('Y-m-t', strtotime($start));

        $receber = ContasAReceber::query()
            ->vencimentosEntre($start, $end)
            ->get();

        $pagar = ContasAPagar::query()
            ->where('ano_vencimento', $ano)
            ->where('mes_vencimento', $mes)
            ->get();

        $totalReceita = $receber->sum('valor_previsto');
        $totalRecebido = $receber->sum('valor_recebido');
        $totalDespesa = $pagar->sum('valor_devido');
        $totalPago = $pagar->sum('valor_pago');
        $vencidosReceber = $receber->filter(fn($r) => $r->isVencido)->count();
        $vencidosPagar = $pagar->filter(fn($p) => $p->status_pagamento === 'pendente' && $p->data_devida && $p->data_devida->lt(now()))->count();

        $receitas = [
            'total_previsto' => $totalReceita,
            'total_recebido' => $totalRecebido,
            'aberto' => $totalReceita - $totalRecebido,
            'taxa_recebimento' => $totalReceita > 0 ? round(($totalRecebido / $totalReceita) * 100, 1) : 0,
        ];

        $despesas = [
            'total_devido' => $totalDespesa,
            'total_pago' => $totalPago,
            'aberto' => $totalDespesa - $totalPago,
            'taxa_pagamento' => $totalDespesa > 0 ? round(($totalPago / $totalDespesa) * 100, 1) : 0,
        ];

        $resultado = $totalReceita - $totalDespesa;
        $margem = $totalReceita > 0 ? round(($resultado / $totalReceita) * 100, 1) : 0;

        return [
            'receitas' => $receitas,
            'despesas' => $despesas,
            'resultado' => $resultado,
            'margem' => $margem,
            'vencidos_receber' => $vencidosReceber,
            'vencidos_pagar' => $vencidosPagar,
        ];
    }

    #[Computed]
    public function eventosMes(): array
    {
        $ano = $this->ano ?: (int) date('Y');
        $mes = $this->mes ?? (int) date('m');
        return Contrato::whereYear('data_evento', $ano)
            ->whereMonth('data_evento', $mes)
            ->with(['artista:id,razao_social', 'booker:id,razao_social'])
            ->orderBy('data_evento')
            ->get(['id', 'codigo_contrato', 'nome_evento', 'data_evento', 'artista_id', 'booker_id', 'status_booking'])
            ->toArray();
    }

    #[Computed]
    public function timelineFinanceira(): array
    {
        $ano = $this->ano ?: (int) date('Y');
        $mes = $this->mes ?? (int) date('m');
        $start = sprintf('%04d-%02d-01', $ano, $mes);
        $end = date('Y-m-t', strtotime($start));

        $receber = ContasAReceber::query()
            ->vencimentosEntre($start, $end)
            ->with(['booker:id,razao_social'])
            ->get();

        $ano = $this->ano ?: (int) date('Y');
        $mes = $this->mes ?? (int) date('m');
        $pagar = ContasAPagar::query()
            ->where('ano_vencimento', $ano)
            ->where('mes_vencimento', $mes)
            ->with(['contraparte:id,razao_social'])
            ->get();

        $timeline = [];
        $ano = $this->ano ?: (int) date('Y');

        foreach ($receber as $r) {
            $timeline[] = [
                'date' => $r->vencimento_atual?->format('Y-m-d') ?? $r->data_evento?->format('Y-m-d') ?? "{$ano}-{$mes}-01",
                'type' => 'receber',
                'label' => $r->nome_evento,
                'booker' => $r->booker?->razao_social ?? '',
                'valor' => $r->valor_previsto,
                'status' => $r->status_pagamento,
                'parcela' => $r->parcela_numero,
            ];
        }

        foreach ($pagar as $p) {
            $timeline[] = [
                'date' => $p->data_devida?->format('Y-m-d') ?? "{$ano}-{$mes}-01",
                'type' => 'pagar',
                'label' => $p->descricao ?: $p->conta_origem,
                'booker' => $p->contraparte?->razao_social ?? '',
                'valor' => $p->valor_devido,
                'status' => $p->status_pagamento,
                'parcela' => '',
            ];
        }

        usort($timeline, fn($a, $b) => $a['date'] <=> $b['date']);

        return $timeline;
    }

    #[Computed]
    public function evolucaoDRE(): array
    {
        // Last 6 months DRE
        $months = [];
        $currentMonth = $this->mes ?? (int) date('m');
        $ano = $this->ano ?: (int) date('Y');
        $current = strtotime("{$ano}-{$currentMonth}-01");
        for ($i = 5; $i >= 0; $i--) {
            $d = strtotime("-{$i} month", $current);
            $y = (int) date('Y', $d);
            $m = (int) date('m', $d);

            $start = sprintf('%04d-%02d-01', $y, $m);
            $end = date('Y-m-t', strtotime($start));

            $receita = ContasAReceber::query()
                ->vencimentosEntre($start, $end)
                ->sum('valor_previsto');

            $despesa = ContasAPagar::query()
                ->where('ano_vencimento', $y)
                ->where('mes_vencimento', $m)
                ->sum('valor_devido');

            $months[] = [
                'periodo' => sprintf('%04d-%02d', $y, $m),
                'label' => (new \DateTime("@{$d}"))->format('M y'),
                'receita' => $receita,
                'despesa' => $despesa,
                'resultado' => $receita - $despesa,
            ];
        }

        return $months;
    }

    #[Computed]
    public function taxasCambio(): array
    {
        $svc = $this->getCambioService();
        return [
            'USD_BRL' => $svc->getRate('USD', 'BRL'),
            'EUR_BRL' => $svc->getRate('EUR', 'BRL'),
            'USD_EUR' => $svc->getRate('USD', 'EUR'),
        ];
    }

    #[Computed]
    public function anosDisponiveis(): array
    {
        $current = (int) date('Y');
        return range($current - 3, $current + 1);
    }

    public function render()
    {
        return view('dashboard');
    }
}