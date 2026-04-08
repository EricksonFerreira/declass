<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('movimentacoes')) {
            return;
        }

        Schema::table('movimentacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('movimentacoes', 'ativo')) {
                $table->string('ativo', 1)->default('S');
            }
            if (!Schema::hasColumn('movimentacoes', 'nome')) {
                $table->string('nome', 100)->nullable()->after('ativo');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('movimentacoes')) {
            return;
        }

        Schema::table('movimentacoes', function (Blueprint $table) {
            if (Schema::hasColumn('movimentacoes', 'ativo')) {
                $table->dropColumn('ativo');
            }
        });
    }
};
