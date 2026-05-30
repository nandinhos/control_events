<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ContasAPagar extends Model
{
    protected $table = 'contas_a_pagar';

    public const STATUS_PAGAMENTO_PENDENTE = 'pendente';
    public const STATUS_PAGAMENTO_PROCESSANDO = 'processando';
    public const STATUS_PAGAMENTO_PAGO = 'pago';

    public const CONCILIADO_STATUS_R = 'R';
    public const CONCILIADO_STATUS_N = 'N';
    public const CONCILIADO_STATUS_P = 'P';

    protected $fillable = [
        'conciliado_status',
        'ano_vencimento',
        'mes_vencimento',
        'status_pagamento',
        'conta_origem',
        'data_devida',
        'data_pagamento',
        'data_emissao',
        'valor_devido',
        'valor_pago',
        'contraparte_id',
        'tipo_doc_fiscal',
        'num_doc_fiscal',
        'descricao',
        'data_evento',
        'contrato_ref_id',
        'plano_contas_id',
        'cashflow_categoria',
        'cashflow_subcat',
        'caixa_referencia',
        'competencia_ref',
        'meio_pagamento',
        'info_favorecido',
        'observacoes',
        'valor_brl',
        'valor_usd',
        'valor_eur',
        'valor_gbp',
        'moeda_original',
        'taxa_cambio',
        'tipo_cambio',
        'taxa_cambio_user_id',
    ];

    protected $casts = [
        'data_devida' => 'date',
        'data_pagamento' => 'date',
        'data_emissao' => 'date',
        'data_evento' => 'date',
        'valor_devido' => 'decimal:2',
        'valor_pago' => 'decimal:2',
        'ano_vencimento' => 'integer',
        'mes_vencimento' => 'integer',
    ];

    // Relationships
    public function contraparte(): BelongsTo
    {
        return $this->belongsTo(Entidade::class, 'contraparte_id');
    }

    public function contratoRef(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'contrato_ref_id');
    }

    public function planoContas(): BelongsTo
    {
        return $this->belongsTo(NomenclaturaConfig::class, 'plano_contas_id');
    }

    // Scopes
    public function scopePendente(Builder $query): Builder
    {
        return $query->where('status_pagamento', self::STATUS_PAGAMENTO_PENDENTE);
    }

    public function scopeProcessando(Builder $query): Builder
    {
        return $query->where('status_pagamento', self::STATUS_PAGAMENTO_PROCESSANDO);
    }

    public function scopePago(Builder $query): Builder
    {
        return $query->where('status_pagamento', self::STATUS_PAGAMENTO_PAGO);
    }

    public function scopeByContraparte(Builder $query, int $contraparteId): Builder
    {
        return $query->where('contraparte_id', $contraparteId);
    }

    public function scopeByAnoVencimento(Builder $query, int $ano): Builder
    {
        return $query->where('ano_vencimento', $ano);
    }

    public function scopeByMesVencimento(Builder $query, int $mes): Builder
    {
        return $query->where('mes_vencimento', $mes);
    }

    public function scopeByPeriodoVencimento(Builder $query, int $ano, int $mes): Builder
    {
        return $query->where('ano_vencimento', $ano)->where('mes_vencimento', $mes);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status_pagamento', $status);
    }

    public function scopeVencimentosEntre(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('data_devida', [$from, $to]);
    }

    public function scopeByCashflowCategoria(Builder $query, string $categoria): Builder
    {
        return $query->where('cashflow_categoria', $categoria);
    }

    // Accessors
    public function getIsPendenteAttribute(): bool
    {
        return $this->status_pagamento === self::STATUS_PAGAMENTO_PENDENTE;
    }

    public function getIsProcessandoAttribute(): bool
    {
        return $this->status_pagamento === self::STATUS_PAGAMENTO_PROCESSANDO;
    }

    public function getIsPagoAttribute(): bool
    {
        return $this->status_pagamento === self::STATUS_PAGAMENTO_PAGO;
    }

    public function getValorEmAbertoAttribute(): float
    {
        return (float) $this->valor_devido - (float) ($this->valor_pago ?? 0);
    }

    public function getDiferencaPagamentoAttribute(): ?int
    {
        if (!$this->data_devida || !$this->data_pagamento) {
            return null;
        }
        return (int) $this->data_pagamento->diffInDays($this->data_devida, false);
    }

    public function getPeriodoVencimentoAttribute(): string
    {
        return sprintf('%04d-%02d', $this->ano_vencimento, $this->mes_vencimento);
    }
}