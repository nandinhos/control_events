<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ExtratoBancarioTransacao extends Model
{
    protected $table = 'extrato_bancario_transacaos';

    public const STATUS_CONCILIACAO_PENDENTE = 'pendente';
    public const STATUS_CONCILIACAO_CONCILIADO = 'conciliado';
    public const STATUS_CONCILIACAO_EXCLUIDO = 'excluido';

    protected $fillable = [
        'conta_bancaria',
        'data_transacao',
        'descricao',
        'valor',
        'tipo',
        'saldo_resultante',
        'hash_unico',
        'conciliado_status',
        'data_importacao',
    ];

    protected $casts = [
        'data_transacao' => 'date',
        'data_importacao' => 'datetime',
        'valor' => 'decimal:2',
        'saldo_resultante' => 'decimal:2',
    ];

    // Relationships
    public function conciliacaoLinks(): HasMany
    {
        return $this->hasMany(ConciliacaoBancariaLink::class, 'extrato_bancario_transacao_id');
    }

    // Scopes
    public function scopePendente(Builder $query): Builder
    {
        return $query->where('conciliado_status', null);
    }

    public function scopeConciliado(Builder $query): Builder
    {
        return $query->where('conciliado_status', 'R');
    }

    public function scopeExcluido(Builder $query): Builder
    {
        return $query->where('conciliado_status', 'E');
    }

    public function scopeByContaBancaria(Builder $query, string $conta): Builder
    {
        return $query->where('conta_bancaria', $conta);
    }

    public function scopeByDataTransacaoBetween(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('data_transacao', [$from, $to]);
    }

    public function scopeByHashUnico(Builder $query, string $hash): Builder
    {
        return $query->where('hash_unico', $hash);
    }

    // Accessors
    public function getIsPendenteAttribute(): bool
    {
        return $this->conciliado_status === null;
    }

    public function getIsConciliadoAttribute(): bool
    {
        return $this->conciliado_status === 'R';
    }

    public function getIsCreditoAttribute(): bool
    {
        return $this->valor > 0;
    }

    public function getIsDebitoAttribute(): bool
    {
        return $this->valor < 0;
    }

    public function getValorAbsolutoAttribute(): float
    {
        return abs((float) $this->valor);
    }
}