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
        // ContasAReceber - campos de multimoeda
        Schema::table('contas_a_receber', function (Blueprint $table) {
            $table->decimal('valor_brl', 15, 2)->default(0)->after('valor_previsto');
            $table->decimal('valor_usd', 15, 2)->default(0)->after('valor_brl');
            $table->decimal('valor_eur', 15, 2)->default(0)->after('valor_usd');
            $table->decimal('valor_gbp', 15, 2)->default(0)->after('valor_eur');
            $table->string('moeda_original', 3)->default('BRL')->after('valor_gbp');
            $table->decimal('taxa_cambio', 10, 6)->nullable()->after('moeda_original');
            $table->string('tipo_cambio', 20)->nullable()->after('taxa_cambio'); // 'oficial', 'manual'
            $table->unsignedBigInteger('taxa_cambio_user_id')->nullable()->after('tipo_cambio');
        });

        // ContasAPagar - campos de multimoeda
        Schema::table('contas_a_pagar', function (Blueprint $table) {
            $table->decimal('valor_brl', 15, 2)->default(0)->after('valor_devido');
            $table->decimal('valor_usd', 15, 2)->default(0)->after('valor_brl');
            $table->decimal('valor_eur', 15, 2)->default(0)->after('valor_usd');
            $table->decimal('valor_gbp', 15, 2)->default(0)->after('valor_eur');
            $table->string('moeda_original', 3)->default('BRL')->after('valor_gbp');
            $table->decimal('taxa_cambio', 10, 6)->nullable()->after('moeda_original');
            $table->string('tipo_cambio', 20)->nullable()->after('taxa_cambio');
            $table->unsignedBigInteger('taxa_cambio_user_id')->nullable()->after('tipo_cambio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contas_a_receber', function (Blueprint $table) {
            $table->dropColumn([
                'valor_brl', 'valor_usd', 'valor_eur', 'valor_gbp',
                'moeda_original', 'taxa_cambio', 'tipo_cambio', 'taxa_cambio_user_id'
            ]);
        });

        Schema::table('contas_a_pagar', function (Blueprint $table) {
            $table->dropColumn([
                'valor_brl', 'valor_usd', 'valor_eur', 'valor_gbp',
                'moeda_original', 'taxa_cambio', 'tipo_cambio', 'taxa_cambio_user_id'
            ]);
        });
    }
};
