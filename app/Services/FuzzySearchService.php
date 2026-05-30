<?php

namespace App\Services;

use App\Models\ContasAReceber;
use App\Models\ContasAPagar;
use Illuminate\Support\Facades\DB;

/**
 * Fuzzy Search Engine para Reconciliation (REC-001)
 *
 * Suporta busca por:
 * - Nome do artista
 * - Nome do evento
 * - Nome do contratante
 * - Código do contrato
 * - Descrições parciais
 */
class FuzzySearchService
{
    /**
     * Busca fuzzy em ContasAReceber
     * Retorna matches rankeados por score de similaridade
     */
    public function searchReceivable(string $query, ?int $limit = 10): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        $terms = $this->tokenize($query);

        return ContasAReceber::query()
            ->with(['artista:id,razao_social', 'contrato:id,codigo_contrato', 'contratante:id,razao_social'])
            ->get()
            ->map(function ($item) use ($terms) {
                $item->searchScore = $this->calculateScore($item, $terms);
                return $item;
            })
            ->filter(fn($item) => $item->searchScore > 0)
            ->sortByDesc('searchScore')
            ->take($limit)
            ->values()
            ->toArray();
    }

    /**
     * Busca fuzzy em ContasAPagar
     */
    public function searchPayable(string $query, ?int $limit = 10): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        $terms = $this->tokenize($query);

        return ContasAPagar::query()
            ->with(['contraparte:id,razao_social', 'contratoRef:id,codigo_contrato'])
            ->get()
            ->map(function ($item) use ($terms) {
                $item->searchScore = $this->calculateScorePayable($item, $terms);
                return $item;
            })
            ->filter(fn($item) => $item->searchScore > 0)
            ->sortByDesc('searchScore')
            ->take($limit)
            ->values()
            ->toArray();
    }

    /**
     * Tokeniza query em termos para busca
     */
    protected function tokenize(string $query): array
    {
        // Remove acentos, coloca lowercase
        $normalized = $this->normalize($query);

        // Separa em palavras
        $words = preg_split('/\s+/', $normalized);
        $words = array_filter($words, fn($w) => strlen($w) >= 2);

        return $words;
    }

    /**
     * Normaliza texto (remove acentos, lowercase)
     */
    protected function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));
        // Remove acentos
        $text = preg_replace('/[áàãäâ]/u', 'a', $text);
        $text = preg_replace('/[éèêë]/u', 'e', $text);
        $text = preg_replace('/[íìïî]/u', 'i', $text);
        $text = preg_replace('/[óòõôö]/u', 'o', $text);
        $text = preg_replace('/[úùüû]/u', 'u', $text);
        $text = preg_replace('/[ç]/u', 'c', $text);
        $text = preg_replace('/[ñ]/u', 'n', $text);
        return $text;
    }

    /**
     * Calcula score de similaridade para ContasAReceber
     */
    protected function calculateScore(ContasAReceber $item, array $terms): float
    {
        $score = 0.0;

        // Concatena campos para busca
        $searchable = implode(' ', [
            $item->artista?->razao_social ?? '',
            $item->contrato?->codigo_contrato ?? '',
            $item->contratante?->razao_social ?? '',
            $item->descricao ?? '',
            $item->registro_contabil ?? '',
        ]);
        $searchable = $this->normalize($searchable);

        foreach ($terms as $term) {
            // Score exato (presença da palavra)
            if (str_contains($searchable, $term)) {
                $score += 1.0;

                // Bônus se match no início
                if (str_starts_with($searchable, $term)) {
                    $score += 0.5;
                }
            }

            // Fuzzy match (levenshtein)
            similar_text($term, $searchable, $percent);
            if ($percent > 60) {
                $score += $percent / 100;
            }
        }

        // Bônus: match exato de código de contrato
        $codigo = $this->normalize($item->contrato?->codigo_contrato ?? '');
        foreach ($terms as $term) {
            if (str_contains($codigo, $term)) {
                $score += 2.0; // Bônus alto para código
            }
        }

        return $score;
    }

    /**
     * Calcula score para ContasAPagar
     */
    protected function calculateScorePayable(ContasAPagar $item, array $terms): float
    {
        $score = 0.0;

        $searchable = implode(' ', [
            $item->contraparte?->razao_social ?? '',
            $item->contratoRef?->codigo_contrato ?? '',
            $item->descricao ?? '',
            $item->conta_origem ?? '',
        ]);
        $searchable = $this->normalize($searchable);

        foreach ($terms as $term) {
            if (str_contains($searchable, $term)) {
                $score += 1.0;
            }

            similar_text($term, $searchable, $percent);
            if ($percent > 60) {
                $score += $percent / 100;
            }
        }

        return $score;
    }
}
