<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('movimentacoes')) {
            return;
        }

        Schema::table('movimentacoes', function (Blueprint $table) {
            if (! Schema::hasColumn('movimentacoes', 'gera_disponibilidade')) {
                $table->boolean('gera_disponibilidade')->default(false)->after('data_chegada_real');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('movimentacoes')) {
            return;
        }

        Schema::table('movimentacoes', function (Blueprint $table) {
            if (Schema::hasColumn('movimentacoes', 'gera_disponibilidade')) {
                $table->dropColumn('gera_disponibilidade');
            }
        });
    }
};
