<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table): void {
            if (!Schema::hasColumn('empresas', 'site_apresentacao_modelo')) {
                $table->string('site_apresentacao_modelo', 30)
                    ->default('modelo_1')
                    ->after('site_apresentacao_ativo');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('empresas') || !Schema::hasColumn('empresas', 'site_apresentacao_modelo')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table): void {
            $table->dropColumn('site_apresentacao_modelo');
        });
    }
};
