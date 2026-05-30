<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\ContasAReceber;

/**
 * Serviço de Validação de Provisionamento (RN-001)
 *
 * Regra: A soma das parcelas tipo 'booking' deve ser IGUAL ao valor_bruto do contrato
 * Lançamentos tipo 'extra' NÃO entram na validação
 */
class ProvisionamentoValidationService
{
    /**
     * Resultado da validação
     */
    public function __construct(
        public readonly bool $isValid,
        public readonly float $somaBooking,
        public readonly float $valorBruto,
        public readonly float $diferenca,
        public readonly string $mensagem,
        public readonly ?string $codigoErro = null,
    ) {}

    /**
     * Cria resultado de sucesso
     */
    public static function success(float $somaBooking, float $valorBruto): self
    {
        return new self(
            isValid: true,
            somaBooking: $somaBooking,
            valorBruto: $valorBruto,
            diferenca: 0.0,
            mensagem: "Provisionamento completo. Soma booking (R$ " . number_format($somaBooking, 2, ',', '.') . ") = valor bruto (R$ " . number_format($valorBruto, 2, ',', '.') . ").",
        );
    }

    /**
     * Cria resultado de falha
     */
    public static function failure(float $somaBooking, float $valorBruto, float $diferenca, string $motivo): self
    {
        return new self(
            isValid: false,
            somaBooking: $somaBooking,
            valorBruto: $valorBruto,
            diferenca: $diferenca,
            mensagem: $motivo,
            codigoErro: $diferenca > 0 ? 'INCOMPLETO' : 'EXCEDENTE',
        );
    }

    /**
     * Valida se o provisionamento está completo para um contrato
     */
    public static function validate(Contrato $contrato): self
    {
        $valorBruto = (float) $contrato->valor_bruto;

        // Carregar contas a receber com tipo de lançamento
        $somaBooking = (float) $contrato->contasAReceber()
            ->where('tipo_lancamento', 'booking')
            ->sum('valor_previsto');

        $diferenca = abs($valorBruto - $somaBooking);

        // Tolerância de 1 centavo para problemas de floating point
        if ($diferenca < 0.01) {
            return self::success($somaBooking, $valorBruto);
        }

        if ($somaBooking < $valorBruto) {
            $faltante = $valorBruto - $somaBooking;
            return self::failure(
                somaBooking: $somaBooking,
                valorBruto: $valorBruto,
                diferenca: $faltante,
                motivo: "Provisionamento incompleto. Faltam R$ " . number_format($faltante, 2, ',', '.') . " (soma booking: R$ " . number_format($somaBooking, 2, ',', '.') . " / valor bruto: R$ " . number_format($valorBruto, 2, ',', '.') . ")."
            );
        }

        // Soma maior que valor bruto
        return self::failure(
            somaBooking: $somaBooking,
            valorBruto: $valorBruto,
            diferenca: $diferenca,
            motivo: "Provisionamento excedente. Soma booking (R$ " . number_format($somaBooking, 2, ',', '.') . ") excede valor bruto (R$ " . number_format($valorBruto, 2, ',', '.') . ") em R$ " . number_format($diferenca, 2, ',', '.') . "."
        );
    }

    /**
     * Retorna soma dos extras (não entra na validação)
     */
    public static function getSomaExtras(Contrato $contrato): float
    {
        return (float) $contrato->contasAReceber()
            ->where('tipo_lancamento', 'extra')
            ->sum('valor_previsto');
    }

    /**
     * Retorna soma total (booking + extra)
     */
    public static function getSomaTotal(Contrato $contrato): float
    {
        return (float) $contrato->contasAReceber()->sum('valor_previsto');
    }

    /**
     * Retorna percentual de provisionamento (0 a 100+)
     */
    public static function getPercentual(Contrato $contrato): float
    {
        $valorBruto = (float) $contrato->valor_bruto;
        if ($valorBruto <= 0) {
            return 0.0;
        }

        $somaBooking = (float) $contrato->contasAReceber()
            ->where('tipo_lancamento', 'booking')
            ->sum('valor_previsto');

        return ($somaBooking / $valorBruto) * 100;
    }
}
