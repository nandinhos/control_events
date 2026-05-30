<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('extrato_bancario_transacoes', function (Blueprint $table) {
            $table->id();
            $table->string('conta_bancaria', 100); // Bradesco, Itau, etc.
            $table->date('data_transacao');
            $table->string('descricao', 255);
            $table->decimal('valor', 15, 2); // positivo=credito, negativo=debito
            $table->string('tipo', 20); // credito, debito
            $table->decimal('saldo_resultante', 15, 2)->nullable();
            $table->string('hash_unico', 64)->unique(); // previne duplicacao de importacao
            $table->char('conciliado_status', 1)->nullable()->default(null); // null=pendente, R=conciliado
            $table->timestamp('data_importacao')->useCurrent();
            $table->timestamps();

            // Indexes para performance
            $table->index(['conta_bancaria', 'data_transacao']);
            $table->index('conciliado_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extrato_bancario_transacoes');
    }
};