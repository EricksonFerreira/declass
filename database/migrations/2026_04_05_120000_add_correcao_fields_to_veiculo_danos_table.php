<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculo_danos', function (Blueprint $table) {
            if (!Schema::hasColumn('veiculo_danos', 'correcao_descricao')) {
                $table->text('correcao_descricao')->nullable();
            }
            if (!Schema::hasColumn('veiculo_danos', 'correcao_data')) {
                $table->timestamp('correcao_data')->nullable();
            }
            if (!Schema::hasColumn('veiculo_danos', 'correcao_user_id')) {
                $table->unsignedBigInteger('correcao_user_id')->nullable();
            }
        });

    }

    public function down(): void
    {
        Schema::table('veiculo_danos', function (Blueprint $table) {
            $table->dropColumn(['correcao_descricao', 'correcao_data', 'correcao_user_id']);
        });
    }
};
