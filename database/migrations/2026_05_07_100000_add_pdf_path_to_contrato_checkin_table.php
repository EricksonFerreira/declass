<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contrato_checkin', function (Blueprint $table) {
            if (!Schema::hasColumn('contrato_checkin', 'pdf_path')) {
                $table->string('pdf_path')->nullable()->after('assinatura');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contrato_checkin', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
        });
    }
};
