<?php

namespace App\Livewire\Payable;

use App\Models\ContasAPagar;
use App\Models\Entidade;
use App\Models\NomenclaturaConfig;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public int $filterAno = 0;
    public int $filterMes = 0;
    public string $filterCashflow = '';
    public string $filterContraparte = '';

    public bool $showModal = false;
    public bool $isEditing = false;
    public ?ContasAPagar $conta = null;

    // Form fields
    public string $ano_vencimento = '';
    public string $mes_vencimento = '';
    public string $status_pagamento = 'pendente';
    public string $conta_origem = '';
    public string $data_devida = '';
    public string $data_pagamento = '';
    public string $data_emissao = '';
    public string $valor_devido = '';
    public string $valor_pago = '';
    public string $contraparte_id = '';
    public string $tipo_doc_fiscal = '';
    public string $num_doc_fiscal = '';
    public string $descricao = '';
    public string $data_evento = '';
    public string $contrato_ref_id = '';
    public string $plano_contas_id = '';
    public string $cashflow_cat = '';
    public string $cashflow_subcat = '';
    public string $caixa_referencia = '';
    public string $competencia_ref = '';
    public string $meio_pagamento = '';
    public string $info_favorecido = '';
    public string $observacoes = '';

    protected $listeners = ['conta-created' => '$refresh', 'conta-updated' => '$refresh'];

    #[Computed]
    public function contas()
    {
        return ContasAPagar::query()
            ->when($this->search, fn($q) => $q->where('descricao', 'like', "%{$this->search}%")
                ->orWhere('conta_origem', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status_pagamento', $this->filterStatus))
            ->when($this->filterAno, fn($q) => $q->where('ano_vencimento', $this->filterAno))
            ->when($this->filterMes, fn($q) => $q->where('mes_vencimento', $this->filterMes))
            ->when($this->filterCashflow, fn($q) => $q->where('cashflow_cat', $this->filterCashflow))
            ->when($this->filterContraparte, fn($q) => $q->where('contraparte_id', $this->filterContraparte))
            ->with(['contraparte:id,razao_social', 'contratoRef:id,codigo_contrato'])
            ->orderByDesc('data_devida')
            ->paginate(15);
    }

    #[Computed]
    public function contrapartes()
    {
        return Entidade::query()->orderBy('razao_social')->get(['id', 'razao_social']);
    }

    #[Computed]
    public function planoContas()
    {
        return NomenclaturaConfig::query()->byTipo('Plano de Contas')->ativo()->orderBy('nome')->get(['id', 'nome', 'codigo']);
    }

    #[Computed]
    public function cashflowCategorias()
    {
        return NomenclaturaConfig::query()->byTipo('Cashflow')->ativo()->orderBy('nome')->get(['id', 'nome']);
    }

    #[Computed]
    public function anosDisponiveis(): array
    {
        $current = (int) date('Y');
        return array_map(fn($y) => ['id' => $y, 'label' => (string) $y], range($current - 2, $current + 1));
    }

    #[Computed]
    public function mesesDisponiveis(): array
    {
        return array_map(fn($m) => ['id' => $m, 'label' => sprintf('%02d - %s', $m, ucfirst(strftime('%B', mktime(0,0,0,$m,1))))], range(1, 12));
    }

    public function resetFilters(): void
    {
        [$this->search, $this->filterStatus, $this->filterCashflow, $this->filterContraparte] = ['', '', '', ''];
        $this->filterAno = 0;
        $this->filterMes = 0;
    }

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(ContasAPagar $conta): void
    {
        $this->resetValidation();
        $this->conta = $conta;
        $this->ano_vencimento = (string) $conta->ano_vencimento;
        $this->mes_vencimento = (string) $conta->mes_vencimento;
        $this->status_pagamento = $conta->status_pagamento;
        $this->conta_origem = $conta->conta_origem;
        $this->data_devida = $conta->data_devida?->format('Y-m-d') ?? '';
        $this->data_pagamento = $conta->data_pagamento?->format('Y-m-d') ?? '';
        $this->data_emissao = $conta->data_emissao?->format('Y-m-d') ?? '';
        $this->valor_devido = (string) $conta->valor_devido;
        $this->valor_pago = (string) ($conta->valor_pago ?? 0);
        $this->contraparte_id = (string) $conta->contraparte_id;
        $this->tipo_doc_fiscal = $conta->tipo_doc_fiscal ?? '';
        $this->num_doc_fiscal = $conta->num_doc_fiscal ?? '';
        $this->descricao = $conta->descricao ?? '';
        $this->data_evento = $conta->data_evento?->format('Y-m-d') ?? '';
        $this->contrato_ref_id = (string) ($conta->contrato_ref_id ?? '');
        $this->plano_contas_id = $conta->plano_contas_id ?? '';
        $this->cashflow_cat = $conta->cashflow_cat ?? '';
        $this->cashflow_subcat = $conta->cashflow_subcat ?? '';
        $this->caixa_referencia = $conta->caixa_referencia?->format('Y-m-d') ?? '';
        $this->competencia_ref = $conta->competencia_ref?->format('Y-m-d') ?? '';
        $this->meio_pagamento = $conta->meio_pagamento ?? '';
        $this->info_favorecido = $conta->info_favorecido ?? '';
        $this->observacoes = $conta->observacoes ?? '';
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $data = $this->validate([
            'ano_vencimento' => 'required|integer|min:2020|max:2100',
            'mes_vencimento' => 'required|integer|min:1|max:12',
            'status_pagamento' => 'required|in:pendente,processando,pago',
            'conta_origem' => 'required|string|max:100',
            'data_devida' => 'required|date',
            'data_pagamento' => 'nullable|date',
            'data_emissao' => 'nullable|date',
            'valor_devido' => 'required|numeric|min:0',
            'valor_pago' => 'nullable|numeric|min:0',
            'contraparte_id' => 'required|exists:entidades,id',
            'tipo_doc_fiscal' => 'nullable|string|max:100',
            'num_doc_fiscal' => 'nullable|string|max:100',
            'descricao' => 'nullable|string',
            'data_evento' => 'nullable|date',
            'contrato_ref_id' => 'nullable|exists:contratos,id',
            'plano_contas_id' => 'required|string|max:100',
            'cashflow_cat' => 'nullable|string|max:100',
            'cashflow_subcat' => 'nullable|string|max:100',
            'caixa_referencia' => 'nullable|date',
            'competencia_ref' => 'nullable|date',
            'meio_pagamento' => 'nullable|string|max:50',
            'info_favorecido' => 'nullable|string',
            'observacoes' => 'nullable|string',
        ]);

        $data['conciliado_status'] = 'N';

        if ($this->isEditing) {
            $this->conta->update($data);
            $this->dispatch('conta-updated');
            $this->notify('Conta atualizada.');
        } else {
            ContasAPagar::create($data);
            $this->dispatch('conta-created');
            $this->notify('Conta criada.');
        }

        $this->closeModal();
    }

    private function resetForm(): void
    {
        $this->conta = null;
        $this->ano_vencimento = (string) date('Y');
        $this->mes_vencimento = (string) date('m');
        $this->status_pagamento = 'pendente';
        $this->conta_origem = '';
        $this->data_devida = '';
        $this->data_pagamento = '';
        $this->data_emissao = '';
        $this->valor_devido = '';
        $this->valor_pago = '';
        $this->contraparte_id = '';
        $this->tipo_doc_fiscal = '';
        $this->num_doc_fiscal = '';
        $this->descricao = '';
        $this->data_evento = '';
        $this->contrato_ref_id = '';
        $this->plano_contas_id = '';
        $this->cashflow_cat = '';
        $this->cashflow_subcat = '';
        $this->caixa_referencia = '';
        $this->competencia_ref = '';
        $this->meio_pagamento = '';
        $this->info_favorecido = '';
        $this->observacoes = '';
    }

    private function notify(string $message): void
    {
        $this->dispatch('notify', ['message' => $message, 'type' => 'success']);
    }

    public function render()
    {
        return view('livewire.payable.index');
    }
}