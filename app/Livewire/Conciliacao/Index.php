<?php

namespace App\Livewire\Conciliacao;

use App\Services\ConciliacaoService;
use App\Models\ExtratoBancarioTransacao;
use App\Models\ConciliacaoBancariaLink;
use App\Models\ContasAReceber;
use App\Models\ContasAPagar;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\TemporaryUploadedFile;
use Illuminate\Support\Facades\Storage;

class Index extends Component
{
    use WithPagination;

    public string $filterContaBancaria = '';
    public string $filterStatus = '';
    public string $searchTransacao = '';
    public ?string $filterDataInicio = null;
    public ?string $filterDataFim = null;
    public ?int $filterArtistaId = null; // HUB-002: Filtro por artista

    public bool $showImportModal = false;
    public TemporaryUploadedFile|null $fileImport = null;
    public string $contaBancariaImport = '';
    public string $importTipo = 'ofx';

    public bool $showManualLinkModal = false;
    public ?ExtratoBancarioTransacao $transacaoParaLinkar = null;
    public string $linkLancamentoType = '';
    public string $linkLancamentoId = '';
    public string $linkValor = '';

    // REC-003: N-para-1 multi-select
    public array $selectedLancamentos = [];
    public ?ExtratoBancarioTransacao $transacaoParaNpara1 = null;
    public float $transacaoParaNpara1Valor = 0.0;

    // REC-004: Quick value adjustment
    public bool $editavel = false;
    public string $editavelId = '';
    public string $editavelType = '';
    public string $editavelValor = '';

    // REC-006: Add new lancamento
    public bool $showNewLancamentoModal = false;
    public string $newLancamentoTipo = 'ContasAReceber';

    // REC-006: Novos campos para lançamento
    public string $newReceivableEvento = '';
    public string $newReceivableValor = '';
    public string $newReceivableVencimento = '';
    public string $newReceivableContrato = '';
    public string $newPayableDescricao = '';
    public string $newPayableValor = '';
    public string $newPayableDataDevida = '';
    public string $newPayableFornecedor = '';

    protected $listeners = [
        'fileUploaded' => 'handleFileUpload',
        'conciliacao-linked' => '$refresh',
        'conciliacao-unlinked' => '$refresh',
    ];

    // =====================================================
    // COMPUTED PROPERTIES
    // =====================================================

    #[Computed]
    public function transacoesBancarias()
    {
        return ExtratoBancarioTransacao::query()
            ->when($this->filterContaBancaria, fn($q) => $q->where('conta_bancaria', $this->filterContaBancaria))
            ->when($this->filterStatus === 'pendente', fn($q) => $q->whereNull('conciliado_status'))
            ->when($this->filterStatus === 'conciliado', fn($q) => $q->where('conciliado_status', 'R'))
            ->when($this->filterStatus === 'excluido', fn($q) => $q->where('conciliado_status', 'E'))
            ->when($this->searchTransacao, fn($q) => $q->where('descricao', 'like', "%{$this->searchTransacao}%"))
            ->when($this->filterDataInicio, fn($q) => $q->whereDate('data_transacao', '>=', $this->filterDataInicio))
            ->when($this->filterDataFim, fn($q) => $q->whereDate('data_transacao', '<=', $this->filterDataFim))
            ->orderByDesc('data_transacao')
            ->paginate(20);
    }

    #[Computed]
    public function lancamentosNaoConciliados()
    {
        // HUB-002: Filtro por artista
        $receberQuery = ContasAReceber::whereDoesntHave('conciliacaoLinks')
            ->aberto();
        if ($this->filterArtistaId) {
            $receberQuery->where('artista_id', $this->filterArtistaId);
        }
        $receber = $receberQuery
            ->selectRaw("id, 'ContasAReceber' as tipo, nome_evento, valor_previsto, vencimento_atual, 'receber' as source")
            ->get()
            ->map(fn($r) => (object) [
                'id' => $r->id,
                'tipo' => 'ContasAReceber',
                'label' => "{$r->nome_evento} | " . number_format($r->valor_previsto, 2, ',', '.') . " | {$r->vencimento_atual?->format('d/m/Y')}",
                'valor' => (float) $r->valor_previsto,
            ]);

        $pagarQuery = ContasAPagar::whereDoesntHave('conciliacaoLinks')
            ->pendente();
        // Payables don't have artista_id directly, would need join through contrato
        $pagar = $pagarQuery
            ->selectRaw("id, 'ContasAPagar' as tipo, descricao, valor_devido, data_devida, 'pagar' as source")
            ->get()
            ->map(fn($p) => (object) [
                'id' => $p->id,
                'tipo' => 'ContasAPagar',
                'label' => ($p->descricao ?: $p->conta_origem) . " | " . number_format($p->valor_devido, 2, ',', '.') . " | {$p->data_devida?->format('d/m/Y')}",
                'valor' => (float) $p->valor_devido,
            ]);

        return $receber->concat($pagar)->sortBy('id')->values();
    }

    #[Computed]
    public function contasBancariasDisponiveis(): array
    {
        return ExtratoBancarioTransacao::query()
            ->select('conta_bancaria')
            ->distinct()
            ->orderBy('conta_bancaria')
            ->pluck('conta_bancaria')
            ->toArray();
    }

    // HUB-002: Artistas para filtro
    #[Computed]
    public function artistasDisponiveis(): array
    {
        return \App\Models\Entidade::query()
            ->whereHas('artistaContratos')
            ->orderBy('razao_social')
            ->pluck('razao_social', 'id')
            ->toArray();
    }

    // REC-003: Soma dos lancamentos selecionados
    #[Computed]
    public function somaSelecionados(): float
    {
        return array_sum(array_map(fn($l) => (float) ($l['valor'] ?? $l->valor ?? 0), $this->selectedLancamentos));
    }

    // REC-003: Diferenca entre soma e valor da transacao
    #[Computed]
    public function diferencaNpara1(): float
    {
        return abs($this->transacaoParaNpara1Valor - $this->somaSelecionados);
    }

    // REC-003: Valida se pode conciliar N-para-1
    public function canConciliarNpara1(): bool
    {
        // REC-005: Só permite confirmar se soma bate exatamente
        return $this->diferencaNpara1 < 0.01;
    }

    // REC-003: Verifica se lançamento está selecionado
    public function isSelected(int $id): bool
    {
        return in_array($id, array_column($this->selectedLancamentos, 'id'));
    }

    // =====================================================
    // ACTIONS
    // =====================================================

    public function resetFilters(): void
    {
        [$this->searchTransacao, $this->filterStatus, $this->filterDataInicio, $this->filterDataFim] = ['', '', null, null];
        $this->filterContaBancaria = '';
    }

    // REC-003: Abrir painel N-para-1 para uma transação
    public function openNpara1(ExtratoBancarioTransacao $transacao): void
    {
        $this->transacaoParaNpara1 = $transacao;
        $this->transacaoParaNpara1Valor = (float) abs($transacao->valor);
        $this->selectedLancamentos = [];
    }

    // REC-006: Criar e vincular novo lançamento
    public function createAndLinkLancamento(): void
    {
        if ($this->newLancamentoTipo === 'ContasAReceber') {
            $this->validate([
                'newReceivableEvento' => 'required|string|max:255',
                'newReceivableValor' => 'required|numeric|min:0',
                'newReceivableVencimento' => 'required|date',
            ]);

            $receivable = ContasAReceber::create([
                'nome_evento' => $this->newReceivableEvento,
                'valor_previsto' => (float) $this->newReceivableValor,
                'vencimento_atual' => $this->newReceivableVencimento,
                'contrato_id' => $this->newReceivableContrato ?: null,
                'status_booking' => 'aberto',
            ]);

            // Vincular automaticamente se há transação N-para-1 selecionada
            if ($this->transacaoParaNpara1) {
                $service = app(ConciliacaoService::class);
                $service->manualLink(
                    $this->transacaoParaNpara1->id,
                    'ContasAReceber',
                    $receivable->id,
                    null
                );
            }

            $this->dispatch('conciliacao-linked');
            $this->notify('Lançamento a Receber criado e vinculado.');
        } else {
            $this->validate([
                'newPayableDescricao' => 'required|string|max:255',
                'newPayableValor' => 'required|numeric|min:0',
                'newPayableDataDevida' => 'required|date',
            ]);

            $payable = ContasAPagar::create([
                'descricao' => $this->newPayableDescricao,
                'valor_devido' => (float) $this->newPayableValor,
                'data_devida' => $this->newPayableDataDevida,
                'fornecedor' => $this->newPayableFornecedor ?: null,
                'status_booking' => 'pendente',
            ]);

            // Vincular automaticamente se há transação N-para-1 selecionada
            if ($this->transacaoParaNpara1) {
                $service = app(ConciliacaoService::class);
                $service->manualLink(
                    $this->transacaoParaNpara1->id,
                    'ContasAPagar',
                    $payable->id,
                    null
                );
            }

            $this->dispatch('conciliacao-linked');
            $this->notify('Lançamento a Pagar criado e vinculado.');
        }

        $this->closeNewLancamentoModal();
        $this->resetNewLancamentoFields();
    }

    private function resetNewLancamentoFields(): void
    {
        $this->newReceivableEvento = '';
        $this->newReceivableValor = '';
        $this->newReceivableVencimento = '';
        $this->newReceivableContrato = '';
        $this->newPayableDescricao = '';
        $this->newPayableValor = '';
        $this->newPayableDataDevida = '';
        $this->newPayableFornecedor = '';
    }

    // REC-003: Toggle selecao de lancamento
    public function toggleLancamento(array $lancamento): void
    {
        $key = array_search($lancamento['id'], array_column($this->selectedLancamentos, 'id'));
        if ($key !== false) {
            array_splice($this->selectedLancamentos, $key, 1);
        } else {
            $this->selectedLancamentos[] = $lancamento;
        }
    }

    public function clearSelection(): void
    {
        $this->selectedLancamentos = [];
    }

    // REC-003: Selecionar todos visiveis
    public function selectAllVisible(): void
    {
        $this->selectedLancamentos = array_map(
            fn($l) => (array) $l,
            $this->lancamentosNaoConciliados
        );
    }

    // REC-003: Conciliar N-para-1
    public function conciliarNpara1(): void
    {
        if (!$this->transacaoParaNpara1 || !$this->canConciliarNpara1()) {
            $this->dispatch('notify', [
                'message' => 'A soma dos títulos selecionados deve bater com o valor da transação.',
                'type' => 'error',
            ]);
            return;
        }

        $service = app(ConciliacaoService::class);
        foreach ($this->selectedLancamentos as $lancamento) {
            $service->manualLink(
                $this->transacaoParaNpara1->id,
                $lancamento['tipo'],
                $lancamento['id'],
                null
            );
        }

        $this->dispatch('notify', [
            'message' => count($this->selectedLancamentos) . ' lançamentos conciliados com sucesso.',
            'type' => 'success',
        ]);
        $this->selectedLancamentos = [];
        $this->dispatch('conciliacao-linked');
    }

    // REC-004: Habilitar edicao inline
    public function enableEdit(array $lancamento): void
    {
        $this->editavel = true;
        $this->editavelId = (string) $lancamento['id'];
        $this->editavelType = $lancamento['tipo'];
        $this->editavelValor = (string) $lancamento['valor'];
    }

    public function saveInlineEdit(): void
    {
        if (!$this->editavelId) return;

        $valor = (float) $this->editavelValor;

        if ($this->editavelType === 'ContasAReceber') {
            $model = ContasAReceber::find($this->editavelId);
        } else {
            $model = ContasAPagar::find($this->editavelId);
        }

        if ($model) {
            // Registrar histórico de auditoria
            $oldValor = $this->editavelType === 'ContasAReceber' ? $model->valor_previsto : $model->valor_devido;
            \Log::info("Quick adjustment: {$this->editavelType} #{$this->editavelId} valor alterado de {$oldValor} para {$valor}");

            if ($this->editavelType === 'ContasAReceber') {
                $model->valor_previsto = $valor;
            } else {
                $model->valor_devido = $valor;
            }
            $model->save();
        }

        $this->editavel = false;
        $this->dispatch('notify', ['message' => 'Valor ajustado.', 'type' => 'success']);
    }

    public function cancelInlineEdit(): void
    {
        $this->editavel = false;
    }

    // REC-006: Abrir modal para adicionar lancamento
    public function openNewLancamentoModal(string $tipo = 'ContasAReceber'): void
    {
        $this->newLancamentoTipo = $tipo;
        $this->showNewLancamentoModal = true;
    }

    public function closeNewLancamentoModal(): void
    {
        $this->showNewLancamentoModal = false;
    }

    // =====================================================
    // IMPORT
    // =====================================================

    public function openImportModal(): void
    {
        $this->fileImport = null;
        $this->contaBancariaImport = '';
        $this->importTipo = 'ofx';
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
    }

    public function importFile(): void
    {
        $this->validate([
            'fileImport' => 'required|file',
            'contaBancariaImport' => 'required|string|max:100',
        ]);

        if (!$this->fileImport) {
            $this->addError('fileImport', 'Selecione um arquivo para importar.');
            return;
        }

        $content = file_get_contents($this->fileImport->getRealPath());
        $service = app(ConciliacaoService::class);

        if ($this->importTipo === 'ofx') {
            $result = $service->importOfx($content, $this->contaBancariaImport);
        } else {
            $result = $service->importCsvFromFile($this->fileImport, $this->contaBancariaImport);
        }

        $msg = "Importadas {$result['imported']} transacoes.";
        if (!empty($result['errors'])) {
            $msg .= " {$result['errors']} erros.";
        }

        $this->dispatch('notify', ['message' => $msg, 'type' => 'success']);
        $this->closeImportModal();
    }

    // =====================================================
    // MANUAL LINK (single)
    // =====================================================

    public function openManualLink(ExtratoBancarioTransacao $transacao): void
    {
        $this->transacaoParaLinkar = $transacao;
        $this->linkLancamentoType = '';
        $this->linkLancamentoId = '';
        $this->linkValor = (string) abs((float) $transacao->valor);
        $this->showManualLinkModal = true;
    }

    public function closeManualLink(): void
    {
        $this->showManualLinkModal = false;
    }

    public function saveManualLink(): void
    {
        if (!$this->transacaoParaLinkar || !$this->linkLancamentoId) return;

        $service = app(ConciliacaoService::class);
        $service->manualLink(
            $this->transacaoParaLinkar->id,
            $this->linkLancamentoType,
            (int) $this->linkLancamentoId,
            (float) $this->linkValor
        );

        $this->dispatch('conciliacao-linked');
        $this->notify('Link criado com sucesso.');
        $this->closeManualLink();
    }

    public function runAutoMatch(string $contaBancaria): void
    {
        $service = app(ConciliacaoService::class);
        $result = $service->autoMatch($contaBancaria);

        $this->dispatch('notify', [
            'message' => "Auto-match encontrou {$result['matched']} correspondencias.",
            'type' => 'success',
        ]);
    }

    public function unlink(ConciliacaoBancariaLink $link): void
    {
        $service = app(ConciliacaoService::class);
        $service->unlink($link->id);
        $this->dispatch('conciliacao-unlinked');
        $this->notify('Link removido.', 'warning');
    }

    public function excludeTransacao(ExtratoBancarioTransacao $transacao): void
    {
        $service = app(ConciliacaoService::class);
        $service->exclude($transacao->id);
        $this->notify('Transacao marcada como excluida.', 'warning');
    }

    private function notify(string $message, string $type = 'success'): void
    {
        $this->dispatch('notify', ['message' => $message, 'type' => $type]);
    }

    public function render()
    {
        return view('livewire.conciliacao.index');
    }
}