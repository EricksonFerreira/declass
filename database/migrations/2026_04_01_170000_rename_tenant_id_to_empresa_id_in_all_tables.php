<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var string[]
     */
    private array $tables = [
        'users',
        'permissions',
        'roles',
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
        'termo_consentimento',
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
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::disableForeignKeyConstraints();
        foreach ($this->tables as $tableName) {
            $this->renameColumnToEmpresaId($tableName);
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'empresa_id')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropUnique('users_tenant_email_unique');
                });
            } catch (\Throwable $e) {
                // Ignore when index does not exist.
            }

            try {
                if (!Schema::hasTable('users')) { Schema::enableForeignKeyConstraints(); return; }
                Schema::table('users', function (Blueprint $table) {
                    $table->unique(['empresa_id', 'email'], 'users_empresa_email_unique');
                });
            } catch (\Throwable $e) {
                // Ignore when index already exists.
            }
        }
        Schema::enableForeignKeyConstraints();
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::disableForeignKeyConstraints();
        foreach ($this->tables as $tableName) {
            $this->renameColumnToTenantId($tableName);
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'tenant_id')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropUnique('users_empresa_email_unique');
                });
            } catch (\Throwable $e) {
                // Ignore when index does not exist.
            }

            try {
                if (!Schema::hasTable('users')) { Schema::enableForeignKeyConstraints(); return; }
                Schema::table('users', function (Blueprint $table) {
                    $table->unique(['tenant_id', 'email'], 'users_tenant_email_unique');
                });
            } catch (\Throwable $e) {
                // Ignore when index already exists.
            }
        }
        Schema::enableForeignKeyConstraints();
        Schema::enableForeignKeyConstraints();
    }

    private function renameColumnToEmpresaId(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (Schema::hasColumn($tableName, 'tenant_id') && !Schema::hasColumn($tableName, 'empresa_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('tenant_id', 'empresa_id');
            });
        }

        if (Schema::hasColumn($tableName, 'empresa_id')) {
            try {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropIndex($tableName . '_tenant_id_index');
                });
            } catch (\Throwable $e) {
                // Ignore when index does not exist.
            }

            try {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->index('empresa_id', $tableName . '_empresa_id_index');
                });
            } catch (\Throwable $e) {
                // Ignore when index already exists.
            }
        }
    }

    private function renameColumnToTenantId(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (Schema::hasColumn($tableName, 'empresa_id') && !Schema::hasColumn($tableName, 'tenant_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('empresa_id', 'tenant_id');
            });
        }

        if (Schema::hasColumn($tableName, 'tenant_id')) {
            try {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropIndex($tableName . '_empresa_id_index');
                });
            } catch (\Throwable $e) {
                // Ignore when index does not exist.
            }

            try {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->index('tenant_id', $tableName . '_tenant_id_index');
                });
            } catch (\Throwable $e) {
                // Ignore when index already exists.
            }
        }
    }
};
