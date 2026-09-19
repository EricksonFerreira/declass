<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Fator de esforço: pesa as horas lançadas no consumo consolidado
            // (estagiário 0.5 conta metade; efetivo 1.0). Distinto de
            // jornada_diaria, que é a capacidade de horas por dia.
            $table->decimal('fator_esforco', 4, 2)->default(1.00)->after('jornada_diaria');
        });

        Schema::table('clockify_projetos', function (Blueprint $table) {
            // Código completo do projeto no formato do cliente:
            // [ano].[numero]-[codigo CC]-[nome] [(status)]
            $table->unsignedSmallInteger('ano')->nullable()->after('codigo');
            $table->unsignedInteger('numero')->nullable()->after('ano');
            $table->index(['empresa_id', 'ano', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('fator_esforco');
        });

        Schema::table('clockify_projetos', function (Blueprint $table) {
            $table->dropIndex(['empresa_id', 'ano', 'numero']);
            $table->dropColumn(['ano', 'numero']);
        });
    }
};
