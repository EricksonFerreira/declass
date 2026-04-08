<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reserva_logs', function (Blueprint $table) {
            $table->string('acao', 120)->nullable()->after('reserva_id');
            $table->enum('nivel', ['info', 'success', 'warning', 'danger'])->default('info')->after('acao');
        });
    }

    public function down(): void
    {
        Schema::table('reserva_logs', function (Blueprint $table) {
            $table->dropColumn(['acao', 'nivel']);
        });
    }
};
