<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Idempotente: um deploy que criou a coluna mas falhou no indice
        // nao deve travar a proxima rodada de migrations.
        if (! Schema::hasColumn('users', 'usuario')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('usuario', 60)->nullable()->after('email');
            });
        }

        if (! Schema::hasIndex('users', 'users_empresa_usuario_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique(['empresa_id', 'usuario'], 'users_empresa_usuario_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_empresa_usuario_unique');
            $table->dropColumn('usuario');
        });
    }
};
