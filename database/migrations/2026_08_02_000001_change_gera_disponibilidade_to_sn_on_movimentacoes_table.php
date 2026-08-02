<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('movimentacoes') || !Schema::hasColumn('movimentacoes', 'gera_disponibilidade')) {
            return;
        }

        DB::table('movimentacoes')
            ->whereIn('gera_disponibilidade', [1, '1', true, 'true', 'T', 't'])
            ->update(['gera_disponibilidade' => 'S']);

        DB::table('movimentacoes')
            ->whereIn('gera_disponibilidade', [0, '0', false, 'false', null, '', 'N', 'n'])
            ->update(['gera_disponibilidade' => 'N']);

        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->char('gera_disponibilidade', 1)->default('N')->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('movimentacoes') || !Schema::hasColumn('movimentacoes', 'gera_disponibilidade')) {
            return;
        }

        DB::table('movimentacoes')
            ->where('gera_disponibilidade', 'S')
            ->update(['gera_disponibilidade' => 1]);

        DB::table('movimentacoes')
            ->where('gera_disponibilidade', 'N')
            ->update(['gera_disponibilidade' => 0]);

        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->boolean('gera_disponibilidade')->default(false)->change();
        });
    }
};
