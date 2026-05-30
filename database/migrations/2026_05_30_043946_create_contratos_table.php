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
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_contrato', 50)->unique();
            $table->date('semana_inicio');
            $table->string('status_booking', 50); // ex: Confirmado, Cancelado
            $table->foreignId('booker_id')->constrained('entidades');
            $table->string('agencia_sigla', 20); // PAN, Coral, MZK
            $table->string('assinatura_status', 50); // Draft, Aguardando Assinatura, Em Execução, Concluído
            $table->date('data_venda')->nullable();
            $table->date('data_evento');
            $table->foreignId('artista_id')->constrained('entidades');
            $table->decimal('valor_bruto', 15, 2);
            $table->char('moeda', 3)->default('BRL');
            $table->decimal('comissao_valor', 15, 2)->nullable();
            $table->string('local_evento')->nullable();
            $table->string('nome_evento');
            $table->string('cidade_evento', 100)->nullable();
            $table->char('estado_evento', 2)->nullable();
            $table->string('regiao_evento', 50)->nullable();
            $table->string('pais_evento', 100)->default('Brasil');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
