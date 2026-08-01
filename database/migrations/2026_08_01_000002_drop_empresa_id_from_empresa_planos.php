<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('empresa_planos') || ! Schema::hasColumn('empresa_planos', 'empresa_id')) {
            return;
        }

        Schema::table('empresa_planos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('empresa_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('empresa_planos') || Schema::hasColumn('empresa_planos', 'empresa_id')) {
            return;
        }

        Schema::table('empresa_planos', function (Blueprint $table): void {
            $table->foreignId('empresa_id')->nullable()->after('id')->constrained('empresas')->nullOnDelete();
        });
    }
};
