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
        Schema::table('clockify_projetos', function (Blueprint $table) {
            $table->decimal('horas_orcadas', 8, 2)->nullable()->after('publico');
            $table->decimal('valor_hora', 10, 2)->nullable()->after('horas_orcadas');
            $table->date('data_entrega_previsao')->nullable()->after('valor_hora');
            $table->decimal('horas_diarias_planejamento', 5, 2)->default(8.00)->nullable()->after('data_entrega_previsao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clockify_projetos', function (Blueprint $table) {
            $table->dropColumn([
                'horas_orcadas',
                'valor_hora',
                'data_entrega_previsao',
                'horas_diarias_planejamento',
            ]);
        });
    }
};
