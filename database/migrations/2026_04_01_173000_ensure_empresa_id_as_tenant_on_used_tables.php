<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabelas utilizadas na aplicacao que devem ter empresa_id.
     *
     * @var string[]
     */
    private array $tables = [
        'users',
        'permissions',
        'roles',
        'termo_consentimento',
        'caracteristicas_frotas',
        'caracteristica_grupo',
        'caracteristica_grupo_frota',
        'categoria_danos',
        'checklists',
        'configuracao_checklist',
        'contrato_checkin',
        'cupom_desconto',
        'danos',
        'dias_uteis',
        'disponibilidade_inicial_veiculo',
        'estacoes',
        'fora_de_hora',
        'frotas',
        'frota_caracteristica',
        'grupos_frota',
        'horarios',
        'horarios_diarios',
        'item_checklist',
        'marcas',
        'modelos',
        'movimentacoes',
        'opcionais',
        'opcional_grupo_veiculo',
        'paises',
        'reservas',
        'segmentos',
        'station',
        'status_item_checklist',
        'status_movimentacao',
        'status_reserva',
        'status_veiculos',
        'tempo_entre_estacoes',
        'tipo_checklist',
        'tipo_frota',
        'tipos_movimentacao',
        'tipo_opcional',
        'tipo_transacoes',
        'tipo_valor_caracteristica_frota',
        'transacoes',
        'veiculos',
        'veiculo_danos',
        'veiculo_danos_imagens',
        'payments',
        'empresas',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->ensureEmpresasTable();

        $defaultEmpresaId = DB::table('empresas')->value('id');
        if (!$defaultEmpresaId) {
            $defaultEmpresaId = DB::table('empresas')->insertGetId([
                'nome' => 'Empresa Padrao',
                'slug' => 'empresa-padrao',
                'ativo' => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($this->tables as $tableName) {
            $this->ensureEmpresaIdColumn($tableName, $defaultEmpresaId);
        }

        $this->ensureUsersUniqueByEmpresa();
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // No-op para evitar perda de dados em rollback.
    }

    private function ensureEmpresasTable(): void
    {
        if (Schema::hasTable('empresas')) {
            return;
        }

        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->string('ativo', 1)->default('S');
            $table->string('cnpj', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('telefone', 25)->nullable();
            $table->string('dominio')->nullable();
            $table->timestamps();
        });
    }

    private function ensureEmpresaIdColumn(string $tableName, int $defaultEmpresaId): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (Schema::hasColumn($tableName, 'tenant_id') && !Schema::hasColumn($tableName, 'empresa_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('tenant_id', 'empresa_id');
            });
        }

        if (!Schema::hasColumn($tableName, 'empresa_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->index('empresa_id');
            });
        } else {
            try {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->index('empresa_id', $tableName . '_empresa_id_index');
                });
            } catch (\Throwable $e) {
                // Index may already exist.
            }
        }

        if ($tableName === 'empresas') {
            DB::table('empresas')
                ->whereNull('empresa_id')
                ->update(['empresa_id' => DB::raw('id')]);
            return;
        }

        DB::table($tableName)
            ->whereNull('empresa_id')
            ->update(['empresa_id' => $defaultEmpresaId]);
    }

    private function ensureUsersUniqueByEmpresa(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'empresa_id')) {
            return;
        }

        $indexes = $this->getIndexes('users');

        if (in_array('users_email_unique', $indexes, true)) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropUnique('users_email_unique');
                });
            } catch (\Throwable $e) {
                // Ignore when index does not exist.
            }
        }

        if (in_array('users_tenant_email_unique', $indexes, true)) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropUnique('users_tenant_email_unique');
                });
            } catch (\Throwable $e) {
                // Ignore when index does not exist.
            }
        }

        if (!in_array('users_empresa_email_unique', $indexes, true)) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->unique(['empresa_id', 'email'], 'users_empresa_email_unique');
                });
            } catch (\Throwable $e) {
                // Ignore when index already exists.
            }
        }
    }

    private function getIndexes(string $table): array
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}`");
            return array_unique(array_map(fn ($i) => $i->Key_name, $indexes));
        } catch (\Throwable $e) {
            return [];
        }
    }
};
