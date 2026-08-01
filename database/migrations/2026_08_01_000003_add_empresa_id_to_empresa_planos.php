<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('empresa_planos') || Schema::hasColumn('empresa_planos', 'empresa_id')) {
            return;
        }

        Schema::table('empresa_planos', function (Blueprint $table): void {
            $table->foreignId('empresa_id')->nullable()->after('id')->constrained('empresas')->nullOnDelete();
        });

        if (Schema::hasTable('empresa_plano_pagamentos')) {
            DB::statement(
                'UPDATE empresa_planos ep
                 JOIN empresa_plano_pagamentos epp ON epp.empresa_plano_id = ep.id
                 SET ep.empresa_id = epp.empresa_id
                 WHERE ep.empresa_id IS NULL AND epp.empresa_id IS NOT NULL'
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('empresa_planos') || ! Schema::hasColumn('empresa_planos', 'empresa_id')) {
            return;
        }

        Schema::table('empresa_planos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('empresa_id');
        });
    }
};
