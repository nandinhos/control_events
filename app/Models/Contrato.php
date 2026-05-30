<?php

namespace App\Models;

use App\Models\Traits\HasContratoLifecycle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Contrato extends Model
{
    use HasContratoLifecycle;
    protected $table = 'contratos';

    public const STATUS_BOOKING_CONFIRMADO = 'Confirmado';
    public const STATUS_BOOKING_CANCELADO = 'Cancelado';
    public const STATUS_BOOKING_PENDENTE = 'Pendente';

    public const ASSINATURA_STATUS_DRAFT = 'Draft';
    public const ASSINATURA_STATUS_AGUARDANDO_ASSINATURA = 'Aguardando Assinatura';
    public const ASSINATURA_STATUS_EM_EXECUCAO = 'Em Execucao';
    public const ASSINATURA_STATUS_CONCLUIDO = 'Concluido';
    public const ASSINATURA_STATUS_CANCELADO = 'Cancelado';
    public const ASSINATURA_STATUS_EXPIRADO = 'Expirado';

    public const MOEDA_BRL = 'BRL';

    protected $fillable = [
        'codigo_contrato',
        'semana_inicio',
        'status_booking',
        'booker_id',
        'agencia_sigla',
        'assinatura_status',
        'data_venda',
        'data_evento',
        'artista_id',
        'valor_bruto',
        'moeda',
        'comissao_valor',
        'local_evento',
        'nome_evento',
        'cidade_evento',
        'estado_evento',
        'regiao_evento',
        'pais_evento',
    ];

    protected $casts = [
        'data_venda' => 'date',
        'data_evento' => 'date',
        'valor_bruto' => 'decimal:2',
        'comissao_valor' => 'decimal:2',
    ];

    // Relationships
    public function artista(): BelongsTo
    {
        return $this->belongsTo(Entidade::class, 'artista_id');
    }

    public function booker(): BelongsTo
    {
        return $this->belongsTo(Entidade::class, 'booker_id');
    }

    public function contasAReceber(): HasMany
    {
        return $this->hasMany(ContasAReceber::class, 'contrato_id');
    }

    public function contasAPagar(): HasMany
    {
        return $this->hasMany(ContasAPagar::class, 'contrato_ref_id');
    }

    // Scopes
    public function scopeConfirmado(Builder $query): Builder
    {
        return $query->where('status_booking', self::STATUS_BOOKING_CONFIRMADO);
    }

    public function scopeCancelado(Builder $query): Builder
    {
        return $query->where('status_booking', self::STATUS_BOOKING_CANCELADO);
    }

    public function scopePendente(Builder $query): Builder
    {
        return $query->where('status_booking', self::STATUS_BOOKING_PENDENTE);
    }

    public function scopeByArtista(Builder $query, int $artistaId): Builder
    {
        return $query->where('artista_id', $artistaId);
    }

    public function scopeByBooker(Builder $query, int $bookerId): Builder
    {
        return $query->where('booker_id', $bookerId);
    }

    public function scopeByStatusBooking(Builder $query, string $status): Builder
    {
        return $query->where('status_booking', $status);
    }

    public function scopeByAssinaturaStatus(Builder $query, string $status): Builder
    {
        return $query->where('assinatura_status', $status);
    }

    public function scopeByDataEventoBetween(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('data_evento', [$from, $to]);
    }

    public function scopeByPais(Builder $query, string $pais): Builder
    {
        return $query->where('pais_evento', $pais);
    }

    public function scopeByEstado(Builder $query, string $estado): Builder
    {
        return $query->where('estado_evento', $estado);
    }

    // Accessors
    public function getIsConfirmadoAttribute(): bool
    {
        return $this->status_booking === self::STATUS_BOOKING_CONFIRMADO;
    }

    public function getIsCanceladoAttribute(): bool
    {
        return $this->status_booking === self::STATUS_BOOKING_CANCELADO;
    }

    public function getIsPendenteAttribute(): bool
    {
        return $this->status_booking === self::STATUS_BOOKING_PENDENTE;
    }

    public function getValorLiquidoAttribute(): float
    {
        return (float) $this->valor_bruto - (float) ($this->comissao_valor ?? 0);
    }

    public function getTextoStatusBookingAttribute(): string
    {
        return ucfirst($this->status_booking);
    }
}