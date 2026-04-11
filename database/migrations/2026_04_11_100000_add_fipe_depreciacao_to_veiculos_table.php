<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->decimal('valor_compra', 12, 2)->nullable()->after('data_compra');
            $table->decimal('valor_fipe', 12, 2)->nullable()->after('valor_compra');
            $table->string('codigo_fipe', 20)->nullable()->after('valor_fipe');
            $table->string('referencia_fipe', 20)->nullable()->after('codigo_fipe');
            $table->date('data_consulta_fipe')->nullable()->after('referencia_fipe');
            $table->decimal('taxa_depreciacao_anual', 5, 2)->nullable()->default(15.00)->after('data_consulta_fipe');
        });
    }

    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->dropColumn([
                'valor_compra',
                'valor_fipe',
                'codigo_fipe',
                'referencia_fipe',
                'data_consulta_fipe',
                'taxa_depreciacao_anual',
            ]);
        });
    }
};
