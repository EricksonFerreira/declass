<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('estacoes', 'valor_taxa_coleta')) {
                $table->decimal('valor_taxa_coleta', 10, 2)->nullable()->after('cobrar_na_coleta')
                    ->comment('Valor da taxa fora do expediente cobrada na coleta');
            }
            if (!Schema::hasColumn('estacoes', 'valor_taxa_entrega')) {
                $table->decimal('valor_taxa_entrega', 10, 2)->nullable()->after('cobrar_na_entrega')
                    ->comment('Valor da taxa fora do expediente cobrada na entrega');
            }
        });
    }

    public function down(): void
    {
        Schema::table('estacoes', function (Blueprint $table) {
            $table->dropColumnIfExists(['valor_taxa_coleta', 'valor_taxa_entrega']);
        });
    }
};
