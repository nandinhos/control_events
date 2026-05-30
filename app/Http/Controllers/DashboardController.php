<?php

namespace App\Http\Controllers;

use App\Models\ContasAReceber;
use App\Models\ContasAPagar;
use App\Models\Contrato;
use App\Services\CambioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class DashboardController
{
    public function __invoke(Request $request)
    {
        $ano = (int) date('Y');
        $mes = (int) date('m');

        $kpis = $this->computeKpis($ano, $mes);
        $evolucaoDRE = $this->computeEvolucaoDRE($ano, $mes);
        $anosDisponiveis = range($ano - 3, $ano + 1);
        $eventosMes = $this->computeEventosMes($ano, $mes);
        $taxasCambio = $this->computeTaxasCambio();
        $mesAtual = $mes;

        return View::make('dashboard', [
            'kpis' => $kpis,
            'evolucaoDRE' => $evolucaoDRE,
            'anosDisponiveis' => $anosDisponiveis,
            'eventosMes' => $eventosMes,
            'taxasCambio' => $taxasCambio,
            'mesAtual' => $mesAtual,
        ]);
    }

    private function computeKpis(int $ano, ?int $mes): array
    {
        $mes = $mes ?? (int) date('m');
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

    private function computeEvolucaoDRE(int $ano, ?int $mes): array
    {
        $months = [];
        $currentMonth = $mes ?? (int) date('m');
        $current = strtotime("{$ano}-{$currentMonth}-01");

        for ($i = 5; $i >= 0; $i--) {
            $d = strtotime("-{$i} month", $current);
            $y = (int) date('Y', $d);
            $m = (int) date('m', $d);

            $start = sprintf('%04d-%02d-01', $y, $m);
            $end = date('Y-m-t', strtotime($start));

            $receita = (int) ContasAReceber::query()
                ->vencimentosEntre($start, $end)
                ->sum('valor_previsto');

            $despesa = (int) ContasAPagar::query()
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

    private function computeEventosMes(int $ano, ?int $mes): array
    {
        $mes = $mes ?? (int) date('m');
        return Contrato::whereYear('data_evento', $ano)
            ->whereMonth('data_evento', $mes)
            ->with(['artista:id,razao_social', 'booker:id,razao_social'])
            ->orderBy('data_evento')
            ->get(['id', 'codigo_contrato', 'nome_evento', 'data_evento', 'artista_id', 'booker_id', 'status_booking'])
            ->toArray();
    }

    private function computeTaxasCambio(): array
    {
        $svc = new CambioService();
        return [
            'USD_BRL' => $svc->getRate('USD', 'BRL'),
            'EUR_BRL' => $svc->getRate('EUR', 'BRL'),
            'USD_EUR' => $svc->getRate('USD', 'EUR'),
        ];
    }
}
