<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table) {
            if (! Schema::hasColumn('empresas', 'representante')) {
                $table->string('representante', 255)->nullable()->after('telefone');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table) {
            if (Schema::hasColumn('empresas', 'representante')) {
                $table->dropColumn('representante');
            }
        });
    }
};
