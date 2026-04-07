<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('empresas') || Schema::hasColumn('empresas', 'nfse_ultima_importacao_em')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table) {
            $table->timestamp('nfse_ultima_importacao_em')->nullable()->after('nfse_tomador_bairro_padrao');
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('empresas') || !Schema::hasColumn('empresas', 'nfse_ultima_importacao_em')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('nfse_ultima_importacao_em');
        });
        Schema::enableForeignKeyConstraints();
    }
};
