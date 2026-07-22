<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            if (! Schema::hasColumn('ordens_servico', 'valor_pecas')) {
                $table->decimal('valor_pecas', 12, 2)->default(0)->after('relatorio_conclusao');
            }

            if (! Schema::hasColumn('ordens_servico', 'valor_mao_obra')) {
                $table->decimal('valor_mao_obra', 12, 2)->default(0)->after('valor_pecas');
            }

            if (! Schema::hasColumn('ordens_servico', 'valor_total')) {
                $table->decimal('valor_total', 12, 2)->default(0)->after('valor_mao_obra');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            foreach (['valor_total', 'valor_mao_obra', 'valor_pecas'] as $column) {
                if (Schema::hasColumn('ordens_servico', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
