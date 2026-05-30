<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class NomenclaturaConfig extends Model
{
    protected $table = 'nomenclatura_configs';

    public const TIPO_CATEGORIA_CAIXA = 'Caixa';
    public const TIPO_CATEGORIA_CASHFLOW = 'Cashflow';
    public const TIPO_CATEGORIA_PLANO_CONTAS = 'Plano de Contas';
    public const TIPO_CATEGORIA_CONTA_BANCARIA = 'Conta Bancaria';

    protected $fillable = [
        'nome',
        'tipo',
        'codigo',
        'descricao',
        'categoria_pai_id',
        'ativo',
        'parametros_extras',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'parametros_extras' => 'array',
    ];

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(NomenclaturaConfig::class, 'categoria_pai_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(NomenclaturaConfig::class, 'categoria_pai_id');
    }

    public function contasAPagar(): HasMany
    {
        return $this->hasMany(ContasAPagar::class, 'plano_contas_id');
    }

    public function extratoBancarioTransacoes(): HasMany
    {
        return $this->hasMany(ExtratoBancarioTransacao::class, 'conta_bancaria_id');
    }

    // Scopes
    public function scopeAtivo(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function scopeByTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeByCodigo(Builder $query, string $codigo): Builder
    {
        return $query->where('codigo', $codigo);
    }

    public function scopeByCategoriaPai(Builder $query, int $paiId): Builder
    {
        return $query->where('categoria_pai_id', $paiId);
    }

    public function scopeCaixa(Builder $query): Builder
    {
        return $query->where('tipo', self::TIPO_CATEGORIA_CAIXA);
    }

    public function scopeCashflow(Builder $query): Builder
    {
        return $query->where('tipo', self::TIPO_CATEGORIA_CASHFLOW);
    }

    public function scopePlanoContas(Builder $query): Builder
    {
        return $query->where('tipo', self::TIPO_CATEGORIA_PLANO_CONTAS);
    }

    public function scopeContaBancaria(Builder $query): Builder
    {
        return $query->where('tipo', self::TIPO_CATEGORIA_CONTA_BANCARIA);
    }

    // Accessors
    public function getIsAtivoAttribute(): bool
    {
        return (bool) $this->ativo;
    }

    public function getIsRaizAttribute(): bool
    {
        return $this->categoria_pai_id === null;
    }

    public function getTextoTipoAttribute(): string
    {
        return ucfirst($this->tipo);
    }

    public function getNomeCompletoAttribute(): string
    {
        if ($this->isRaiz) {
            return $this->nome;
        }
        $pai = $this->parent;
        return $pai ? "{$pai->nome} > {$this->nome}" : $this->nome;
    }
}