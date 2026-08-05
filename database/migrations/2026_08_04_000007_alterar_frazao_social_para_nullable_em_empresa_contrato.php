<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('empresa_contratos')) {
            return;
        }

        if (! Schema::hasColumn('empresa_contratos', 'razao_social')) {
            return;
        }

        Schema::table('empresa_contratos', function (Blueprint $table) {
            $table->string('razao_social', 255)
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('empresa_contratos')) {
            return;
        }

        if (! Schema::hasColumn('empresa_contratos', 'razao_social')) {
            return;
        }

        Schema::table('empresa_contratos', function (Blueprint $table) {
            $table->string('razao_social', 255)
                ->nullable(false)
                ->change();
        });
    }
};