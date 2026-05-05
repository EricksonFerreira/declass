<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculo_danos', function (Blueprint $table) {
            $table->unsignedBigInteger('contrato_id')->nullable()->after('reserva_id');
        });
    }

    public function down(): void
    {
        Schema::table('veiculo_danos', function (Blueprint $table) {
            $table->dropColumn('contrato_id');
        });
    }
};
