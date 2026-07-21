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
         * Adiciona a nova referência central.
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
         * Migra registros antigos que possuem reserva_id.
         */
        if (
            Schema::hasColumn('avaria_cobrancas', 'reserva_id')
            && Schema::hasTable('reserva_codigos')
        ) {
            DB::statement('
                UPDATE avaria_cobrancas AS ac
                INNER JOIN reserva_codigos AS rc
                    ON rc.reserva_id = ac.reserva_id
                SET ac.reserva_codigo_id = rc.id
                WHERE ac.reserva_codigo_id IS NULL
                  AND ac.reserva_id IS NOT NULL
            ');
        }

        /*
         * Confere se algum registro antigo ficou sem referência.
         */
        $quantidadeSemCodigo = DB::table('avaria_cobrancas')
            ->whereNull('reserva_codigo_id')
            ->count();

        if ($quantidadeSemCodigo > 0) {
            throw new RuntimeException(
                "Existem {$quantidadeSemCodigo} cobranças de avaria sem reserva_codigo_id."
            );
        }

        /*
         * Remove a chave estrangeira antiga e a coluna reserva_id.
         */
        if (Schema::hasColumn('avaria_cobrancas', 'reserva_id')) {
            Schema::table('avaria_cobrancas', function (Blueprint $table) {
                $foreignKeys = Schema::getForeignKeys(
                    'avaria_cobrancas'
                );

                $possuiForeignReserva = collect($foreignKeys)
                    ->contains(function (array $foreign): bool {
                        return in_array(
                            'reserva_id',
                            $foreign['columns'] ?? [],
                            true
                        );
                    });

                if ($possuiForeignReserva) {
                    $table->dropForeign(['reserva_id']);
                }

                $table->dropColumn('reserva_id');
            });
        }
    }

    public function down(): void
    {
        /*
         * Recria temporariamente a coluna antiga.
         */
        if (! Schema::hasColumn('avaria_cobrancas', 'reserva_id')) {
            Schema::table('avaria_cobrancas', function (Blueprint $table) {
                $table->foreignId('reserva_id')
                    ->nullable()
                    ->after('empresa_id')
                    ->constrained('reservas')
                    ->nullOnDelete();
            });
        }

        /*
         * Restaura reserva_id apenas para reservas comuns.
         */
        if (Schema::hasColumn('avaria_cobrancas', 'reserva_codigo_id')) {
            DB::statement('
                UPDATE avaria_cobrancas AS ac
                INNER JOIN reserva_codigos AS rc
                    ON rc.id = ac.reserva_codigo_id
                SET ac.reserva_id = rc.reserva_id
                WHERE ac.reserva_id IS NULL
                  AND rc.reserva_id IS NOT NULL
            ');

            Schema::table('avaria_cobrancas', function (Blueprint $table) {
                $table->dropForeign(['reserva_codigo_id']);
                $table->dropColumn('reserva_codigo_id');
            });
        }
    }
};
