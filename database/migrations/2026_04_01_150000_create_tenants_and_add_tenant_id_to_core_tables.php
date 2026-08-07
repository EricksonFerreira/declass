<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Main business tables that need tenant isolation.
     *
     * @var string[]
     */
    private array $tenantTables = [
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

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('empresas')) {
            Schema::create('empresas', function (Blueprint $table) {
                $table->id();
                $table->string('nome');
                $table->string('slug')->unique();
                $table->string('ativo', 1)->default('S');
                $table->timestamps();
    });
        }

        $defaultTenantId = DB::table('empresas')->where('slug', 'empresa-padrao')->value('id');
        if (!$defaultTenantId) {
            $defaultTenantId = DB::table('empresas')->insertGetId([
                'nome' => 'Empresa Padrao',
                'slug' => 'empresa-padrao',
                'ativo' => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'empresa_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->index('empresa_id');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'empresa_id')) {
            DB::table('users')->whereNull('empresa_id')->update(['empresa_id' => $defaultTenantId]);

            $indexes = $this->getUserIndexes();

            if (in_array('users_email_unique', $indexes, true)) {
                try {
                    Schema::table('users', function (Blueprint $table) {
                        $table->dropUnique('users_email_unique');
                    });
                } catch (\Throwable $e) {
                    // Ignore if the index does not exist.
                }
            }

            if (!in_array('users_empresa_email_unique', $indexes, true)) {
                try {
                    Schema::table('users', function (Blueprint $table) {
                        $table->unique(['empresa_id', 'email'], 'users_empresa_email_unique');
                    });
                } catch (\Throwable $e) {
                    // Ignore if the index already exists.
                }
            }
        }

        foreach ($this->tenantTables as $tableName) {
            $this->addTenantColumnToTable($tableName, $defaultTenantId);
        }
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach ($this->tenantTables as $tableName) {
            $this->dropTenantColumnFromTable($tableName);
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'empresa_id')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropUnique('users_empresa_email_unique');
                });
            } catch (\Throwable $e) {
                // Ignore if the index does not exist.
            }

            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->unique('email', 'users_email_unique');
                });
            } catch (\Throwable $e) {
                // Ignore if the index already exists.
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['empresa_id']);
                $table->dropColumn('empresa_id');
            });
        }

        Schema::dropIfExists('empresas');
        Schema::enableForeignKeyConstraints();
    }

    private function getUserIndexes(): array
    {
        try {
            $indexes = DB::select('SHOW INDEX FROM `users`');
            return array_unique(array_map(fn ($i) => $i->Key_name, $indexes));
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function addTenantColumnToTable(string $tableName, int $defaultTenantId): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (!Schema::hasColumn($tableName, 'empresa_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->index('empresa_id');
            });
        }

        DB::table($tableName)->whereNull('empresa_id')->update(['empresa_id' => $defaultTenantId]);
    }

    private function dropTenantColumnFromTable(string $tableName): void
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'empresa_id')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropIndex(['empresa_id']);
            $table->dropColumn('empresa_id');
        });
    }
};
