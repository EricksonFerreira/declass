<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('veiculo_danos')) {
            return;
        }

        Schema::table('veiculo_danos', function (Blueprint $table) {
            if (Schema::hasColumn('veiculo_danos', 'quilometragem_veiculo')) {
                $table->integer('quilometragem_veiculo')->nullable()->default(null)->change();
            }

            if (Schema::hasColumn('veiculo_danos', 'valor')) {
                $table->decimal('valor', 10, 2)->nullable()->default(null)->change();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('veiculo_danos')) {
            return;
        }

        Schema::table('veiculo_danos', function (Blueprint $table) {
            if (Schema::hasColumn('veiculo_danos', 'quilometragem_veiculo')) {
                $table->integer('quilometragem_veiculo')->nullable(false)->default(0)->change();
            }

            if (Schema::hasColumn('veiculo_danos', 'valor')) {
                $table->decimal('valor', 10, 2)->nullable(false)->default(0)->change();
            }
        });
    }
};
