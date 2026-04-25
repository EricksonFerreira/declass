<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('planos') && ! Schema::hasColumn('planos', 'limite_permissoes_por_regra')) {
            Schema::table('planos', function (Blueprint $table) {
                $table->unsignedInteger('limite_permissoes_por_regra')
                    ->nullable()
                    ->after('limite_usuarios');
            });
        }

        if (! Schema::hasTable('plano_permissao')) {
            Schema::create('plano_permissao', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('plano_id');
                $table->unsignedBigInteger('permission_id');
                $table->timestamps();

                $table->unique(['plano_id', 'permission_id'], 'plano_permissao_unique');
                $table->foreign('plano_id')->references('id')->on('planos')->onDelete('cascade');
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('plano_permissao')) {
            Schema::dropIfExists('plano_permissao');
        }

        if (Schema::hasTable('planos') && Schema::hasColumn('planos', 'limite_permissoes_por_regra')) {
            Schema::table('planos', function (Blueprint $table) {
                $table->dropColumn('limite_permissoes_por_regra');
            });
        }
    }
};
