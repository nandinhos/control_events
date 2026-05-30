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

    public bool $showImportModal = false;
    public $fileImport = null;
    public string $contaBancariaImport = '';
    public string $importTipo = 'ofx';

    public bool $showManualLinkModal = false;
    public ?ExtratoBancarioTransacao $transacaoParaLinkar = null;
    public string $linkLancamentoType = '';
    public string $linkLancamentoId = '';
    public string $linkValor = '';

    protected $listeners = [
        'fileUploaded' => 'handleFileUpload',
        'conciliacao-linked' => '$refresh',
        'conciliacao-unlinked' => '$refresh',
    ];

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
        $receber = ContasAReceber::whereDoesntHave('conciliacaoLinks')
            ->aberto()
            ->selectRaw("id, 'ContasAReceber' as tipo, nome_evento, valor_previsto, vencimento_atual, 'receber' as source")
            ->get()
            ->map(fn($r) => (object) [
                'id' => $r->id,
                'tipo' => 'ContasAReceber',
                'label' => "{$r->nome_evento} | " . number_format($r->valor_previsto, 2, ',', '.') . " | {$r->vencimento_atual?->format('d/m/Y')}",
            ]);

        $pagar = ContasAPagar::whereDoesntHave('conciliacaoLinks')
            ->pendente()
            ->selectRaw("id, 'ContasAPagar' as tipo, descricao, valor_devido, data_devida, 'pagar' as source")
            ->get()
            ->map(fn($p) => (object) [
                'id' => $p->id,
                'tipo' => 'ContasAPagar',
                'label' => ($p->descricao ?: $p->conta_origem) . " | " . number_format($p->valor_devido, 2, ',', '.') . " | {$p->data_devida?->format('d/m/Y')}",
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

    public function resetFilters(): void
    {
        [$this->searchTransacao, $this->filterStatus, $this->filterDataInicio, $this->filterDataFim] = ['', '', null, null];
        $this->filterContaBancaria = '';
    }

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