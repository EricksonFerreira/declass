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
            if (!Schema::hasColumn('movimentacoes', 'km_saida')) {
                $table->unsignedInteger('km_saida')->nullable();
            }
            if (!Schema::hasColumn('movimentacoes', 'km_chegada')) {
                $table->unsignedInteger('km_chegada')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('movimentacoes')) {
            return;
        }

        Schema::table('movimentacoes', function (Blueprint $table) {
            if (Schema::hasColumn('movimentacoes', 'km_saida')) {
                $table->dropColumn('km_saida');
            }
            if (Schema::hasColumn('movimentacoes', 'km_chegada')) {
                $table->dropColumn('km_chegada');
            }
        });
    }
};
