<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('modelos') && !Schema::hasColumn('modelos', 'ano_modelo')) {
            Schema::table('modelos', function (Blueprint $table) {
                $table->unsignedSmallInteger('ano_modelo')->nullable()->after('nome_fipe');
            });
        }

        if (Schema::hasTable('modelos') && Schema::hasTable('veiculos') && Schema::hasColumn('veiculos', 'ano_modelo')) {
            $anosPorModelo = DB::table('veiculos')
                ->whereNotNull('modelo_id')
                ->whereNotNull('ano_modelo')
                ->selectRaw('modelo_id, MAX(ano_modelo) as ano_modelo')
                ->groupBy('modelo_id')
                ->pluck('ano_modelo', 'modelo_id');

            foreach ($anosPorModelo as $modeloId => $anoModelo) {
                DB::table('modelos')
                    ->where('id', (int) $modeloId)
                    ->update(['ano_modelo' => (int) $anoModelo]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('modelos') && Schema::hasColumn('modelos', 'ano_modelo')) {
            Schema::table('modelos', function (Blueprint $table) {
                $table->dropColumn('ano_modelo');
            });
        }
    }
};
