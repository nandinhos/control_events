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
        Schema::create('contas_a_receber', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->nullable()->constrained('contratos')->nullOnDelete();
            $table->date('mes_base');
            $table->foreignId('booker_id')->constrained('entidades');
            $table->string('status_booking', 50);
            $table->string('registro_contabil', 100); // Panorama, Coral, Artista (Movimentação Interna)
            $table->date('data_evento');
            $table->foreignId('artista_id')->constrained('entidades');
            $table->string('nome_evento');
            $table->foreignId('contratante_id')->constrained('entidades');
            $table->string('tipo_lancamento', 50); // Booking, Extra Contratual
            $table->string('parcela_numero', 10); // ex: "1/2"
            $table->decimal('valor_previsto', 15, 2);
            $table->date('vencimento_original');
            $table->date('vencimento_atual');
            $table->string('status_pagamento', 50)->default('aberto'); // aberto, pago
            $table->integer('aging_dias')->default(0);
            $table->date('data_pagamento')->nullable();
            $table->decimal('valor_recebido', 15, 2)->default(0.00);
            $table->decimal('valor_principal', 15, 2)->default(0.00);
            $table->decimal('juros_multas', 15, 2)->default(0.00);
            $table->string('cashflow_categoria', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_a_receber');
    }
};
