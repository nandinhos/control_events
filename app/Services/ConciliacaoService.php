<?php

namespace App\Services;

use App\Models\ExtratoBancarioTransacao;
use App\Models\ContasAReceber;
use App\Models\ContasAPagar;
use App\Models\ConciliacaoBancariaLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ConciliacaoService
{
    /**
     * Import transactions from OFX content or CSV array
     * Returns array with imported count and errors
     */
    public function importOfx(string $content, string $contaBancaria): array
    {
        $imported = 0;
        $errors = [];

        // Parse OFX simple format (STMTTRN blocks)
        preg_match_all('/<STMTTRN>(.*?)<\/STMTTRN>/is', $content, $blocks);
        foreach ($blocks[1] ?? [] as $block) {
            $date = $this->extractOfxTag($block, 'DTPOSTED');
            $amount = $this->extractOfxTag($block, 'TRNAMT');
            $memo = $this->extractOfxTag($block, 'MEMO') ?? $this->extractOfxTag($block, 'NAME') ?? '';
            $type = $this->extractOfxTag($block, 'TRNTYPE') ?? 'OTHER';
            $fitid = $this->extractOfxTag($block, 'FITID') ?? '';

            if (!$date || !$amount) {
                $errors[] = "Bloco STMTTRN incompleto, pulando";
                continue;
            }

            $parsedDate = $this->parseOfxDate($date);
            $hash = $this->generateHash($contaBancaria, $parsedDate, (float) $amount, $fitid);

            // Skip duplicates
            if (ExtratoBancarioTransacao::where('hash_unico', $hash)->exists()) {
                $errors[] = "Transacao duplicada via hash, pulando: {$fitid}";
                continue;
            }

            ExtratoBancarioTransacao::create([
                'conta_bancaria' => $contaBancaria,
                'data_transacao' => $parsedDate,
                'descricao' => trim($memo),
                'valor' => (float) $amount,
                'tipo' => $this->mapTipoTransacao($type, (float) $amount),
                'hash_unico' => $hash,
                'conciliado_status' => null,
                'data_importacao' => now(),
            ]);

            $imported++;
        }

        return ['imported' => $imported, 'errors' => $errors, 'total' => $imported + count($errors)];
    }

    /**
     * Import from Livewire TemporaryUploadedFile (CSV)
     */
    public function importCsvFromFile($file, string $contaBancaria): array
    {
        $path = $file->getRealPath();
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle, 0, ';');
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                $row = array_combine($headers, $data);
                $rows[] = $row;
            }
            fclose($handle);
        }
        return $this->importCsv($rows, $contaBancaria);
    }

    /**
     * Import from CSV array (expected headers: data, valor, descricao, conta_bancaria)
     */
    public function importCsv(array $rows, string $contaBancaria): array
    {
        $imported = 0;
        $errors = [];

        foreach ($rows as $idx => $row) {
            try {
                if (empty($row['data']) || empty($row['valor'])) {
                    $errors[] = "Linha {$idx}: data ou valor ausentes";
                    continue;
                }

                $parsedDate = $this->parseCsvDate($row['data']);
                $amount = (float) str_replace([',', 'R$', ' '], ['', '', ''], $row['valor']);
                $descricao = $row['descricao'] ?? '';
                $hash = $this->generateHash($contaBancaria, $parsedDate, $amount, $idx);

                if (ExtratoBancarioTransacao::where('hash_unico', $hash)->exists()) {
                    $errors[] = "Linha {$idx}: duplicada";
                    continue;
                }

                ExtratoBancarioTransacao::create([
                    'conta_bancaria' => $contaBancaria,
                    'data_transacao' => $parsedDate,
                    'descricao' => $descricao,
                    'valor' => $amount,
                    'tipo' => $this->mapTipoTransacao(null, $amount),
                    'hash_unico' => $hash,
                    'conciliado_status' => null,
                    'data_importacao' => now(),
                ]);

                $imported++;
            } catch (\Throwable $e) {
                $errors[] = "Linha {$idx}: " . $e->getMessage();
            }
        }

        return ['imported' => $imported, 'errors' => $errors, 'total' => $imported + count($errors)];
    }

    /**
     * Auto-match transactions by hash or amount+date+description similarity
     */
    public function autoMatch(string $contaBancaria): array
    {
        $matched = 0;
        $transacoes = ExtratoBancarioTransacao::byContaBancaria($contaBancaria)->pendente()->get();

        foreach ($transacoes as $transacao) {
            $absValor = abs((float) $transacao->valor);

            // Try to match with ContasAReceber
            $receber = ContasAReceber::where(function ($q) use ($transacao) {
                $q->whereRaw('ABS(valor_previsto - ?)', [abs((float) $transacao->valor)])
                  ->whereDate('vencimento_atual', $transacao->data_transacao);
            })->whereDoesntHave('conciliacaoLinks', function ($q) use ($transacao) {
                $q->whereHas('transacao', fn($tq) => $tq->where('id', '!=', $transacao->id));
            })->first();

            if ($receber) {
                $this->createLink($transacao, $receber, $receber->tipo_lancamento, 'auto');
                $matched++;
                continue;
            }

            // Try to match with ContasAPagar
            $pagar = ContasAPagar::where(function ($q) use ($transacao) {
                $q->whereRaw('ABS(valor_devido - ?)', [abs((float) $transacao->valor)])
                  ->whereDate('data_devida', $transacao->data_transacao);
            })->whereDoesntHave('conciliacaoLinks', function ($q) use ($transacao) {
                $q->whereHas('transacao', fn($tq) => $tq->where('id', '!=', $transacao->id));
            })->first();

            if ($pagar) {
                $this->createLink($transacao, $pagar, 'ContasAPagar', 'auto');
                $matched++;
            }
        }

        return ['matched' => $matched];
    }

    /**
     * Manual link a transacao to a lancamento
     */
    public function manualLink(
        int $transacaoId,
        string $lancamentoType,
        int $lancamentoId,
        ?float $valorConciliado = null
    ): ?ConciliacaoBancariaLink {
        $transacao = ExtratoBancarioTransacao::find($transacaoId);
        if (!$transacao) return null;

        $link = ConciliacaoBancariaLink::create([
            'extrato_bancario_transacao_id' => $transacao->id,
            'tipo_lancamento' => $lancamentoType,
            'lancamento_id' => $lancamentoId,
            'lancamento_type' => $lancamentoType === 'ContasAReceber' ? ContasAReceber::class : ContasAPagar::class,
            'valor_conciliado' => $valorConciliado ?? abs((float) $transacao->valor),
        ]);

        $transacao->update(['conciliado_status' => 'R']);

        return $link;
    }

    /**
     * Unlink a conciliacao
     */
    public function unlink(int $linkId): void
    {
        $link = ConciliacaoBancariaLink::find($linkId);
        if (!$link) return;

        $link->transacao()->update(['conciliado_status' => null]);
        $link->delete();
    }

    /**
     * Mark transacao as manually excluded fromconciliation
     */
    public function exclude(int $transacaoId): void
    {
        ExtratoBancarioTransacao::find($transacaoId)?->update(['conciliado_status' => 'E']);
    }

    // =====================================================
    // PRIVATE HELPERS
    // =====================================================

    private function generateHash(string $conta, $date, float $valor, string $ref): string
    {
        return hash('sha256', "{$conta}|{$date}|{$valor}|{$ref}");
    }

    private function extractOfxTag(string $block, string $tag): ?string
    {
        if (preg_match("/<{$tag}>(.+?)(?=<[A-Z]|$)/is", $block, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function parseOfxDate(string $date): string
    {
        // OFX date format: YYYYMMDD or YYYYMMDDHHMMSS
        $date = preg_replace('/[^0-9]/', '', $date);
        if (strlen($date) >= 8) {
            $y = substr($date, 0, 4);
            $m = substr($date, 4, 2);
            $d = substr($date, 6, 2);
            return "{$y}-{$m}-{$d}";
        }
        return date('Y-m-d');
    }

    private function parseCsvDate(string $date): string
    {
        $date = trim($date);
        // Try DD/MM/YYYY
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        // Try YYYY-MM-DD already
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        return date('Y-m-d');
    }

    private function mapTipoTransacao(?string $ofxType, float $amount): string
    {
        if ($amount > 0) return 'credito';
        if ($amount < 0) return 'debito';
        return 'ajuste';
    }

    private function createLink(
        ExtratoBancarioTransacao $transacao,
        $lancamento,
        string $lancamentoType,
        string $matchType
    ): ConciliacaoBancariaLink {
        $link = ConciliacaoBancariaLink::create([
            'extrato_bancario_transacao_id' => $transacao->id,
            'tipo_lancamento' => $lancamentoType,
            'lancamento_id' => $lancamento->id,
            'lancamento_type' => $lancamentoType === 'ContasAReceber' ? ContasAReceber::class : ContasAPagar::class,
            'valor_conciliado' => abs((float) $transacao->valor),
        ]);

        $transacao->update(['conciliado_status' => 'R']);

        return $link;
    }
}