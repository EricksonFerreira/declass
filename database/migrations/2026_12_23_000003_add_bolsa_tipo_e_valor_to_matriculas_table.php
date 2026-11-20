<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bolsa/desconto por matricula em duas modalidades (padrao de
 * sistemas escolares): percentual (bolsa_percentual, ja existente)
 * ou valor fixo em R$ abatido de cada mensalidade (bolsa_valor).
 * bolsa_tipo registra qual modalidade esta ativa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->string('bolsa_tipo', 15)->nullable()->after('bolsa_percentual');
            $table->decimal('bolsa_valor', 10, 2)->nullable()->after('bolsa_tipo');
        });
    }

    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropColumn(['bolsa_tipo', 'bolsa_valor']);
        });
    }
};
