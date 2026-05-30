<?php

namespace App\Livewire\Entidades;

use App\Http\Requests\StoreEntidadeRequest;
use App\Http\Requests\UpdateEntidadeRequest;
use App\Models\Entidade;
use Illuminate\Http\Request;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?Entidade $entidade = null;

    public bool $showModal = false;

    public bool $isEditing = false;

    // Form fields
    public string $cnpj_cpf = '';

    public string $razao_social = '';

    public string $nome_fantasia = '';

    public string $email = '';

    public string $telefone = '';

    public string $website = '';

    public string $inscricao_estadual = '';

    public string $endereco = '';

    public string $complemento = '';

    public string $cep = '';

    public string $bairro = '';

    public string $cidade = '';

    public string $estado = '';

    public string $banco_nome = '';

    public string $banco_agencia = '';

    public string $banco_conta = '';

    public string $chave_pix = '';

    public string $banco_titular_doc = '';

    public array $tipo = [];

    public string $activeTab = 'dados_basicos';

    protected $listeners = ['refreshEntidades' => '$refresh'];

    #[Computed]
    public function entidades()
    {
        return Entidade::query()
            ->search($this->search)
            ->orderBy('razao_social')
            ->paginate(10);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset([
            'entidade', 'isEditing', 'cnpj_cpf', 'razao_social', 'nome_fantasia',
            'email', 'telefone', 'website', 'inscricao_estadual', 'endereco',
            'complemento', 'cep', 'bairro', 'cidade', 'estado', 'banco_nome',
            'banco_agencia', 'banco_conta', 'chave_pix', 'banco_titular_doc', 'tipo',
        ]);
        $this->activeTab = 'dados_basicos';
        $this->showModal = true;
    }

    public function openEditModal(Entidade $entidade): void
    {
        $this->resetValidation();
        $this->entidade = $entidade;
        $this->isEditing = true;
        $this->cnpj_cpf = $entidade->cnpj_cpf;
        $this->razao_social = $entidade->razao_social;
        $this->nome_fantasia = $entidade->nome_fantasia ?? '';
        $this->email = $entidade->email ?? '';
        $this->telefone = $entidade->telefone ?? '';
        $this->website = $entidade->website ?? '';
        $this->inscricao_estadual = $entidade->inscricao_estadual ?? '';
        $this->endereco = $entidade->endereco ?? '';
        $this->complemento = $entidade->complemento ?? '';
        $this->cep = $entidade->cep ?? '';
        $this->bairro = $entidade->bairro ?? '';
        $this->cidade = $entidade->cidade ?? '';
        $this->estado = $entidade->estado ?? '';
        $this->banco_nome = $entidade->banco_nome ?? '';
        $this->banco_agencia = $entidade->banco_agencia ?? '';
        $this->banco_conta = $entidade->banco_conta ?? '';
        $this->chave_pix = $entidade->chave_pix ?? '';
        $this->banco_titular_doc = $entidade->banco_titular_doc ?? '';
        $this->tipo = $entidade->tipo ?? [];
        $this->activeTab = 'dados_basicos';
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->activeTab === 'dados_basicos' ? $this->validateBasicos() : $this->validateAll();

        if ($this->isEditing) {
            $this->update();
        } else {
            $this->store();
        }
    }

    public function validateBasicos(): void
    {
        $rules = [
            'cnpj_cpf' => ['required', 'string', 'max:20', $this->isEditing ? "unique:entidades,cnpj_cpf,{$this->entidade->id}" : 'unique:entidades,cnpj_cpf'],
            'razao_social' => ['required', 'string', 'max:200'],
            'nome_fantasia' => ['nullable', 'string', 'max:200'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'inscricao_estadual' => ['nullable', 'string', 'max:30'],
        ];

        $this->validate($rules);
    }

    public function validateAll(): void
    {
        $rules = [
            'cnpj_cpf' => ['required', 'string', 'max:20', $this->isEditing ? "unique:entidades,cnpj_cpf,{$this->entidade->id}" : 'unique:entidades,cnpj_cpf'],
            'razao_social' => ['required', 'string', 'max:200'],
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

        $this->validate($rules);
    }

    protected function store(): void
    {
        Entidade::create([
            'cnpj_cpf' => $this->cnpj_cpf,
            'razao_social' => $this->razao_social,
            'nome_fantasia' => $this->nome_fantasia ?: null,
            'email' => $this->email ?: null,
            'telefone' => $this->telefone ?: null,
            'website' => $this->website ?: null,
            'inscricao_estadual' => $this->inscricao_estadual ?: null,
            'endereco' => $this->endereco ?: null,
            'complemento' => $this->complemento ?: null,
            'cep' => $this->cep ?: null,
            'bairro' => $this->bairro ?: null,
            'cidade' => $this->cidade ?: null,
            'estado' => $this->estado ?: null,
            'banco_nome' => $this->banco_nome ?: null,
            'banco_agencia' => $this->banco_agencia ?: null,
            'banco_conta' => $this->banco_conta ?: null,
            'chave_pix' => $this->chave_pix ?: null,
            'banco_titular_doc' => $this->banco_titular_doc ?: null,
            'tipo' => array_values(array_filter($this->tipo)),
        ]);

        $this->closeModal();
        session()->flash('success', 'Entidade criada com sucesso.');
    }

    protected function update(): void
    {
        $this->entidade->update([
            'cnpj_cpf' => $this->cnpj_cpf,
            'razao_social' => $this->razao_social,
            'nome_fantasia' => $this->nome_fantasia ?: null,
            'email' => $this->email ?: null,
            'telefone' => $this->telefone ?: null,
            'website' => $this->website ?: null,
            'inscricao_estadual' => $this->inscricao_estadual ?: null,
            'endereco' => $this->endereco ?: null,
            'complemento' => $this->complemento ?: null,
            'cep' => $this->cep ?: null,
            'bairro' => $this->bairro ?: null,
            'cidade' => $this->cidade ?: null,
            'estado' => $this->estado ?: null,
            'banco_nome' => $this->banco_nome ?: null,
            'banco_agencia' => $this->banco_agencia ?: null,
            'banco_conta' => $this->banco_conta ?: null,
            'chave_pix' => $this->chave_pix ?: null,
            'banco_titular_doc' => $this->banco_titular_doc ?: null,
            'tipo' => array_values(array_filter($this->tipo)),
        ]);

        $this->closeModal();
        session()->flash('success', 'Entidade atualizada com sucesso.');
    }

    public function delete(Entidade $entidade): void
    {
        $entidade->delete();
        session()->flash('success', 'Entidade excluída com sucesso.');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function toggleTipo(string $value): void
    {
        $index = array_search($value, $this->tipo);
        if ($index !== false) {
            unset($this->tipo[$index]);
            $this->tipo = array_values($this->tipo);
        } else {
            $this->tipo[] = $value;
        }
    }

    public function isTipoSelected(string $value): bool
    {
        return in_array($value, $this->tipo, true);
    }

    public function render()
    {
        return view('livewire.entidades.index', [
            'entidades' => $this->entidades,
        ]);
    }
}
