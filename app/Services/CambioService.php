<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\ContasAReceber;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class CambioService
{
    private const CACHE_TTL_SECONDS = 300; // 5 minutes

    /**
     * Get exchange rate for a currency pair
     * Supports: USD-BRL, EUR-BRL, EUR-USD
     */
    public function getRate(string $from, string $to): ?float
    {
        if ($from === $to) return 1.0;

        $cacheKey = "cambio_rate_{$from}_{$to}";

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($from, $to) {
            // Try BCB if BRL involved (public, no API key needed)
            if ($from === 'USD' && $to === 'BRL') {
                return $this->fetchBcbUsdRate();
            }
            if ($from === 'EUR' && $to === 'BRL') {
                $eurRate = $this->fetchBcbEurRate();
                if ($eurRate) return $eurRate;
            }
            if ($from === 'BRL' && $to === 'USD') {
                $usd = $this->fetchBcbUsdRate();
                return $usd ? (1 / $usd) : null;
            }
            if ($from === 'BRL' && $to === 'EUR') {
                $eur = $this->fetchBcbEurRate();
                return $eur ? (1 / $eur) : null;
            }
            if ($from === 'EUR' && $to === 'USD') {
                $eurBrl = $this->fetchBcbEurRate();
                $usdBrl = $this->fetchBcbUsdRate();
                if ($eurBrl && $usdBrl) return $usdBrl / $eurBrl;
            }
            if ($from === 'USD' && $to === 'EUR') {
                $eurBrl = $this->fetchBcbEurRate();
                $usdBrl = $this->fetchBcbUsdRate();
                if ($eurBrl && $usdBrl) return $eurBrl / $usdBrl;
            }

            return null;
        });
    }

    /**
     * Convert amount from one currency to another
     */
    public function convert(float $amount, string $from, string $to): ?float
    {
        $rate = $this->getRate($from, $to);
        return $rate !== null ? $amount * $rate : null;
    }

    /**
     * Convert to BRL (base currency)
     */
    public function toBrl(float $amount, string $from): ?float
    {
        return $this->convert($amount, $from, 'BRL');
    }

    /**
     * Get DRE summary for a period with multi-currency conversion
     */
    public function getDreSummary(int $ano, int $mes, string $baseCurrency = 'BRL'): array
    {
        $periodStart = sprintf('%04d-%02d-01', $ano, $mes);
        $periodEnd = date('Y-m-t', strtotime($periodStart));

        $receber = ContasAReceber::query()
            ->vencimentosEntre($periodStart, $periodEnd)
            ->get();

        $totalReceita = 0;
        $totalRecebido = 0;
        $byMoeda = [];

        foreach ($receber as $r) {
            $valor = (float) $r->valor_previsto;
            $recebido = (float) $r->valor_recebido;
            $moeda = $r->contrato?->moeda ?? 'BRL';

            $valorConvertido = $this->convert($valor, $moeda, $baseCurrency) ?? $valor;
            $recebidoConvertido = $this->convert($recebido, $moeda, $baseCurrency) ?? $recebido;

            $totalReceita += $valorConvertido;
            $totalRecebido += $recebidoConvertido;

            if (!isset($byMoeda[$moeda])) {
                $byMoeda[$moeda] = ['total' => 0, 'recebido' => 0, 'aberto' => 0];
            }
            $byMoeda[$moeda]['total'] += $valor;
            $byMoeda[$moeda]['recebido'] += $recebido;
            $byMoeda[$moeda]['aberto'] += ($valor - $recebido);
        }

        // Also get payables for the period
        $pagar = \App\Models\ContasAPagar::query()
            ->where('ano_vencimento', $ano)
            ->where('mes_vencimento', $mes)
            ->get();

        $totalDespesa = 0;
        foreach ($pagar as $p) {
            $totalDespesa += (float) $p->valor_devido;
        }

        // International contracts summary
        $internacionais = Contrato::query()
            ->whereYear('data_evento', $ano)
            ->whereMonth('data_evento', $mes)
            ->where('moeda', '!=', 'BRL')
            ->get();

        $totalInternacional = 0;
        $byMoedaInt = [];
        foreach ($internacionais as $c) {
            $converted = $this->convert((float) $c->valor_bruto, $c->moeda, $baseCurrency) ?? (float) $c->valor_bruto;
            $totalInternacional += $converted;
            if (!isset($byMoedaInt[$c->moeda])) {
                $byMoedaInt[$c->moeda] = 0;
            }
            $byMoedaInt[$c->moeda] += (float) $c->valor_bruto;
        }

        return [
            'periodo' => sprintf('%04d-%02d', $ano, $mes),
            'base_currency' => $baseCurrency,
            'total_receita' => $totalReceita,
            'total_recebido' => $totalRecebido,
            'total_despesa' => $totalDespesa,
            'resultado' => $totalReceita - $totalDespesa,
            'total_internacional' => $totalInternacional,
            'by_moeda' => $byMoeda,
            'by_moeda_internacional' => $byMoedaInt,
            'exchange_rates' => [
                'USD_BRL' => $this->getRate('USD', 'BRL'),
                'EUR_BRL' => $this->getRate('EUR', 'BRL'),
                'USD_EUR' => $this->getRate('USD', 'EUR'),
            ],
            'cache_ttl' => self::CACHE_TTL_SECONDS,
        ];
    }

    /**
     * Force refresh exchange rates cache
     */
    public function refreshRates(): void
    {
        Cache::forget('cambio_rate_USD_BRL');
        Cache::forget('cambio_rate_EUR_BRL');
        $this->getRate('USD', 'BRL');
        $this->getRate('EUR', 'BRL');
    }

    // =====================================================
    // PRIVATE
    // =====================================================

    private function fetchBcbUsdRate(): ?float
    {
        try {
            // BCB PTAX endpoint (public)
            $response = Http::timeout(5)->get('https://api.bcb.gov.br/dados/serie/bcbdata.serie.estatisticas/dados/taxas-cartorio.csv');
            if ($response->ok()) {
                $lines = explode("\n", trim($response->body()));
                foreach (array_reverse($lines) as $line) {
                    if (strpos($line, 'USD') !== false || strpos($line, 'Dólar') !== false) {
                        $parts = str_getcsv($line, ';');
                        if (count($parts) >= 2 && is_numeric(str_replace(',', '.', end($parts)))) {
                            return (float) str_replace(',', '.', end($parts));
                        }
                    }
                }
            }
        } catch (\Throwable) {
            // fallback
        }

        // Fallback: PTAX simplified
        try {
            $xml = Http::timeout(5)->get('https://www3.bcb.gov.br/pub/deshtml/fx8.htm');
            if ($xml->ok()) {
                if (preg_match('/USD\s+[\d,]+/', $xml->body(), $m)) {
                    $num = preg_replace('/[^\d,]/', '', $m[0]);
                    return (float) str_replace(',', '.', $num);
                }
            }
        } catch (\Throwable) {
            // fallback
        }

        return null;
    }

    private function fetchBcbEurRate(): ?float
    {
        try {
            $xml = Http::timeout(5)->get('https://www3.bcb.gov.br/pub/deshtml/fx8.htm');
            if ($xml->ok()) {
                if (preg_match('/EUR\s+[\d,]+/', $xml->body(), $m)) {
                    $num = preg_replace('/[^\d,]/', '', $m[0]);
                    return (float) str_replace(',', '.', $num);
                }
            }
        } catch (\Throwable) {
            return null;
        }
        return null;
    }
}