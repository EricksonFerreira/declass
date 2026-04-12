<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            if (!Schema::hasColumn('reservas', 'taxa_coleta')) {
                $table->decimal('taxa_coleta', 10, 2)->nullable()->default(null)->after('valor_total_final')
                    ->comment('Taxa fora do expediente aplicada na coleta');
            }
            if (!Schema::hasColumn('reservas', 'taxa_entrega')) {
                $table->decimal('taxa_entrega', 10, 2)->nullable()->default(null)->after('taxa_coleta')
                    ->comment('Taxa fora do expediente aplicada na entrega');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumnIfExists(['taxa_coleta', 'taxa_entrega']);
        });
    }
};
