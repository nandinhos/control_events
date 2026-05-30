<?php

namespace App\Models\Traits;

use App\Models\Contrato;
use App\Events\ContratoLifecycleEvent;
use Illuminate\Support\Facades\Event;

/**
 * Trait HasContratoLifecycle
 *
 * Implementa máquina de estados para ciclo de vida do contrato:
 * draft -> provisionando -> em_execucao -> concluido
 *
 * @property string $assinatura_status
 */
trait HasContratoLifecycle
{
    /**
     * Estados possíveis do ciclo de vida
     */
    public const ESTADO_DRAFT = 'Draft';
    public const ESTADO_PROVISIONANDO = 'Aguardando Assinatura';
    public const ESTADO_EM_EXECUCAO = 'Em Execucao';
    public const ESTADO_CONCLUIDO = 'Concluido';
    public const ESTADO_CANCELADO = 'Cancelado';

    /**
     * Transições permitidas: [de => [para => guarda]]
     */
    protected static array $transitions = [
        'Draft' => [
            'Aguardando Assinatura' => 'startProvisioning',
        ],
        'Aguardando Assinatura' => [
            'Em Execucao' => 'completeProvisioning',
            'Cancelado' => 'cancel',
        ],
        'Em Execucao' => [
            'Concluido' => 'conclude',
            'Cancelado' => 'cancel',
        ],
    ];

    /**
     * Verifica se pode transitar para o estado target
     */
    public function canTransitionTo(string $targetEstado): bool
    {
        $currentEstado = $this->getEstado();
        $allowedTransitions = self::$transitions[$currentEstado] ?? [];

        if (!isset($allowedTransitions[$targetEstado])) {
            return false;
        }

        $guardMethod = $allowedTransitions[$targetEstado];
        return $this->{$guardMethod}();
    }

    /**
     * Transita para novo estado
     *
     * @throws \InvalidArgumentException se transição não permitida
     */
    public function transitionTo(string $targetEstado): bool
    {
        if (!$this->canTransitionTo($targetEstado)) {
            throw new \InvalidArgumentException(
                "Transição de '{$this->getEstado()}' para '{$targetEstado}' não é permitida."
            );
        }

        $oldEstado = $this->getEstado();
        $this->assinatura_status = $targetEstado;
        $this->save();

        // Disparar callbacks
        $this->executeCallback($targetEstado);

        // Disparar evento
        Event::dispatch(new ContratoLifecycleEvent($this, $oldEstado, $targetEstado));

        return true;
    }

    /**
     * Obtém o estado atual do contrato
     */
    public function getEstado(): string
    {
        return $this->assinatura_status ?? self::ESTADO_DRAFT;
    }

    /**
     * Verifica se está em estado terminal
     */
    public function isTerminal(): bool
    {
        return in_array($this->getEstado(), [
            self::ESTADO_CONCLUIDO,
            self::ESTADO_CANCELADO,
        ]);
    }

    // ============================================
    // GUARDS (Validadores de Transição)
    // ============================================

    /**
     * Guard: verifica se pode iniciar provisionamento
     * Condição: contrato tem campos obrigatórios
     */
    public function startProvisioning(): bool
    {
        return $this->checkContratoValido();
    }

    /**
     * Guard: verifica se pode completar provisionamento (RN-001)
     * Condição: soma das parcelas booking = valor_bruto
     */
    public function completeProvisioning(): bool
    {
        if (!$this->checkContratoValido()) {
            return false;
        }

        return $this->isProvisionamentoCompleto();
    }

    /**
     * Guard: verifica se pode concluir contrato
     * Condição: saldo de receber e pagar = 0
     */
    public function conclude(): bool
    {
        return $this->isSaldoZero();
    }

    /**
     * Guard: verifica se pode cancelar
     * Condição: não está em estado terminal
     */
    public function cancel(): bool
    {
        return !$this->isTerminal();
    }

    // ============================================
    // HELPERS (Métodos de Verificação)
    // ============================================

    /**
     * Verifica se contrato tem campos obrigatórios
     */
    public function checkContratoValido(): bool
    {
        return $this->artista_id
            && $this->booker_id
            && $this->valor_bruto > 0
            && $this->data_evento;
    }

    /**
     * Verifica se provisionamento está completo (RN-001)
     * Soma das parcelas tipo 'booking' deve = valor_bruto
     */
    public function isProvisionamentoCompleto(): bool
    {
        if (!$this->relationLoaded('contasAReceber')) {
            $this->load('contasAReceber');
        }

        $somaBooking = $this->contasAReceber
            ->where('tipo_lancamento', 'booking')
            ->sum('valor_previsto');

        // Tolerância de 1 centavo para evitar problemas de floating point
        return abs((float) $somaBooking - (float) $this->valor_bruto) < 0.01;
    }

    /**
     * Retorna soma das parcelas booking
     */
    public function getSomaBookingAttribute(): float
    {
        if (!$this->relationLoaded('contasAReceber')) {
            $this->load('contasAReceber');
        }

        return (float) $this->contasAReceber
            ->where('tipo_lancamento', 'booking')
            ->sum('valor_previsto');
    }

    /**
     * Retorna valor faltante para completar provisionamento
     */
    public function getValorFaltanteAttribute(): float
    {
        return max(0, (float) $this->valor_bruto - $this->somaBooking);
    }

    /**
     * Verifica se saldo é zero (todas parcelas quitadas)
     */
    public function isSaldoZero(): bool
    {
        if ($this->contasAReceber->isNotEmpty()) {
            $temReceberPendente = $this->contasAReceber
                ->whereNotIn('status_pagamento', ['quitado'])
                ->isNotEmpty();
            if ($temReceberPendente) {
                return false;
            }
        }

        if ($this->relationLoaded('contasAPagar')) {
            $temPagarPendente = $this->contasAPagar
                ->whereNotIn('status_pagamento', ['pago'])
                ->isNotEmpty();
            if ($temPagarPendente) {
                return false;
            }
        }

        return true;
    }

    // ============================================
    // CALLBACKS (Ações durante transições)
    // ============================================

    /**
     * Callback ao entrar em Em Execucao
     */
    protected function onEnterEmExecucao(): void
    {
        // Notificar financeiro (implementar depois com Notification)
        // Por enquanto, apenas log
        \Illuminate\Support\Facades\Log::info("Contrato {$this->id} entrou em Execução");
    }

    /**
     * Callback ao entrar em Concluido
     */
    protected function onEnterConcluido(): void
    {
        \Illuminate\Support\Facades\Log::info("Contrato {$this->id} foi concluído");
    }

    /**
     * Executa callback apropriado para o estado
     */
    protected function executeCallback(string $estado): void
    {
        match ($estado) {
            self::ESTADO_EM_EXECUCAO => $this->onEnterEmExecucao(),
            self::ESTADO_CONCLUIDO => $this->onEnterConcluido(),
            default => null,
        };
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Scope: contratos em draft
     */
    public function scopeDraft($query)
    {
        return $query->where('assinatura_status', self::ESTADO_DRAFT);
    }

    /**
     * Scope: contratos em provisionamento
     */
    public function scopeProvisionando($query)
    {
        return $query->where('assinatura_status', self::ESTADO_PROVISIONANDO);
    }

    /**
     * Scope: contratos em execução
     */
    public function scopeEmExecucao($query)
    {
        return $query->where('assinatura_status', self::ESTADO_EM_EXECUCAO);
    }

    /**
     * Scope: contratos concluídos
     */
    public function scopeConcluidos($query)
    {
        return $query->where('assinatura_status', self::ESTADO_CONCLUIDO);
    }

    /**
     * Scope: contratos não terminados (ativos)
     */
    public function scopeAtivos($query)
    {
        return $query->whereNotIn('assinatura_status', [
            self::ESTADO_CONCLUIDO,
            self::ESTADO_CANCELADO,
        ]);
    }
}
