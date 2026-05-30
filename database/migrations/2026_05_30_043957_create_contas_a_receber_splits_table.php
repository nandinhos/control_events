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
        Schema::create('contas_a_receber_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_a_receber_id')->constrained('contas_a_receber')->cascadeOnDelete();
            $table->string('destino', 50); // Panorama, Coral, Artista, MovimentacaoInterna
            $table->decimal('valor', 15, 2);
            $table->string('observacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_a_receber_splits');
    }
};