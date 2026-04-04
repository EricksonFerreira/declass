<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('grupos_frota') || Schema::hasColumn('grupos_frota', 'imagens')) {
            return;
        }

        Schema::table('grupos_frota', function (Blueprint $table) {
            $table->json('imagens')->nullable()->after('imagem');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('grupos_frota') || !Schema::hasColumn('grupos_frota', 'imagens')) {
            return;
        }

        Schema::table('grupos_frota', function (Blueprint $table) {
            $table->dropColumn('imagens');
        });
    }
};
