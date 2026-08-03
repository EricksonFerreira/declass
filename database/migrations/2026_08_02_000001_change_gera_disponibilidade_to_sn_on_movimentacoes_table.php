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

        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->char('gera_disponibilidade', 1)->default('N')->change();
        });

        DB::table('movimentacoes')
            ->where('gera_disponibilidade', 1)
            ->update(['gera_disponibilidade' => 'S']);

        DB::table('movimentacoes')
            ->where(function ($query) {
                $query->where('gera_disponibilidade', 0)
                    ->orWhereNull('gera_disponibilidade');
            })
            ->update(['gera_disponibilidade' => 'N']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('movimentacoes') || !Schema::hasColumn('movimentacoes', 'gera_disponibilidade')) {
            return;
        }

        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->boolean('gera_disponibilidade')->default(false)->change();
        });

        DB::table('movimentacoes')
            ->where('gera_disponibilidade', 'S')
            ->update(['gera_disponibilidade' => 1]);

        DB::table('movimentacoes')
            ->where('gera_disponibilidade', 'N')
            ->update(['gera_disponibilidade' => 0]);
    }
};
