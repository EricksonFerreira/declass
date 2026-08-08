<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            if (!Schema::hasColumn('veiculos', 'documento_fiscal_id')) {
                $table->foreignId('documento_fiscal_id')->nullable()->constrained('documentos_fiscais')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            if (Schema::hasColumn('veiculos', 'documento_fiscal_id')) {
                $table->dropForeign(['documento_fiscal_id']);
                $table->dropColumn('documento_fiscal_id');
            }
        });
    }
};
