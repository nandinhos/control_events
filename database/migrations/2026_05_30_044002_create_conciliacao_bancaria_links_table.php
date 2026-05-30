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
        Schema::create('conciliacao_bancaria_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extrato_bancario_transacao_id')->constrained('extrato_bancario_transacoes')->cascadeOnDelete();
            $table->string('tipo_lancamento', 20); // receber, pagar
            $table->unsignedBigInteger('lancamento_id'); // polymorphic: contas_a_receber_id ou contas_a_pagar_id
            $table->string('lancamento_type', 50); // polymorphic type
            $table->decimal('valor_conciliado', 15, 2);
            $table->timestamps();

            // Indexes
            $table->index(['extrato_bancario_transacao_id']);
            $table->index(['lancamento_type', 'lancamento_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conciliacao_bancaria_links');
    }
};