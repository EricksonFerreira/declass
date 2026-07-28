<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentacoes_veiculo', function (Blueprint $table) {
            if (!Schema::hasColumn('documentacoes_veiculo', 'nome')) {
                $table->string('nome')->after('veiculo_id');
            }

            if (!Schema::hasColumn('documentacoes_veiculo', 'tipo')) {
                $table->enum('tipo', ['ipva', 'licenciamento', 'seguro'])->after('veiculo_id');
            }

            if (!Schema::hasColumn('documentacoes_veiculo', 'descricao')) {
                $table->text('descricao')->nullable()->after('tipo');
            }

            if (!Schema::hasColumn('documentacoes_veiculo', 'parcelas_total')) {
                $table->integer('parcelas_total')->default(1)->after('descricao');
            }

            if (!Schema::hasColumn('documentacoes_veiculo', 'valor_parcela')) {
                $table->decimal('valor_parcela', 15, 2)->default(0)->after('parcelas_total');
            }

            if (!Schema::hasColumn('documentacoes_veiculo', 'status')) {
                $table->string('status')->default('ativo')->after('valor_parcela');
            }

            if (!Schema::hasColumn('documentacoes_veiculo', 'categoria_financeira_id')) {
                $table->foreignId('categoria_financeira_id')
                    ->nullable()
                    ->constrained('categorias_financeiras')
                    ->nullOnDelete()
                    ->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documentacoes_veiculo', function (Blueprint $table) {
            if (Schema::hasColumn('documentacoes_veiculo', 'categoria_financeira_id')) {
                $table->dropConstrainedForeignId('categoria_financeira_id');
            }

            foreach (['status', 'valor_parcela', 'parcelas_total', 'descricao', 'tipo', 'nome'] as $column) {
                if (Schema::hasColumn('documentacoes_veiculo', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
