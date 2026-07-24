<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('veiculo_danos')) {
            return;
        }

        Schema::table('veiculo_danos', function (Blueprint $table) {
            $table->decimal('custo_estimado', 10, 2)->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('veiculo_danos')) {
            return;
        }

        Schema::table('veiculo_danos', function (Blueprint $table) {
            $table->decimal('custo_estimado', 10, 2)->nullable(false)->default(null)->change();
        });
    }
};
