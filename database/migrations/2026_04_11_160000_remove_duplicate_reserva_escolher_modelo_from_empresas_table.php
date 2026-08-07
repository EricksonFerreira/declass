<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (!Schema::hasTable('empresas')) {
            return;
        }

        if (
            Schema::hasColumn('empresas', 'reserva_escolher_modelo')
            && Schema::hasColumn('empresas', 'reserva_selecionar_modelo')
        ) {
            DB::table('empresas')
                ->where('reserva_escolher_modelo', 'S')
                ->update(['reserva_selecionar_modelo' => 'S']);

            Schema::table('empresas', function (Blueprint $table) {
                $table->dropColumn('reserva_escolher_modelo');
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('empresas') && !Schema::hasColumn('empresas', 'reserva_escolher_modelo')) {
            Schema::table('empresas', function (Blueprint $table) {
                $table->char('reserva_escolher_modelo', 1)->default('N')->after('reserva_escolher_empresa');
            });

            DB::table('empresas')
                ->where('reserva_selecionar_modelo', 'S')
                ->update(['reserva_escolher_modelo' => 'S']);
        }

        Schema::enableForeignKeyConstraints();
    }
};
