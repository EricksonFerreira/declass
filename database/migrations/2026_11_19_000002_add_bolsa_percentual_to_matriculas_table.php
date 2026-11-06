<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bolsa/desconto por matricula: percentual (0-100) abatido do valor
 * do plano de mensalidade na geracao das cobrancas e na rematricula.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->decimal('bolsa_percentual', 5, 2)->nullable()->after('plano_mensalidade_id');
        });
    }

    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropColumn('bolsa_percentual');
        });
    }
};
