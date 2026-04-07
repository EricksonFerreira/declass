<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('grupos_frota')) {
            return;
        }

        Schema::table('grupos_frota', function (Blueprint $table) {
            if (!Schema::hasColumn('grupos_frota', 'ordem')) {
                $table->unsignedSmallInteger('ordem')->default(0)->after('ativo')
                    ->comment('Hierarquia de categoria para upgrade: maior = melhor');
            }
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('grupos_frota')) {
            return;
        }

        Schema::table('grupos_frota', function (Blueprint $table) {
            if (Schema::hasColumn('grupos_frota', 'ordem')) {
                $table->dropColumn('ordem');
            }
        });
        Schema::enableForeignKeyConstraints();
    }
};
