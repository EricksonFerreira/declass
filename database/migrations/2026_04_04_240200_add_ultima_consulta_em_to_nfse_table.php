<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('nfse') || Schema::hasColumn('nfse', 'ultima_consulta_em')) {
            return;
        }

        Schema::table('nfse', function (Blueprint $table) {
            $table->timestamp('ultima_consulta_em')->nullable()->after('cancelada_em');
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('nfse') || !Schema::hasColumn('nfse', 'ultima_consulta_em')) {
            return;
        }

        Schema::table('nfse', function (Blueprint $table) {
            $table->dropColumn('ultima_consulta_em');
        });
        Schema::enableForeignKeyConstraints();
    }
};
