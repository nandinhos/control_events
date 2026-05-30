<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nomenclatura_configs', function (Blueprint $table) {
            $table->string('nome', 100)->after('id');
            $table->string('tipo', 50)->after('nome'); // Caixa, Cashflow, Plano de Contas, Conta Bancaria
            $table->string('codigo', 50)->nullable()->after('tipo');
            $table->text('descricao')->nullable()->after('codigo');
            $table->unsignedBigInteger('categoria_pai_id')->nullable()->after('descricao');
            $table->boolean('ativo')->default(true)->after('categoria_pai_id');
            $table->json('parametros_extras')->nullable()->after('ativo');

            $table->foreign('categoria_pai_id')
                  ->references('id')
                  ->on('nomenclatura_configs')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('nomenclatura_configs', function (Blueprint $table) {
            $table->dropForeign(['categoria_pai_id']);
            $table->dropColumn([
                'nome', 'tipo', 'codigo', 'descricao',
                'categoria_pai_id', 'ativo', 'parametros_extras',
            ]);
        });
    }
};
