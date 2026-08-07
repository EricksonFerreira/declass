<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('empresas')) {
            Schema::table('empresas', function (Blueprint $table) {
                if (!Schema::hasColumn('empresas', 'regime_emissao')) {
                    $table->string('regime_emissao', 30)->default('NFSE_APENAS')->after('nfse_ativo');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('empresas')) {
            Schema::table('empresas', function (Blueprint $table) {
                if (Schema::hasColumn('empresas', 'regime_emissao')) {
                    $table->dropColumn('regime_emissao');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
};
