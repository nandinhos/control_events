<?php

namespace App\Http\Requests\Entidade;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEntidadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $entidadeId = $this->route('entidade');

        return [
            'cnpj_cpf' => ['sometimes', 'string', 'max:20', Rule::unique('entidades', 'cnpj_cpf')->ignore($entidadeId)],
            'razao_social' => ['sometimes', 'string', 'max:200'],
            'nome_fantasia' => ['nullable', 'string', 'max:200'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'inscricao_estadual' => ['nullable', 'string', 'max:30'],
            'endereco' => ['nullable', 'string', 'max:300'],
            'complemento' => ['nullable', 'string', 'max:100'],
            'cep' => ['nullable', 'string', 'max:10'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:2'],
            'banco_nome' => ['nullable', 'string', 'max:100'],
            'banco_agencia' => ['nullable', 'string', 'max:20'],
            'banco_conta' => ['nullable', 'string', 'max:30'],
            'chave_pix' => ['nullable', 'string', 'max:100'],
            'banco_titular_doc' => ['nullable', 'string', 'max:20'],
            'tipo' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'cnpj_cpf.unique' => 'Este CNPJ/CPF já está cadastrado.',
            'cnpj_cpf.max' => 'O CNPJ/CPF deve ter no máximo 20 caracteres.',
            'razao_social.required' => 'A razão social é obrigatória.',
            'email.email' => 'O email deve ser um endereço válido.',
            'website.url' => 'O website deve ser uma URL válida.',
        ];
    }
}
