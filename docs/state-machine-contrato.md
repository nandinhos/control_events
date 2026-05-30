# State Machine — Contrato Lifecycle

**Data:** 2026-05-30  
**Status:** SM-001 Design Completo

---

## Diagrama de Estados

```
                                    ┌─────────────────┐
                                    │                 │
                                    │     DRAFT       │◄──────┐
                                    │ (Confeccionando)│       │
                                    └────────┬────────┘       │ cancel
                                             │                │ (from draft,
                                             │ start_         │  provisionando)
                                             │ provisioning   │  em_execucao)
                                             ▼                │
                                    ┌─────────────────┐       │
                                    │                 │       │
                                    │  PROVISIONANDO  │───────┘
                                    │ (Aguardando     │       cancel
                                    │  Parcelas)      │       (from
                                    └────────┬────────┘       │  provisionando)
                                             │                │
                                             │ complete_      │
                                             │ provisioning   │
                                             │ (if sum =      │
                                             │  valor_bruto)  │
                                             ▼                │
                                    ┌─────────────────┐       │
                                    │                 │       │
                                    │   EM EXECUCAO   │───────┘
                                    │ (Liberado Pagar/│       cancel
                                    │  Receber)      │  (from em_execucao)
                                    └────────┬────────┘
                                             │
                                             │ conclude
                                             │ (if saldo = 0
                                             │  for all
                                             │  linked)
                                             ▼
                                    ┌─────────────────┐
                                    │                 │
                                    │   CONCLUIDO     │
                                    │ (Terminal)      │
                                    └─────────────────┘
```

---

## Estados

| Estado | Descrição | Terminal? |
|--------|-----------|----------|
| `draft` | Rascunho inicial, edição livre | Não |
| `provisionando` | Aguardando definição de parcelas a receber | Não |
| `em_execucao` | Liberado para Pagar/Receber | Não |
| `concluido` | Todas parcelas liquidadas | **Sim** |
| `cancelado` | Cancelado (não implementado no diagrama mas existente) | **Sim** |

---

## Transições

| Transição | De | Para | Guard | Callback |
|-----------|-----|------|-------|----------|
| `start_provisioning` | draft | provisionando | `checkContratoValido` | `ContratoProvisioningStarted` |
| `complete_provisioning` | provisionando | em_execucao | `checkProvisionamentoCompleto` | `ContratoExecutionStarted` |
| `conclude` | em_execucao | concluido | `checkSaldoZero` | `ContratoConcluded` |
| `cancel` | draft, provisionando, em_execucao | cancelado | `checkPodeCancelar` | `ContratoCanceled` |

---

## Guards (Validadores)

### 1. checkContratoValido
Verifica que o contrato tem campos obrigatórios:
- `artista_id` existe
- `booker_id` existe  
- `valor_bruto` > 0
- `data_evento` está definida

### 2. checkProvisionamentoCompleto (RN-001)
**CRÍTICO — RN-001 do PRD**

```
SOMA(parcelas tipo='booking') deve ser IGUAL a valor_bruto do contrato
```

```sql
SUM(CASE WHEN tipo_lancamento = 'booking' THEN valor_previsto ELSE 0 END) = valor_bruto
```

**Validação:**
- Soma dos lançamentos tipo `booking` = `valor_bruto` (100%)
- Lançamentos tipo `extra` NÃO entram na validação
- Tolerância: 0 (deve bater exato)

### 3. checkSaldoZero
Verifica que todas as obrigações estão liquidadas:
- `contas_a_receber`: todos `status_pagamento = 'quitado'`
- `contas_a_pagar`: todos `status_pagamento = 'pago'`

### 4. checkPodeCancelar
- Contrato não pode estar em `concluido`
- Contrato pode estar em `draft`, `provisionando`, ou `em_execucao`

---

## Callbacks

| Callback | Quando | Ação |
|---------|--------|------|
| `onEnterEmExecucao` | Entrando em `em_execucao` | Notificar financeiro, liberar criação de Pagar |
| `onEnterConcluido` | Entrando em `concluido` | Verificar saldo zero, arquivar contrato |
| `onLeaveEmExecucao` | Saindo de `em_execucao` | Se cancelando, bloquear Pagar |

---

## Events

| Event | Disparado | Payload |
|-------|----------|---------|
| `ContratoProvisioningStarted` | `start_provisioning` completa | contrato_id, timestamp |
| `ContratoExecutionStarted` | `complete_provisioning` completa | contrato_id, timestamp |
| `ContratoConcluded` | `conclude` completa | contrato_id, timestamp |
| `ContratoCanceled` | `cancel` completa | contrato_id, motivo, timestamp |

---

## Estrutura de Arquivos

```
app/
├── StateMachine/
│   └── Contrato/
│       ├── ContratoStateMachine.php       # Configuração principal
│       ├── States/
│       │   └── ContratoEstado.php        # Enum de estados
│       └── Transitions/
│           ├── StartProvisioning.php
│           ├── CompleteProvisioning.php
│           ├── Conclude.php
│           └── Cancel.php
├── Models/
│   └── Contrato.php                      # Adicionar HasStateMachine trait
└── Http/
    └── Requests/
        └── Contrato/
            └── StartProvisioningRequest.php
```

---

## Implementação (SM-002)

### Passo 1: Migration
Criar coluna `contrato_estado` na tabela `contratos`:
```php
$table->string('contrato_estado')->default('draft');
```

### Passo 2: States Enum
```php
enum ContratoEstado: string {
    case Draft = 'draft';
    case Provisionando = 'provisionando';
    case EmExecucao = 'em_execucao';
    case Concluido = 'concluido';
    case Cancelado = 'cancelado';
}
```

### Passo 3: Configuração StateMachine
Usar padrão similar ao `asantibanez/laravel-eloquent-state-machine` ou `spatie/laravel-state-machine`.

---

## Critérios de Aceitação

- [ ] Contrato em `draft` pode ir para `provisionando`
- [ ] Contrato em `provisionando` só vai para `em_execucao` se soma booking = valor_bruto
- [ ] Contrato em `em_execucao` só vai para `concluido` se saldo = 0
- [ ] Contrato pode ser cancelado de qualquer estado (exceto `concluido`)
- [ ] Events são disparados nas transições
- [ ] RN-001 (bloquear Pagar) é respeitada
