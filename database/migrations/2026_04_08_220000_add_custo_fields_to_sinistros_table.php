<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sinistros', function (Blueprint $table) {
            $table->enum('destino_custo', ['empresa', 'cliente', 'ambos'])->default('empresa')->after('valor_responsabilidade_cliente');
            $table->decimal('valor_custo_empresa', 10, 2)->nullable()->after('destino_custo');
        });
    }

    public function down(): void
    {
        Schema::table('sinistros', function (Blueprint $table) {
            $table->dropColumn(['destino_custo', 'valor_custo_empresa']);
        });
    }
};
