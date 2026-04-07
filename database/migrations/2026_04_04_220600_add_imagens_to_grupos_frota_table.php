<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('grupos_frota') || Schema::hasColumn('grupos_frota', 'imagens')) {
            return;
        }

        Schema::table('grupos_frota', function (Blueprint $table) {
            $table->json('imagens')->nullable()->after('imagem');
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('grupos_frota') || !Schema::hasColumn('grupos_frota', 'imagens')) {
            return;
        }

        Schema::table('grupos_frota', function (Blueprint $table) {
            $table->dropColumn('imagens');
        });
        Schema::enableForeignKeyConstraints();
    }
};
