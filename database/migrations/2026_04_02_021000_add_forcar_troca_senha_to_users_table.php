<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'forcar_troca_senha')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->boolean('forcar_troca_senha')->default(false)->after('acesso_todas_estacoes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'forcar_troca_senha')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('forcar_troca_senha');
            });
        }
    }
};
