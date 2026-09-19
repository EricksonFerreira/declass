<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // Cota mensal de tokens de IA (entrada+saída). NULL = ilimitado.
            // Definida pelo superadmin na empresa ou no monitor Uso de IA.
            $table->unsignedBigInteger('ia_limite_tokens')->nullable()->after('licitacoes_ativo');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('ia_limite_tokens');
        });
    }
};
