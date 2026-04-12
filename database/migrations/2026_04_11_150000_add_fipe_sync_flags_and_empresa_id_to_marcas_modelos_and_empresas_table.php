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

        if (Schema::hasTable('empresas')) {
            Schema::table('empresas', function (Blueprint $table) {
                if (!Schema::hasColumn('empresas', 'adicionar_novas_marcas_automaticamente')) {
                    $table->char('adicionar_novas_marcas_automaticamente', 1)->default('S')->after('reserva_bloqueio_pos_reserva_horas');
                }

                if (!Schema::hasColumn('empresas', 'adicionar_novos_modelos_automaticamente')) {
                    $table->char('adicionar_novos_modelos_automaticamente', 1)->default('S')->after('adicionar_novas_marcas_automaticamente');
                }
            });

            DB::table('empresas')
                ->whereNull('adicionar_novas_marcas_automaticamente')
                ->orWhere('adicionar_novas_marcas_automaticamente', '')
                ->update(['adicionar_novas_marcas_automaticamente' => 'S']);

            DB::table('empresas')
                ->whereNull('adicionar_novos_modelos_automaticamente')
                ->orWhere('adicionar_novos_modelos_automaticamente', '')
                ->update(['adicionar_novos_modelos_automaticamente' => 'S']);
        }

        if (Schema::hasTable('marcas')) {
            Schema::table('marcas', function (Blueprint $table) {
                if (!Schema::hasColumn('marcas', 'empresa_id')) {
                    $table->unsignedBigInteger('empresa_id')->default(1)->after('id');
                    $table->index('empresa_id');
                }
            });

            DB::table('marcas')
                ->whereNull('empresa_id')
                ->update(['empresa_id' => 1]);

            if (Schema::hasTable('empresas') && Schema::hasColumn('marcas', 'empresa_id')) {
                Schema::table('marcas', function (Blueprint $table) {
                    $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
                });
            }
        }

        if (Schema::hasTable('modelos')) {
            Schema::table('modelos', function (Blueprint $table) {
                if (!Schema::hasColumn('modelos', 'empresa_id')) {
                    $table->unsignedBigInteger('empresa_id')->default(1)->after('id');
                    $table->index('empresa_id');
                }
            });

            DB::table('modelos')
                ->whereNull('empresa_id')
                ->update(['empresa_id' => 1]);

            if (Schema::hasTable('empresas') && Schema::hasColumn('modelos', 'empresa_id')) {
                Schema::table('modelos', function (Blueprint $table) {
                    $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
                });
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('modelos') && Schema::hasColumn('modelos', 'empresa_id')) {
            Schema::table('modelos', function (Blueprint $table) {
                $table->dropForeign(['empresa_id']);
                $table->dropIndex(['empresa_id']);
                $table->dropColumn('empresa_id');
            });
        }

        if (Schema::hasTable('marcas') && Schema::hasColumn('marcas', 'empresa_id')) {
            Schema::table('marcas', function (Blueprint $table) {
                $table->dropForeign(['empresa_id']);
                $table->dropIndex(['empresa_id']);
                $table->dropColumn('empresa_id');
            });
        }

        if (Schema::hasTable('empresas')) {
            Schema::table('empresas', function (Blueprint $table) {
                if (Schema::hasColumn('empresas', 'adicionar_novos_modelos_automaticamente')) {
                    $table->dropColumn('adicionar_novos_modelos_automaticamente');
                }

                if (Schema::hasColumn('empresas', 'adicionar_novas_marcas_automaticamente')) {
                    $table->dropColumn('adicionar_novas_marcas_automaticamente');
                }
            });
        }

        Schema::enableForeignKeyConstraints();
    }
};
