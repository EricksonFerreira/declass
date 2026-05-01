<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('status_veiculos')) {
            return;
        }

        Schema::table('status_veiculos', function (Blueprint $table) {
            if (!Schema::hasColumn('status_veiculos', 'cor')) {
                $table->string('cor', 30)->default('secondary')->after('nome');
            }

            if (!Schema::hasColumn('status_veiculos', 'ativo')) {
                $table->string('ativo', 1)->default('S')->after('cor');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('status_veiculos')) {
            return;
        }

        Schema::table('status_veiculos', function (Blueprint $table) {
            if (Schema::hasColumn('status_veiculos', 'ativo')) {
                $table->dropColumn('ativo');
            }

            if (Schema::hasColumn('status_veiculos', 'cor')) {
                $table->dropColumn('cor');
            }
        });
    }
};
