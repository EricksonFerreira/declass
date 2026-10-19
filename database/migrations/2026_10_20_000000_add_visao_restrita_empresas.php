<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('empresas') || Schema::hasColumn('empresas', 'visao_restrita')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table) {
            $table->boolean('visao_restrita')->default(true)->after('tags_por_projeto');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('empresas') || ! Schema::hasColumn('empresas', 'visao_restrita')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('visao_restrita');
        });
    }
};
