<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lancamentos_financeiros')) {
            return;
        }

        Schema::table('lancamentos_financeiros', function (Blueprint $table) {
            if (!Schema::hasColumn('lancamentos_financeiros', 'data_lancamento')) {
                $table->dateTime('data_lancamento')->nullable()->after('categoria_financeira_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('lancamentos_financeiros')) {
            return;
        }

        Schema::table('lancamentos_financeiros', function (Blueprint $table) {
            if (Schema::hasColumn('lancamentos_financeiros', 'data_lancamento')) {
                $table->dropColumn('data_lancamento');
            }
        });
    }
};
