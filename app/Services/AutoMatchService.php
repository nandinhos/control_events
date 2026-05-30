<?php

namespace App\Services;

use App\Models\ExtratoBancarioTransacao;
use App\Models\ContasAReceber;
use App\Models\ContasAPagar;
use Illuminate\Support\Collection;

/**
 * Auto-Match Service para Reconciliation (REC-002)
 *
 * Scoring:
 * - Exact amount match: peso ALTO (10.0)
 * - Date proximity: peso MÉDIO (5.0)
 * - Description similarity: peso BAIXO (3.0)
 * - Fuzzy name match: peso MÉDIO (4.0)
 *
 * Retorna top 5 matches rankeados por score总分
 */
class AutoMatchService
{
    public function __construct(
        protected FuzzySearchService $fuzzySearch = new FuzzySearchService(),
    ) {}

    /**
     * Encontra matches para uma transação bancária
     * Retorna array de matches com score detalhado
     */
    public function findMatches(
        ExtratoBancarioTransacao $transacao,
        ?string $tipo = null, // 'receivable' ou 'payable'
        int $limit = 5
    ): array {
        $absValor = abs((float) $transacao->valor);
        $transacaoData = $transacao->data_transacao;
        $transacaoDesc = $this->normalize($transacao->descricao ?? '');

        $matches = [];

        // Busca em Receivable ou ambos
        if ($tipo === null || $tipo === 'receivable') {
            $receivableMatches = $this->searchReceivableMatches(
                $transacao,
                $absValor,
                $transacaoData,
                $transacaoDesc
            );
            $matches = array_merge($matches, $receivableMatches);
        }

        // Busca em Payable ou ambos
        if ($tipo === null || $tipo === 'payable') {
            $payableMatches = $this->searchPayableMatches(
                $transacao,
                $absValor,
                $transacaoData,
                $transacaoDesc
            );
            $matches = array_merge($matches, $payableMatches);
        }

        // Ordena por score总分
        usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($matches, 0, $limit);
    }

    /**
     * Busca matches em ContasAReceber
     */
    protected function searchReceivableMatches(
        ExtratoBancarioTransacao $transacao,
        float $absValor,
        $transacaoData,
        string $transacaoDesc
    ): array {
        $matches = [];

        // Primeiro: busca fuzzy por descrição
        $fuzzyResults = $this->fuzzySearch->searchReceivable($transacao->descricao ?? '', 20);

        foreach ($fuzzyResults as $item) {
            $item = (object) $item;
            $score = 0.0;
            $breakdown = [];

            // SCORE: Amount match (peso ALTO = 10)
            $amountDiff = abs((float) $item->valor_previsto - $absValor);
            if ($amountDiff < 0.01) {
                $score += 10.0;
                $breakdown['amount'] = ['score' => 10.0, 'match' => 'exact'];
            } elseif ($amountDiff < $absValor * 0.05) { // 5% tolerância
                $score += 5.0;
                $breakdown['amount'] = ['score' => 5.0, 'match' => 'close', 'diff' => $amountDiff];
            }

            // SCORE: Date proximity (peso MÉDIO = 5)
            $dateDiff = $this->dateDiffDays($item->vencimento_atual, $transacaoData);
            if ($dateDiff === 0) {
                $score += 5.0;
                $breakdown['date'] = ['score' => 5.0, 'match' => 'exact'];
            } elseif ($dateDiff <= 3) {
                $score += 3.0;
                $breakdown['date'] = ['score' => 3.0, 'match' => 'close', 'days' => $dateDiff];
            } elseif ($dateDiff <= 7) {
                $score += 1.0;
                $breakdown['date'] = ['score' => 1.0, 'match' => 'week', 'days' => $dateDiff];
            }

            // SCORE: Fuzzy description match (peso BAIXO = 3)
            $descScore = $this->calculateDescScore($transacaoDesc, $item->searchScore ?? 0);
            $score += $descScore;
            if ($descScore > 0) {
                $breakdown['description'] = ['score' => $descScore];
            }

            if ($score > 0) {
                $matches[] = [
                    'type' => 'receivable',
                    'id' => $item->id,
                    'lancamento' => $item,
                    'score' => $score,
                    'breakdown' => $breakdown,
                ];
            }
        }

        // Segundo: busca por amount exato (mesmo sem fuzzy match)
        $exactMatches = ContasAReceber::query()
            ->whereDoesntHave('conciliacaoLinks')
            ->whereRaw('ABS(valor_previsto - ?)', [$absValor])
            ->whereDate('vencimento_atual', '>=', now()->subDays(7))
            ->whereDate('vencimento_atual', '<=', now()->addDays(7))
            ->with(['artista:id,razao_social', 'contrato:id,codigo_contrato'])
            ->limit(10)
            ->get();

        foreach ($exactMatches as $item) {
            // Pula se já está nos matches com score alto
            if (collect($matches)->contains(fn($m) => $m['type'] === 'receivable' && $m['id'] === $item->id && $m['score'] > 8)) {
                continue;
            }

            $score = 10.0; // Amount match
            $dateDiff = $this->dateDiffDays($item->vencimento_atual, $transacaoData);

            if ($dateDiff === 0) {
                $score += 5.0;
            } elseif ($dateDiff <= 3) {
                $score += 3.0;
            }

            $matches[] = [
                'type' => 'receivable',
                'id' => $item->id,
                'lancamento' => $item,
                'score' => $score,
                'breakdown' => ['amount' => ['score' => 10.0, 'match' => 'exact']],
            ];
        }

        return $matches;
    }

    /**
     * Busca matches em ContasAPagar (mesma lógica)
     */
    protected function searchPayableMatches(
        ExtratoBancarioTransacao $transacao,
        float $absValor,
        $transacaoData,
        string $transacaoDesc
    ): array {
        $matches = [];

        $fuzzyResults = $this->fuzzySearch->searchPayable($transacao->descricao ?? '', 20);

        foreach ($fuzzyResults as $item) {
            $item = (object) $item;
            $score = 0.0;
            $breakdown = [];

            $amountDiff = abs((float) $item->valor_devido - $absValor);
            if ($amountDiff < 0.01) {
                $score += 10.0;
                $breakdown['amount'] = ['score' => 10.0, 'match' => 'exact'];
            } elseif ($amountDiff < $absValor * 0.05) {
                $score += 5.0;
                $breakdown['amount'] = ['score' => 5.0, 'match' => 'close', 'diff' => $amountDiff];
            }

            $dateDiff = $this->dateDiffDays($item->data_devida, $transacaoData);
            if ($dateDiff === 0) {
                $score += 5.0;
                $breakdown['date'] = ['score' => 5.0, 'match' => 'exact'];
            } elseif ($dateDiff <= 3) {
                $score += 3.0;
                $breakdown['date'] = ['score' => 3.0, 'match' => 'close', 'days' => $dateDiff];
            }

            $descScore = $this->calculateDescScore($transacaoDesc, $item->searchScore ?? 0);
            $score += $descScore;
            if ($descScore > 0) {
                $breakdown['description'] = ['score' => $descScore];
            }

            if ($score > 0) {
                $matches[] = [
                    'type' => 'payable',
                    'id' => $item->id,
                    'lancamento' => $item,
                    'score' => $score,
                    'breakdown' => $breakdown,
                ];
            }
        }

        return $matches;
    }

    /**
     * Score de descrição baseado no fuzzy search score
     */
    protected function calculateDescScore(string $query, float $fuzzyScore): float
    {
        if ($fuzzyScore >= 3.0) {
            return 3.0; // peso BAIXO para description
        }
        if ($fuzzyScore >= 1.5) {
            return 2.0;
        }
        if ($fuzzyScore >= 0.5) {
            return 1.0;
        }
        return 0;
    }

    /**
     * Normaliza texto para comparação
     */
    protected function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[áàãäâéèêëíìïîóòõôöúùüûç]/u', '', $text);
        return $text;
    }

    /**
     * Diferença em dias entre duas datas
     */
    protected function dateDiffDays($date1, $date2): int
    {
        if (!$date1 || !$date2) {
            return 999;
        }
        $d1 = $date1 instanceof \Carbon\Carbon ? $date1 : \Carbon\Carbon::parse($date1);
        $d2 = $date2 instanceof \Carbon\Carbon ? $date2 : \Carbon\Carbon::parse($date2);
        return (int) abs($d1->diffInDays($d2));
    }
}
