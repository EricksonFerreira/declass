<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adiciona empresa_id na tabela correta (o model usa horarios_diarios)
        $table = Schema::hasTable('horarios_diarios') ? 'horarios_diarios' : 'horario_diarios';

        if (Schema::hasColumn($table, 'empresa_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable()->after('id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        foreach (['horarios_diarios', 'horario_diarios'] as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'empresa_id')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->dropForeign(['empresa_id']);
                    $table->dropColumn('empresa_id');
                });
                break;
            }
        }
    }
};
