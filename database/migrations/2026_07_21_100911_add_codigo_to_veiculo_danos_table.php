<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculo_danos', function (Blueprint $table) {
            $table->unsignedBigInteger('reserva_codigo_id')->nullable()->after('veiculo_id');
            $table->foreign('reserva_codigo_id')->references('id')->on('reserva_codigos')->nullOnDelete();
            $table->index('reserva_codigo_id', 'veiculo_danos_reserva_codigo_id_idx');
        });

        DB::statement('UPDATE veiculo_danos d
            JOIN reserva_codigos rc ON rc.reserva_id = d.reserva_id
            SET d.reserva_codigo_id = rc.id
            WHERE d.reserva_codigo_id IS NULL');
    }

    public function down(): void
    {
        Schema::table('veiculo_danos', function (Blueprint $table) {
            $table->dropForeign('veiculo_danos_reserva_codigo_id_foreign');
            $table->dropIndex('veiculo_danos_reserva_codigo_id_idx');
            $table->dropColumn('reserva_codigo_id');
        });
    }
};
