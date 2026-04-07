<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('caracteristicas_frotas') && !Schema::hasColumn('caracteristicas_frotas', 'imagem')) {
            Schema::table('caracteristicas_frotas', function (Blueprint $table) {
                $table->string('imagem')->nullable();
    });
        }

        if (Schema::hasTable('caracteristica_frotas') && !Schema::hasColumn('caracteristica_frotas', 'imagem')) {
            Schema::table('caracteristica_frotas', function (Blueprint $table) {
                $table->string('imagem')->nullable();
            });
        }
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('caracteristicas_frotas') && Schema::hasColumn('caracteristicas_frotas', 'imagem')) {
            Schema::table('caracteristicas_frotas', function (Blueprint $table) {
                $table->dropColumn('imagem');
    });
        }

        if (Schema::hasTable('caracteristica_frotas') && Schema::hasColumn('caracteristica_frotas', 'imagem')) {
            Schema::table('caracteristica_frotas', function (Blueprint $table) {
                $table->dropColumn('imagem');
            });
        }
        Schema::enableForeignKeyConstraints();
    }
};
