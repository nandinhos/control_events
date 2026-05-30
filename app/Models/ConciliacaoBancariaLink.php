<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ConciliacaoBancariaLink extends Model
{
    protected $table = 'conciliacao_bancaria_links';

    protected $fillable = [
        'extrato_bancario_transacao_id',
        'tipo_lancamento',
        'lancamento_id',
        'lancamento_type',
        'valor_conciliado',
    ];

    protected $casts = [
        'valor_conciliado' => 'decimal:2',
    ];

    // Relationships
    public function transacao(): BelongsTo
    {
        return $this->belongsTo(ExtratoBancarioTransacao::class, 'extrato_bancario_transacao_id');
    }

    public function transactable(): MorphTo
    {
        return $this->morphTo(null, 'lancamento_type', 'lancamento_id');
    }

    public function contaReceber(): MorphTo
    {
        return $this->morphTo(ContasAReceber::class, 'lancamento_type', 'lancamento_id');
    }

    public function contaPagar(): MorphTo
    {
        return $this->morphTo(ContasAPagar::class, 'lancamento_type', 'lancamento_id');
    }

    // Accessors
    public function getIsContasAReceberAttribute(): bool
    {
        return $this->lancamento_type === ContasAReceber::class;
    }

    public function getIsContasAPagarAttribute(): bool
    {
        return $this->lancamento_type === ContasAPagar::class;
    }

    public function getLancamentoAttribute(): Model|null
    {
        return $this->transactable;
    }
}