<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ContasAReceber extends Model
{
    protected $table = 'contas_a_receber';

    public const STATUS_PAGAMENTO_ABERTO = 'aberto';
    public const STATUS_PAGAMENTO_QUITADO = 'quitado';
    public const STATUS_PAGAMENTO_VENCIDO = 'vencido';
    public const STATUS_PAGAMENTO_CANCELADO = 'cancelado';
    public const STATUS_PAGAMENTO_AGUARDANDO_CAMBIO = 'aguardando_cambio';

    public const REGISTRO_CONTABIL_PANORAMA = 'Panorama';
    public const REGISTRO_CONTABIL_CORAL = 'Coral';
    public const REGISTRO_CONTABIL_ARTISTA = 'Artista';

    public const TIPO_LANCAMENTO_BOOKING = 'Booking';
    public const TIPO_LANCAMENTO_EXTRA_CONTRATUAL = 'Extra Contratual';
    public const TIPO_LANCAMENTO_MOVIMENTACAO_INTERNA = 'Movimentacao Interna';

    protected $fillable = [
        'contrato_id',
        'mes_base',
        'booker_id',
        'status_booking',
        'registro_contabil',
        'data_evento',
        'artista_id',
        'nome_evento',
        'contratante_id',
        'tipo_lancamento',
        'parcela_numero',
        'valor_previsto',
        'vencimento_original',
        'vencimento_atual',
        'status_pagamento',
        'aging_dias',
        'data_pagamento',
        'valor_recebido',
        'valor_principal',
        'juros_multas',
        'cashflow_categoria',
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
        'data_evento' => 'date',
        'vencimento_original' => 'date',
        'vencimento_atual' => 'date',
        'data_pagamento' => 'date',
        'valor_previsto' => 'decimal:2',
        'valor_recebido' => 'decimal:2',
        'valor_principal' => 'decimal:2',
        'juros_multas' => 'decimal:2',
        'aging_dias' => 'integer',
    ];

    // Relationships
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    public function booker(): BelongsTo
    {
        return $this->belongsTo(Entidade::class, 'booker_id');
    }

    public function artista(): BelongsTo
    {
        return $this->belongsTo(Entidade::class, 'artista_id');
    }

    public function contratante(): BelongsTo
    {
        return $this->belongsTo(Entidade::class, 'contratante_id');
    }

    public function splits(): HasMany
    {
        return $this->hasMany(ContasAReceberSplit::class, 'lancamento_id');
    }

    // Scopes
    public function scopeAberto(Builder $query): Builder
    {
        return $query->where('status_pagamento', self::STATUS_PAGAMENTO_ABERTO);
    }

    public function scopeQuitado(Builder $query): Builder
    {
        return $query->where('status_pagamento', self::STATUS_PAGAMENTO_QUITADO);
    }

    public function scopeVencido(Builder $query): Builder
    {
        return $query->where('status_pagamento', self::STATUS_PAGAMENTO_VENCIDO);
    }

    public function scopeAguardandoCambio(Builder $query): Builder
    {
        return $query->where('status_pagamento', self::STATUS_PAGAMENTO_AGUARDANDO_CAMBIO);
    }

    // INT-003: Accessor para verificar se aguardando cambio
    public function getIsAguardandoCambioAttribute(): bool
    {
        return $this->status_pagamento === self::STATUS_PAGAMENTO_AGUARDANDO_CAMBIO;
    }

    // INT-003: Verifica se tem valor estrangeiro sem BRL
    public function getTemValorEstrangeiroSemBrlAttribute(): bool
    {
        return ($this->valor_usd > 0 || $this->valor_eur > 0 || $this->valor_gbp > 0) && $this->valor_brl == 0;
    }

    public function scopeByRegistroContabil(Builder $query, string $registro): Builder
    {
        return $query->where('registro_contabil', $registro);
    }

    public function scopeByTipoLancamento(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo_lancamento', $tipo);
    }

    public function scopeByBooker(Builder $query, int $bookerId): Builder
    {
        return $query->where('booker_id', $bookerId);
    }

    public function scopeByArtista(Builder $query, int $artistaId): Builder
    {
        return $query->where('artista_id', $artistaId);
    }

    public function scopeByContratante(Builder $query, int $contratanteId): Builder
    {
        return $query->where('contratante_id', $contratanteId);
    }

    public function scopeByMesBase(Builder $query, string $mesBase): Builder
    {
        return $query->where('mes_base', $mesBase);
    }

    // INT-005: Não aparece na conciliação bancária (movimentação interna)
    public function scopeVisivelNaConciliacao(Builder $query): Builder
    {
        return $query->where('tipo_lancamento', '!=', self::TIPO_LANCAMENTO_MOVIMENTACAO_INTERNA);
    }

    public function scopeVencimentosEntre(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('vencimento_atual', [$from, $to]);
    }

    // Accessors
    public function getIsAbertoAttribute(): bool
    {
        return $this->status_pagamento === self::STATUS_PAGAMENTO_ABERTO;
    }

    public function getIsQuitadoAttribute(): bool
    {
        return $this->status_pagamento === self::STATUS_PAGAMENTO_QUITADO;
    }

    public function getIsVencidoAttribute(): bool
    {
        if ($this->status_pagamento === self::STATUS_PAGAMENTO_VENCIDO) {
            return true;
        }
        if ($this->isAberto && $this->vencimento_atual && $this->vencimento_atual->lt(now())) {
            return true;
        }
        return false;
    }

    public function getValorEmAbertoAttribute(): float
    {
        return (float) $this->valor_previsto - (float) ($this->valor_recebido ?? 0);
    }

    public function getDiferencaVencimentoAttribute(): int
    {
        if (!$this->vencimento_atual) {
            return 0;
        }
        return (int) now()->diffInDays($this->vencimento_atual, false);
    }
}