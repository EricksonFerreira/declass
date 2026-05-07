<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planos', function (Blueprint $table) {
            if (!Schema::hasColumn('planos', 'limite_armazenamento_mb')) {
                $table->integer('limite_armazenamento_mb')->nullable()->after('limite_veiculos');
            }
        });

        DB::table('planos')->whereNull('limite_armazenamento_mb')->update(['limite_armazenamento_mb' => 1024]);
    }

    public function down(): void
    {
        Schema::table('planos', function (Blueprint $table) {
            $table->dropColumn('limite_armazenamento_mb');
        });
    }
};
