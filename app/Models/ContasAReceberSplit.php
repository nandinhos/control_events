<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ContasAReceberSplit extends Model
{
    protected $table = 'contas_a_receber_splits';

    public const TIPO_DESTINATARIO_ARTISTA = 'Artista';
    public const TIPO_DESTINATARIO_BOOKER = 'Booker';
    public const TIPO_DESTINATARIO_ENTIDADE = 'Entidade';

    protected $fillable = [
        'lancamento_id',
        'tipo_destinatario',
        'entidade_id',
        'valor_percentual',
        'valor_absoluto',
        'observacao',
    ];

    protected $casts = [
        'valor_percentual' => 'decimal:4',
        'valor_absoluto' => 'decimal:2',
    ];

    // Relationships
    public function lancamento(): BelongsTo
    {
        return $this->belongsTo(ContasAReceber::class, 'lancamento_id');
    }

    public function entidade(): BelongsTo
    {
        return $this->belongsTo(Entidade::class, 'entidade_id');
    }

    // Scopes
    public function scopeByTipoDestinatario(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo_destinatario', $tipo);
    }

    public function scopeByEntidade(Builder $query, int $entidadeId): Builder
    {
        return $query->where('entidade_id', $entidadeId);
    }

    public function scopeByLancamento(Builder $query, int $lancamentoId): Builder
    {
        return $query->where('lancamento_id', $lancamentoId);
    }

    // Accessors
    public function getIsArtistaAttribute(): bool
    {
        return $this->tipo_destinatario === self::TIPO_DESTINATARIO_ARTISTA;
    }

    public function getIsBookerAttribute(): bool
    {
        return $this->tipo_destinatario === self::TIPO_DESTINATARIO_BOOKER;
    }

    public function getIsEntidadeAttribute(): bool
    {
        return $this->tipo_destinatario === self::TIPO_DESTINATARIO_ENTIDADE;
    }

    public function getValorCalculadoAttribute(): float
    {
        if ($this->valor_absoluto > 0) {
            return (float) $this->valor_absoluto;
        }
        return 0;
    }
}