<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planos', function (Blueprint $table): void {
            if (! Schema::hasColumn('planos', 'valor_veiculo_adicional')) {
                $table->decimal('valor_veiculo_adicional', 10, 2)->nullable()->after('valor_anual');
            }

            if (! Schema::hasColumn('planos', 'quantidade_filiais')) {
                $table->integer('quantidade_filiais')->nullable()->after('valor_veiculo_adicional');
            }

            if (! Schema::hasColumn('planos', 'valor_filial_adicional')) {
                $table->decimal('valor_filial_adicional', 10, 2)->nullable()->after('quantidade_filiais');
            }

            if (! Schema::hasColumn('planos', 'valor_armazenamento_adicional')) {
                $table->decimal('valor_armazenamento_adicional', 10, 2)->nullable()->after('valor_filial_adicional');
            }

            if (! Schema::hasColumn('planos', 'quantidade_armazenamento_adicional')) {
                $table->integer('quantidade_armazenamento_adicional')->nullable()->after('valor_armazenamento_adicional');
            }
        });
    }

    public function down(): void
    {
        Schema::table('planos', function (Blueprint $table): void {
            foreach ([
                'quantidade_armazenamento_adicional',
                'valor_armazenamento_adicional',
                'valor_filial_adicional',
                'quantidade_filiais',
                'valor_veiculo_adicional',
            ] as $column) {
                if (Schema::hasColumn('planos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
