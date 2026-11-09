<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Boleto anexado a mensalidade pela secretaria: o PDF fica no
 * disco 'public' e a familia baixa pelo portal para pagar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mensalidades', function (Blueprint $table) {
            $table->string('boleto_path')->nullable()->after('observacoes');
            $table->string('boleto_nome')->nullable()->after('boleto_path');
        });
    }

    public function down(): void
    {
        Schema::table('mensalidades', function (Blueprint $table) {
            $table->dropColumn(['boleto_path', 'boleto_nome']);
        });
    }
};
