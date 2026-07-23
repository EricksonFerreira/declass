<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contrato_checkout', function (Blueprint $table) {
            if (!Schema::hasColumn('contrato_checkout', 'pdf_path')) {
                $table->string('pdf_path', 500)->nullable()->after('data_assinatura');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contrato_checkout', function (Blueprint $table) {
            if (Schema::hasColumn('contrato_checkout', 'pdf_path')) {
                $table->dropColumn('pdf_path');
            }
        });
    }
};
