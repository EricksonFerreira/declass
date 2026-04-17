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
        if (Schema::hasTable('veiculos') && Schema::hasColumn('veiculos', 'ano_modelo')) {
            Schema::table('veiculos', function (Blueprint $table) {
                $table->dropColumn('ano_modelo');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('veiculos') && !Schema::hasColumn('veiculos', 'ano_modelo')) {
            Schema::table('veiculos', function (Blueprint $table) {
                $table->unsignedSmallInteger('ano_modelo')->nullable()->after('ano_fabricacao');
            });
        }
    }
};
