<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;

class Entidade extends Model
{
    protected $table = 'entidades';

    protected $fillable = [
        'tags',
        'cnpj_cpf',
        'razao_social',
        'nome_fantasia',
        'endereco',
        'complemento',
        'cep',
        'bairro',
        'cidade',
        'estado',
        'telefone',
        'email',
        'website',
        'inscricao_estadual',
        'banco_nome',
        'banco_agencia',
        'banco_conta',
        'chave_pix',
        'banco_titular_doc',
    ];

    protected $hidden = [
        'banco_agencia',
        'banco_conta',
        'chave_pix',
        'banco_titular_doc',
    ];

    protected function casts(): array
    {
        return [
            'banco_agencia' => 'encrypted',
            'banco_conta' => 'encrypted',
            'chave_pix' => 'encrypted',
            'banco_titular_doc' => 'encrypted',
        ];
    }

    // Relationships
    public function contratosComoArtista(): HasMany
    {
        return $this->hasMany(Contrato::class, 'artista_id');
    }

    public function contratosComoBooker(): HasMany
    {
        return $this->hasMany(Contrato::class, 'booker_id');
    }

    public function contasAReceber(): HasMany
    {
        return $this->hasMany(ContasAReceber::class, 'contratante_id');
    }

    public function contasAPagar(): HasMany
    {
        return $this->hasMany(ContasAPagar::class, 'contraparte_id');
    }

    public function contasAReceberSplits(): HasMany
    {
        return $this->hasMany(ContasAReceberSplit::class, 'entidade_id');
    }

    // Scopes
    public function scopeByCnpjCpf(Builder $query, string $cnpjCpf): Builder
    {
        return $query->where('cnpj_cpf', $cnpjCpf);
    }

    public function scopeByEstado(Builder $query, string $estado): Builder
    {
        return $query->where('estado', $estado);
    }

    public function scopeByCidade(Builder $query, string $cidade): Builder
    {
        return $query->where('cidade', $cidade);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('razao_social', 'like', "%{$term}%")
              ->orWhere('nome_fantasia', 'like', "%{$term}%")
              ->orWhere('cnpj_cpf', 'like', "%{$term}%");
        });
    }

    // Accessors
    public function getTagsArrayAttribute(): array
    {
        if (empty($this->tags)) {
            return [];
        }
        return array_map('trim', explode(',', $this->tags));
    }

    public function getDocumentoFormatadoAttribute(): ?string
    {
        $doc = preg_replace('/\D/', '', $this->cnpj_cpf ?? '');
        if (strlen($doc) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $doc);
        }
        if (strlen($doc) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $doc);
        }
        return $this->cnpj_cpf;
    }

    public function getIsPessoaFisicaAttribute(): bool
    {
        $doc = preg_replace('/\D/', '', $this->cnpj_cpf ?? '');
        return strlen($doc) === 11;
    }

    public function getIsPessoaJuridicaAttribute(): bool
    {
        $doc = preg_replace('/\D/', '', $this->cnpj_cpf ?? '');
        return strlen($doc) === 14;
    }
}