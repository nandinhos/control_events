<?php

namespace App\Services;

use App\Models\ContasAReceber;
use App\Models\ContasAReceberSplit;

/**
 * Serviço de Validação de Splits de Recebíveis (RF-004)
 *
 * Regra: Soma dos splits deve ser IGUAL ao valor da parcela pai
 * Destinos possíveis: Panorama, Coral, Artista (conforme PRD/RN-004)
 */
class SplitsValidationService
{
    /**
     * Destinos de split (RN-004, AR-04)
     */
    public const DESTINO_PANORAMA = 'Panorama';
    public const DESTINO_CORAL = 'Coral';
    public const DESTINO_ARTISTA = 'Artista';

    public const DESTINOS_VALIDOS = [
        self::DESTINO_PANORAMA,
        self::DESTINO_CORAL,
        self::DESTINO_ARTISTA,
    ];

    /**
     * Resultado da validação
     */
    public function __construct(
        public readonly bool $isValid,
        public readonly float $somaSplits,
        public readonly float $valorParcela,
        public readonly float $diferenca,
        public readonly string $mensagem,
        public readonly ?string $codigoErro = null,
    ) {}

    /**
     * Cria resultado de sucesso
     */
    public static function success(float $somaSplits, float $valorParcela): self
    {
        return new self(
            isValid: true,
            somaSplits: $somaSplits,
            valorParcela: $valorParcela,
            diferenca: 0.0,
            mensagem: "Splits válidos. Soma (R$ " . number_format($somaSplits, 2, ',', '.') . ") = valor da parcela (R$ " . number_format($valorParcela, 2, ',', '.') . ").",
        );
    }

    /**
     * Cria resultado de falha
     */
    public static function failure(float $somaSplits, float $valorParcela, float $diferenca, string $motivo): self
    {
        return new self(
            isValid: false,
            somaSplits: $somaSplits,
            valorParcela: $valorParcela,
            diferenca: $diferenca,
            mensagem: $motivo,
            codigoErro: $diferenca > 0 ? 'SPLITS_INCOMPLETOS' : 'SPLITS_EXCEDENTES',
        );
    }

    /**
     * Valida se os splits de um lançamento estão corretos
     */
    public static function validate(ContasAReceber $lancamento): self
    {
        $valorParcela = (float) $lancamento->valor_previsto;
        $somaSplits = self::getSomaSplits($lancamento);
        $diferenca = abs($valorParcela - $somaSplits);

        // Tolerância de 1 centavo
        if ($diferenca < 0.01) {
            return self::success($somaSplits, $valorParcela);
        }

        if ($somaSplits < $valorParcela) {
            $faltante = $valorParcela - $somaSplits;
            return self::failure(
                somaSplits: $somaSplits,
                valorParcela: $valorParcela,
                diferenca: $faltante,
                motivo: "Splits incompletos. Faltam R$ " . number_format($faltante, 2, ',', '.') . " para completar o valor da parcela (R$ " . number_format($valorParcela, 2, ',', '.') . ")."
            );
        }

        $excedente = $somaSplits - $valorParcela;
        return self::failure(
            somaSplits: $somaSplits,
            valorParcela: $valorParcela,
            diferenca: $excedente,
            motivo: "Splits excedentes. Soma (R$ " . number_format($somaSplits, 2, ',', '.') . ") excede valor da parcela (R$ " . number_format($valorParcela, 2, ',', '.') . ") em R$ " . number_format($excedente, 2, ',', '.') . "."
        );
    }

    /**
     * Retorna soma dos splits de um lançamento
     */
    public static function getSomaSplits(ContasAReceber $lancamento): float
    {
        if (!$lancamento->relationLoaded('splits')) {
            $lancamento->load('splits');
        }

        return (float) $lancamento->splits->sum('valor_absoluto');
    }

    /**
     * Retorna splits agrupados por destino
     */
    public static function getSplitsPorDestino(ContasAReceber $lancamento): array
    {
        if (!$lancamento->relationLoaded('splits')) {
            $lancamento->load('splits');
        }

        return $lancamento->splits
            ->groupBy('tipo_destinatario')
            ->map(fn($splits) => $splits->sum('valor_absoluto'))
            ->toArray();
    }

    /**
     * Retorna percentual de cada destino
     */
    public static function getPercentualPorDestino(ContasAReceber $lancamento): array
    {
        $valorParcela = (float) $lancamento->valor_previsto;
        if ($valorParcela <= 0) {
            return [];
        }

        $porDestino = self::getSplitsPorDestino($lancamento);
        $result = [];

        foreach ($porDestino as $destino => $valor) {
            $result[$destino] = [
                'valor' => $valor,
                'percentual' => ($valor / $valorParcela) * 100,
            ];
        }

        return $result;
    }

    /**
     * Verifica se destino é válido
     */
    public static function isDestinoValido(string $destino): bool
    {
        return in_array($destino, self::DESTINOS_VALIDOS);
    }
}
