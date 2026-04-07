<?php

namespace Database\Seeders;

use App\Models\Estacao;
use App\Models\GrupoFrota;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\Pais;
use App\Models\Segmento;
use App\Models\StatusVeiculo;
use App\Models\TipoFrota;
use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FrotaCatalogSeeder extends Seeder
{
    private ?int $empresaId = null;

    public function run(): void
    {
        $this->empresaId = $this->resolveEmpresaId();
        
        $brasil = $this->upsertIfTable(Pais::class, ['sigla' => 'BR'], [
            'nome' => 'Brasil',
            'sigla' => 'BR',
            'ativo' => 'S',
        ]);

        $estacaoMatriz = $this->upsertIfTable(Estacao::class, ['codigo' => 'MATRIZ'], [
            'codigo' => 'MATRIZ',
            'nome' => 'Matriz - Centro',
            'email' => 'matriz@xpandcars.test',
            'telefone' => '(11) 3000-0000',
            'celular' => '(11) 99999-0000',
            'cidade' => 'Sao Paulo',
            'numero' => '100',
            'zip_code' => '01000-000',
            'endereco' => 'Av. Paulista',
            'pais_id' => $brasil?->id,
            'latitude' => '-23.561684',
            'longitude' => '-46.656139',
            'ativo' => 'S',
            'checkin' => 'S',
            'checkout' => 'S',
        ]);

        $estacaoAeroporto = $this->upsertIfTable(Estacao::class, ['codigo' => 'AEROP'], [
            'codigo' => 'AEROP',
            'nome' => 'Aeroporto',
            'email' => 'aeroporto@xpandcars.test',
            'telefone' => '(11) 3000-1000',
            'celular' => '(11) 99999-1000',
            'cidade' => 'Sao Paulo',
            'numero' => '1',
            'zip_code' => '07190-100',
            'endereco' => 'Aeroporto Internacional',
            'pais_id' => $brasil?->id,
            'latitude' => '-23.435556',
            'longitude' => '-46.473056',
            'ativo' => 'S',
            'checkin' => 'S',
            'checkout' => 'S',
        ]);

        $statusDisponivel = $this->upsertIfTable(StatusVeiculo::class, ['nome' => 'Disponivel'], [
            'nome' => 'Disponivel',
            'ativo' => 'S',
        ]);

        $tipoCarro = $this->upsertIfTable(TipoFrota::class, ['codigo' => 'CAR'], [
            'codigo' => 'CAR',
            'nome' => 'Carro',
            'ativo' => 'S',
        ]);

        $tipoVan = $this->upsertIfTable(TipoFrota::class, ['codigo' => 'VAN'], [
            'codigo' => 'VAN',
            'nome' => 'Van',
            'ativo' => 'S',
        ]);

        $segHatch = $this->upsertIfTable(Segmento::class, ['nome' => 'Hatch'], ['nome' => 'Hatch', 'ativo' => 'S']);
        $segSedan = $this->upsertIfTable(Segmento::class, ['nome' => 'Sedan'], ['nome' => 'Sedan', 'ativo' => 'S']);
        $segSuv = $this->upsertIfTable(Segmento::class, ['nome' => 'SUV'], ['nome' => 'SUV', 'ativo' => 'S']);
        $segPickup = $this->upsertIfTable(Segmento::class, ['nome' => 'Pickup'], ['nome' => 'Pickup', 'ativo' => 'S']);

        $marcaFiat = $this->upsertIfTable(Marca::class, ['nome' => 'Fiat'], ['nome' => 'Fiat', 'ativo' => 'S']);
        $marcaVw = $this->upsertIfTable(Marca::class, ['nome' => 'Volkswagen'], ['nome' => 'Volkswagen', 'ativo' => 'S']);
        $marcaGm = $this->upsertIfTable(Marca::class, ['nome' => 'Chevrolet'], ['nome' => 'Chevrolet', 'ativo' => 'S']);
        $marcaToyota = $this->upsertIfTable(Marca::class, ['nome' => 'Toyota'], ['nome' => 'Toyota', 'ativo' => 'S']);

        $modeloArgo = $this->upsertIfTable(Modelo::class, ['nome' => 'Argo'], [
            'nome' => 'Argo',
            'ativo' => 'S',
            'marca_id' => $marcaFiat?->id,
            'segmento_id' => $segHatch?->id,
        ]);
        $modeloCronos = $this->upsertIfTable(Modelo::class, ['nome' => 'Cronos'], [
            'nome' => 'Cronos',
            'ativo' => 'S',
            'marca_id' => $marcaFiat?->id,
            'segmento_id' => $segSedan?->id,
        ]);
        $modeloGol = $this->upsertIfTable(Modelo::class, ['nome' => 'Gol'], [
            'nome' => 'Gol',
            'ativo' => 'S',
            'marca_id' => $marcaVw?->id,
            'segmento_id' => $segHatch?->id,
        ]);
        $modeloTCross = $this->upsertIfTable(Modelo::class, ['nome' => 'T-Cross'], [
            'nome' => 'T-Cross',
            'ativo' => 'S',
            'marca_id' => $marcaVw?->id,
            'segmento_id' => $segSuv?->id,
        ]);
        $modeloOnix = $this->upsertIfTable(Modelo::class, ['nome' => 'Onix'], [
            'nome' => 'Onix',
            'ativo' => 'S',
            'marca_id' => $marcaGm?->id,
            'segmento_id' => $segHatch?->id,
        ]);
        $modeloTracker = $this->upsertIfTable(Modelo::class, ['nome' => 'Tracker'], [
            'nome' => 'Tracker',
            'ativo' => 'S',
            'marca_id' => $marcaGm?->id,
            'segmento_id' => $segSuv?->id,
        ]);
        $modeloCorolla = $this->upsertIfTable(Modelo::class, ['nome' => 'Corolla'], [
            'nome' => 'Corolla',
            'ativo' => 'S',
            'marca_id' => $marcaToyota?->id,
            'segmento_id' => $segSedan?->id,
        ]);
        $modeloHilux = $this->upsertIfTable(Modelo::class, ['nome' => 'Hilux'], [
            'nome' => 'Hilux',
            'ativo' => 'S',
            'marca_id' => $marcaToyota?->id,
            'segmento_id' => $segPickup?->id,
        ]);

        $grupoEconomico = $this->upsertIfTable(GrupoFrota::class, ['codigo' => 'ECO'], [
            'tipo_frota_id' => $tipoCarro?->id,
            'codigo' => 'ECO',
            'nome' => 'Economico',
            'valor_diario' => 99.90,
            'ativo' => 'S',
            'ordem' => 10,
        ]);
        $grupoCompacto = $this->upsertIfTable(GrupoFrota::class, ['codigo' => 'COM'], [
            'tipo_frota_id' => $tipoCarro?->id,
            'codigo' => 'COM',
            'nome' => 'Compacto',
            'valor_diario' => 129.90,
            'ativo' => 'S',
            'ordem' => 20,
        ]);
        $grupoSuv = $this->upsertIfTable(GrupoFrota::class, ['codigo' => 'SUV'], [
            'tipo_frota_id' => $tipoCarro?->id,
            'codigo' => 'SUV',
            'nome' => 'SUV',
            'valor_diario' => 199.90,
            'ativo' => 'S',
            'ordem' => 30,
        ]);
        $grupoPremium = $this->upsertIfTable(GrupoFrota::class, ['codigo' => 'PRE'], [
            'tipo_frota_id' => $tipoCarro?->id,
            'codigo' => 'PRE',
            'nome' => 'Premium',
            'valor_diario' => 299.90,
            'ativo' => 'S',
            'ordem' => 40,
        ]);

        if (Schema::hasTable('grupo_frota_modelo')) {
            $this->attachModeloNoGrupo($grupoEconomico, [$modeloArgo, $modeloGol, $modeloOnix]);
            $this->attachModeloNoGrupo($grupoCompacto, [$modeloCronos, $modeloCorolla]);
            $this->attachModeloNoGrupo($grupoSuv, [$modeloTCross, $modeloTracker]);
            $this->attachModeloNoGrupo($grupoPremium, [$modeloCorolla, $modeloTracker]);
        }

        // Veiculos
        $baseEstacaoId = $estacaoMatriz?->id ?? $estacaoAeroporto?->id ?? null;

        $this->upsertIfTable(Veiculo::class, ['placa_licenca' => 'ABC1D23'], [
            'nome' => 'Fiat Argo 1.0',
            'ativo' => 'S',
            'tipo_frota_id' => $tipoCarro?->id,
            'grupo_frota_id' => $grupoEconomico?->id,
            'marca_id' => $marcaFiat?->id,
            'modelo_id' => $modeloArgo?->id,
            'segmento_id' => $segHatch?->id,
            'status_veiculo_id' => $statusDisponivel?->id,
            'placa_licenca' => 'ABC1D23',
            'numero_motor' => 'MOT-ABC1D23',
            'chassi' => 'CHS-ABC1D23',
            'ano_fabricacao' => 2023,
            'ano_modelo' => 2024,
            'cor' => 'Prata',
            'kms' => 12000,
            'local_disponibilidade' => $baseEstacaoId,
            'data_hora_disponibilidade' => now(),
        ]);

        $this->upsertIfTable(Veiculo::class, ['placa_licenca' => 'DEF4G56'], [
            'nome' => 'VW Gol 1.6',
            'ativo' => 'S',
            'tipo_frota_id' => $tipoCarro?->id,
            'grupo_frota_id' => $grupoEconomico?->id,
            'marca_id' => $marcaVw?->id,
            'modelo_id' => $modeloGol?->id,
            'segmento_id' => $segHatch?->id,
            'status_veiculo_id' => $statusDisponivel?->id,
            'placa_licenca' => 'DEF4G56',
            'numero_motor' => 'MOT-DEF4G56',
            'chassi' => 'CHS-DEF4G56',
            'ano_fabricacao' => 2022,
            'ano_modelo' => 2023,
            'cor' => 'Branco',
            'kms' => 24000,
            'local_disponibilidade' => $baseEstacaoId,
            'data_hora_disponibilidade' => now(),
        ]);

        $this->upsertIfTable(Veiculo::class, ['placa_licenca' => 'GHI7J89'], [
            'nome' => 'Toyota Corolla 2.0',
            'ativo' => 'S',
            'tipo_frota_id' => $tipoCarro?->id,
            'grupo_frota_id' => $grupoPremium?->id,
            'marca_id' => $marcaToyota?->id,
            'modelo_id' => $modeloCorolla?->id,
            'segmento_id' => $segSedan?->id,
            'status_veiculo_id' => $statusDisponivel?->id,
            'placa_licenca' => 'GHI7J89',
            'numero_motor' => 'MOT-GHI7J89',
            'chassi' => 'CHS-GHI7J89',
            'ano_fabricacao' => 2024,
            'ano_modelo' => 2025,
            'cor' => 'Preto',
            'kms' => 8000,
            'local_disponibilidade' => $baseEstacaoId,
            'data_hora_disponibilidade' => now(),
        ]);

        $this->upsertIfTable(Veiculo::class, ['placa_licenca' => 'JKL1M23'], [
            'nome' => 'Chevrolet Tracker 1.2 Turbo',
            'ativo' => 'S',
            'tipo_frota_id' => $tipoCarro?->id,
            'grupo_frota_id' => $grupoSuv?->id,
            'marca_id' => $marcaGm?->id,
            'modelo_id' => $modeloTracker?->id,
            'segmento_id' => $segSuv?->id,
            'status_veiculo_id' => $statusDisponivel?->id,
            'placa_licenca' => 'JKL1M23',
            'numero_motor' => 'MOT-JKL1M23',
            'chassi' => 'CHS-JKL1M23',
            'ano_fabricacao' => 2023,
            'ano_modelo' => 2024,
            'cor' => 'Azul',
            'kms' => 15000,
            'local_disponibilidade' => $baseEstacaoId,
            'data_hora_disponibilidade' => now(),
        ]);

        $this->upsertIfTable(Veiculo::class, ['placa_licenca' => 'MNO4P56'], [
            'nome' => 'Toyota Hilux 2.8',
            'ativo' => 'S',
            'tipo_frota_id' => $tipoVan?->id ?? $tipoCarro?->id,
            'grupo_frota_id' => $grupoSuv?->id,
            'marca_id' => $marcaToyota?->id,
            'modelo_id' => $modeloHilux?->id,
            'segmento_id' => $segPickup?->id,
            'status_veiculo_id' => $statusDisponivel?->id,
            'placa_licenca' => 'MNO4P56',
            'numero_motor' => 'MOT-MNO4P56',
            'chassi' => 'CHS-MNO4P56',
            'ano_fabricacao' => 2022,
            'ano_modelo' => 2023,
            'cor' => 'Cinza',
            'kms' => 32000,
            'local_disponibilidade' => $baseEstacaoId,
            'data_hora_disponibilidade' => now(),
        ]);
    }

    private function attachModeloNoGrupo(?GrupoFrota $grupo, array $modelos): void
    {
        if (!$grupo) {
            return;
        }
        if (!Schema::hasTable('grupo_frota_modelo')) {
            return;
        }

        $modeloIds = collect($modelos)
            ->filter()
            ->map(fn($m) => (int) $m->id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (!$modeloIds) {
            return;
        }

        // Evita duplicar: syncWithoutDetaching se pivot existir e relacionamento estiver OK.
        try {
            $grupo->modelos()->syncWithoutDetaching($modeloIds);
        } catch (\Throwable $e) {
            // fallback silencioso (schema pode estar diferente)
        }
    }

    /**
     * Upsert por Model::updateOrCreate filtrando apenas colunas existentes.
     * Retorna null caso a tabela do model nao exista.
     */
    private function upsertIfTable(string $modelClass, array $unique, array $attrs): ?Model
    {
        /** @var Model $model */
        $model = new $modelClass();
        $table = $model->getTable();

        if (!Schema::hasTable($table)) {
            return null;
        }

        $columnsLower = collect(Schema::getColumnListing($table))
            ->map(fn ($c) => strtolower((string) $c))
            ->values()
            ->all();

        if (
            $this->empresaId
            && $table !== 'empresas'
            && in_array('empresa_id', $columnsLower, true)
        ) {
            $attrs['empresa_id'] = $attrs['empresa_id'] ?? $this->empresaId;
            $unique['empresa_id'] = $unique['empresa_id'] ?? $attrs['empresa_id'];
        }

        $uniqueFiltered = [];
        foreach ($unique as $key => $value) {
            if (in_array(strtolower((string) $key), $columnsLower, true)) {
                $uniqueFiltered[$key] = $value;
            }
        }

        // Sempre tenta pelo menos 'nome' quando existir.
        if (!$uniqueFiltered && isset($attrs['nome']) && in_array('nome', $columnsLower, true)) {
            $uniqueFiltered = ['nome' => $attrs['nome']];
        }

        $attrsFiltered = [];
        foreach ($attrs as $key => $value) {
            if (in_array(strtolower((string) $key), $columnsLower, true)) {
                $attrsFiltered[$key] = $value;
            }
        }

        if (!$uniqueFiltered) {
            return $model->newQuery()->create($attrsFiltered);
        }

        return $model->newQuery()->updateOrCreate($uniqueFiltered, $attrsFiltered);
    }

    private function resolveEmpresaId(): ?int
    {
        if (!Schema::hasTable('empresas')) {
            return null;
        }

        $id = (int) (DB::table('empresas')->where('slug', 'empresa-padrao')->value('id') ?? 0);
        if ($id <= 0) {
            $id = (int) (DB::table('empresas')->value('id') ?? 0);
        }

        if ($id > 0) {
            return $id;
        }

        $id = (int) DB::table('empresas')->insertGetId([
            'nome' => 'Empresa Padrao',
            'slug' => 'empresa-padrao',
            'ativo' => 'S',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($id > 0 && Schema::hasColumn('empresas', 'empresa_id')) {
            DB::table('empresas')->where('id', $id)->update(['empresa_id' => $id]);
        }

        return $id > 0 ? $id : null;
    }
}
