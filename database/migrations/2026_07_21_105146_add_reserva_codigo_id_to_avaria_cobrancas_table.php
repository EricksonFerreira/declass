<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avaria_cobrancas', function (Blueprint $table) {
            $table->foreignId('reserva_codigo_id')
                ->nullable()
                ->after('empresa_id')
                ->constrained('reserva_codigos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('avaria_cobrancas', function (Blueprint $table) {
            $table->dropForeign(['reserva_codigo_id']);
            $table->dropColumn('reserva_codigo_id');
        });
    }
};
