<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reserva_logs')) {
            // Table will be created by a separate migration; nothing to do.
            return;
        }
        Schema::table('reserva_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('reserva_logs', 'acao')) {
                $table->string('acao', 120)->nullable()->after('reserva_id');
            }
            if (!Schema::hasColumn('reserva_logs', 'nivel')) {
                $table->enum('nivel', ['info', 'success', 'warning', 'danger'])->default('info')->after('acao');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reserva_logs', function (Blueprint $table) {
            $table->dropColumn(['acao', 'nivel']);
        });
    }
};
