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
        Schema::create('contas_a_pagar', function (Blueprint $table) {
            $table->id();
            $table->char('conciliado_status', 1)->nullable(); // "R" = Conciliado
            $table->integer('ano_vencimento');
            $table->integer('mes_vencimento');
            $table->string('status_pagamento', 50)->default('pendente'); // pendente, processando, pago
            $table->string('conta_origem', 100);
            $table->date('data_devida');
            $table->date('data_pagamento')->nullable();
            $table->date('data_emissao')->nullable();
            $table->decimal('valor_devido', 15, 2);
            $table->decimal('valor_pago', 15, 2)->default(0.00);
            $table->foreignId('contraparte_id')->constrained('entidades');
            $table->string('tipo_doc_fiscal', 100)->nullable();
            $table->string('num_doc_fiscal', 100)->nullable();
            $table->text('descricao')->nullable();
            $table->date('data_evento')->nullable();
            $table->foreignId('contrato_ref_id')->nullable()->constrained('contratos')->nullOnDelete();
            $table->string('plano_contas_id', 100);
            $table->string('cashflow_cat', 100)->nullable();
            $table->string('cashflow_subcat', 100)->nullable();
            $table->date('caixa_referencia')->nullable();
            $table->date('competencia_ref')->nullable();
            $table->string('meio_pagamento', 50)->nullable();
            $table->text('info_favorecido')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_a_pagar');
    }
};
