<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reservas_motorista_app')) {
            Schema::table('reservas_motorista_app', function (Blueprint $table): void {
                if (!Schema::hasColumn('reservas_motorista_app', 'valor_caucao')) {
                    $table->decimal('valor_caucao', 10, 2)->nullable()->after('valor_opcionais');
                }
            });

            try {
                DB::statement("
                    UPDATE reservas_motorista_app r
                    INNER JOIN planos_motorista_app p ON r.plano_motorista_app_id = p.id
                    SET r.valor_caucao = p.calcao
                    WHERE r.valor_caucao IS NULL OR r.valor_caucao = 0
                ");
            } catch (\Throwable $e) {
                // Ignore if fallback migration data backfill encounters no records
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reservas_motorista_app')) {
            Schema::table('reservas_motorista_app', function (Blueprint $table): void {
                if (Schema::hasColumn('reservas_motorista_app', 'valor_caucao')) {
                    $table->dropColumn('valor_caucao');
                }
            });
        }
    }
};
