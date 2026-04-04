<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transacoes') || Schema::hasColumn('transacoes', 'pago_por_user_id')) {
            return;
        }

        Schema::table('transacoes', function (Blueprint $table) {
            $table->unsignedBigInteger('pago_por_user_id')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('transacoes') || !Schema::hasColumn('transacoes', 'pago_por_user_id')) {
            return;
        }

        Schema::table('transacoes', function (Blueprint $table) {
            $table->dropColumn('pago_por_user_id');
        });
    }
};
