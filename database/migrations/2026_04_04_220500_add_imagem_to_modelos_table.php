<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('modelos') || Schema::hasColumn('modelos', 'imagem')) {
            return;
        }

        Schema::table('modelos', function (Blueprint $table) {
            $table->string('imagem')->nullable()->after('nome');
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('modelos') || !Schema::hasColumn('modelos', 'imagem')) {
            return;
        }

        Schema::table('modelos', function (Blueprint $table) {
            $table->dropColumn('imagem');
        });
        Schema::enableForeignKeyConstraints();
    }
};
