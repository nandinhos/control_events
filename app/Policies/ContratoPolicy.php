<?php

namespace App\Policies;

use App\Models\Contrato;
use App\Models\User;

class ContratoPolicy
{
    /**
     * Pode acessar module de contratos
     */
    public function access(?User $user): bool
    {
        if (!$user) return false;
        return $user->hasAnyRole([
            User::ROLE_CONTRACT,
            User::ROLE_RECEIVABLE,
            User::ROLE_ADMIN_FINANCE,
        ]);
    }

    /**
     * Pode visualizar dados financeiros do contrato
     */
    public function viewFinancials(?User $user, Contrato $contrato): bool
    {
        if (!$user) return false;

        // Admin finance vê tudo
        if ($user->isAdminFinance()) return true;

        // Receivable e payable veem financials
        return $user->hasAnyRole([
            User::ROLE_RECEIVABLE,
            User::ROLE_PAYABLE,
        ]);
    }

    /**
     * Pode criar/modificar contratos
     */
    public function manage(?User $user): bool
    {
        if (!$user) return false;
        return $user->hasAnyRole([
            User::ROLE_CONTRACT,
            User::ROLE_ADMIN_FINANCE,
        ]);
    }

    /**
     * Pode iniciar provisionamento (mudar para Aguardando Assinatura)
     */
    public function startProvisioning(?User $user, Contrato $contrato): bool
    {
        if (!$user) return false;

        // Só quem pode gerenciar contratos
        if (!$this->manage($user)) return false;

        // Contrato deve estar em Draft
        return $contrato->getEstado() === Contrato::ESTADO_DRAFT;
    }

    /**
     * Pode completar provisionamento (mudar para Em Execucao)
     * RN-001: Soma booking deve = valor_bruto
     */
    public function completeProvisioning(?User $user, Contrato $contrato): bool
    {
        if (!$user) return false;

        // Admin finance ou receivable
        if (!$user->hasAnyRole([User::ROLE_RECEIVABLE, User::ROLE_ADMIN_FINANCE])) {
            return false;
        }

        // Contrato deve estar em Provisionando
        if ($contrato->getEstado() !== Contrato::ESTADO_PROVISIONANDO) {
            return false;
        }

        // Provisionamento deve estar completo
        return $contrato->isProvisionamentoCompleto();
    }

    /**
     * Pode concluir contrato (mudar para Concluido)
     * Requer saldo zero
     */
    public function conclude(?User $user, Contrato $contrato): bool
    {
        if (!$user) return false;

        // Admin finance pode concluir
        if (!$user->isAdminFinance()) return false;

        // Contrato deve estar em Execucao
        if ($contrato->getEstado() !== Contrato::ESTADO_EM_EXECUCAO) {
            return false;
        }

        // Saldo deve ser zero
        return $contrato->isSaldoZero();
    }

    /**
     * Pode cancelar contrato
     */
    public function cancel(?User $user, Contrato $contrato): bool
    {
        if (!$user) return false;

        // Contrato não pode estar em estado terminal
        if ($contrato->isTerminal()) return false;

        // Admin ou contract manager
        return $user->hasAnyRole([
            User::ROLE_ADMIN_FINANCE,
            User::ROLE_CONTRACT,
        ]);
    }
}
