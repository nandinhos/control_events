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
        Schema::create('entidades', function (Blueprint $table) {
            $table->id();
            $table->string('tags'); // Ex: "Cliente", "Artista", "Fornecedor", "Colaborador"
            $table->string('cnpj_cpf', 20)->unique();
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('endereco')->nullable();
            $table->string('complemento', 100)->nullable();
            $table->string('cep', 10)->nullable();
            $table->string('bairro', 100)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->char('estado', 2)->nullable();
            $table->string('telefone', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 100)->nullable();
            $table->string('inscricao_estadual', 50)->nullable();
            $table->string('banco_nome', 100)->nullable();
            $table->text('banco_agencia')->nullable(); // Criptografado
            $table->text('banco_conta')->nullable(); // Criptografado
            $table->text('chave_pix')->nullable(); // Criptografado
            $table->text('banco_titular_doc')->nullable(); // Criptografado
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entidades');
    }
};
