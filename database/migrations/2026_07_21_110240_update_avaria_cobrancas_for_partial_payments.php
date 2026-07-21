<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Referência central para reserva comum ou motorista app.
         */
        if (! Schema::hasColumn('avaria_cobrancas', 'reserva_codigo_id')) {
            Schema::table('avaria_cobrancas', function (Blueprint $table) {
                $table->foreignId('reserva_codigo_id')
                    ->nullable()
                    ->after('empresa_id')
                    ->constrained('reserva_codigos')
                    ->nullOnDelete();
            });
        }

        /*
         * Valores separados por origem da cobrança.
         */
        if (! Schema::hasColumn('avaria_cobrancas', 'valor_calcao')) {
            Schema::table('avaria_cobrancas', function (Blueprint $table) {
                $table->decimal('valor_calcao', 12, 2)
                    ->default(0)
                    ->after('valor');
            });
        }

        if (! Schema::hasColumn('avaria_cobrancas', 'valor_stripe')) {
            Schema::table('avaria_cobrancas', function (Blueprint $table) {
                $table->decimal('valor_stripe', 12, 2)
                    ->default(0)
                    ->after('valor_calcao');
            });
        }

        if (! Schema::hasColumn('avaria_cobrancas', 'valor_pendente')) {
            Schema::table('avaria_cobrancas', function (Blueprint $table) {
                $table->decimal('valor_pendente', 12, 2)
                    ->default(0)
                    ->after('valor_stripe');
            });
        }

        /*
         * Migra cobranças antigas vinculadas diretamente por reserva_id.
         */
        if (
            Schema::hasColumn('avaria_cobrancas', 'reserva_id')
            && Schema::hasTable('reserva_codigos')
        ) {
            DB::statement('
                UPDATE avaria_cobrancas AS ac
                INNER JOIN reserva_codigos AS rc
                    ON rc.reserva_id = ac.reserva_id
                    AND rc.empresa_id = ac.empresa_id
                SET ac.reserva_codigo_id = rc.id
                WHERE ac.reserva_codigo_id IS NULL
            ');
        }

        /*
         * Preenche os valores pendentes das cobranças antigas.
         */
        DB::table('avaria_cobrancas')
            ->whereNull('cobrado_em')
            ->where('valor_pendente', 0)
            ->update([
                'valor_pendente' => DB::raw(
                    'GREATEST(valor - valor_calcao - valor_stripe, 0)'
                ),
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('avaria_cobrancas', 'valor_pendente')) {
            Schema::table('avaria_cobrancas', function (Blueprint $table) {
                $table->dropColumn('valor_pendente');
            });
        }

        if (Schema::hasColumn('avaria_cobrancas', 'valor_stripe')) {
            Schema::table('avaria_cobrancas', function (Blueprint $table) {
                $table->dropColumn('valor_stripe');
            });
        }

        if (Schema::hasColumn('avaria_cobrancas', 'valor_calcao')) {
            Schema::table('avaria_cobrancas', function (Blueprint $table) {
                $table->dropColumn('valor_calcao');
            });
        }

        if (Schema::hasColumn('avaria_cobrancas', 'reserva_codigo_id')) {
            Schema::table('avaria_cobrancas', function (Blueprint $table) {
                $table->dropForeign(['reserva_codigo_id']);
                $table->dropColumn('reserva_codigo_id');
            });
        }
    }
};
