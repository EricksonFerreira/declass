<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $tabelas = [
        'estacao_user',
        'estacoes',
    ];

    public function up(): void
    {
        // Remove FKs de tabelas MANTIDAS que apontam para estacoes/estacao_user
        // (ex.: reservas.estacao_retirada_id, movimentacoes.local_origem_id).
        $fksOrfas = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->selectRaw('TABLE_NAME as tabela, CONSTRAINT_NAME as constraint_name')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->whereIn('REFERENCED_TABLE_NAME', $this->tabelas)
            ->whereNotIn('TABLE_NAME', $this->tabelas)
            ->get();

        foreach ($fksOrfas as $fk) {
            Schema::table($fk->tabela, function (Blueprint $table) use ($fk): void {
                $table->dropForeign($fk->constraint_name);
            });
        }

        Schema::disableForeignKeyConstraints();

        foreach ($this->tabelas as $tabela) {
            Schema::dropIfExists($tabela);
        }

        Schema::enableForeignKeyConstraints();

        if (Schema::hasColumn('users', 'acesso_todas_estacoes')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('acesso_todas_estacoes');
            });
        }

        if (Schema::hasColumn('planos', 'multi_estacao')) {
            Schema::table('planos', function (Blueprint $table): void {
                $table->dropColumn('multi_estacao');
            });
        }

        // Coluna reaproveitada: passa a marcar "primeiro registro de horas".
        if (Schema::hasColumn('onboarding_steps', 'step_estacao_cadastrada')
            && ! Schema::hasColumn('onboarding_steps', 'step_primeiro_registro')) {
            Schema::table('onboarding_steps', function (Blueprint $table): void {
                $table->renameColumn('step_estacao_cadastrada', 'step_primeiro_registro');
            });
        }
    }

    public function down(): void
    {
        // Irreversivel: as tabelas removidas nao sao recriadas.
    }
};
