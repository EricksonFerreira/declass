<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'acesso_todas_estacoes')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->boolean('acesso_todas_estacoes')->default(true)->after('empresa_id');
            });
        }

        if (!Schema::hasTable('estacao_user')) {
            Schema::create('estacao_user', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('estacao_id');
                $table->timestamps();

                $table->unique(['user_id', 'estacao_id']);
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('estacao_id')->references('id')->on('estacoes')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('estacao_user')) {
            Schema::drop('estacao_user');
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'acesso_todas_estacoes')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('acesso_todas_estacoes');
            });
        }
    }
};
