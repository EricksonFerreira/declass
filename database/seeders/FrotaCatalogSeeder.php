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

        // Usa o DefaultFrotaTemplateService para popular tipos, grupos,
        // segmentos, marcas, modelos e vínculos de forma completa e alinhada
        // com o padrão do mercado de locação.
        if ($this->empresaId !== null) {
            app(\App\Services\DefaultFrotaTemplateService::class)->seed($this->empresaId);
        }

        // Referências locais usadas nos veículos de exemplo abaixo
        $tipoCarro = TipoFrota::where('codigo', 'CAR')->first();
        $tipoVan   = TipoFrota::where('codigo', 'VAN')->first();

        $segHatch  = Segmento::where('nome', 'Hatch')->first();
        $segSedan  = Segmento::where('nome', 'Sedan')->first();
        $segSuv    = Segmento::where('nome', 'SUV')->first();
        $segPickup = Segmento::where('nome', 'Pickup')->first();

        $marcaFiat   = Marca::where('nome', 'Fiat')->first();
        $marcaVw     = Marca::where('nome', 'Volkswagen')->first();
        $marcaGm     = Marca::where('nome', 'Chevrolet')->first();
        $marcaToyota = Marca::where('nome', 'Toyota')->first();

        $modeloArgo    = Modelo::where('nome', 'Argo')->first();
        $modeloGol     = Modelo::where('nome', 'Gol')->first();
        $modeloOnix    = Modelo::where('nome', 'Onix')->first();
        $modeloCronos  = Modelo::where('nome', 'Cronos')->first();
        $modeloCorolla = Modelo::where('nome', 'Corolla')->first();
        $modeloTracker = Modelo::where('nome', 'Tracker')->first();
        $modeloHilux   = Modelo::where('nome', 'Hilux')->first();

        $grupoMini      = GrupoFrota::where('codigo', 'MINI')->first();
        $grupoEconomico = GrupoFrota::where('codigo', 'ECO')->first();
        $grupoCompacto  = GrupoFrota::where('codigo', 'COM')->first();
        $grupoSuvC      = GrupoFrota::where('codigo', 'SUVC')->first();
        $grupoPickup    = GrupoFrota::where('codigo', 'PICK')->first();

        // Veiculos de exemplo
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
            'grupo_frota_id' => $grupoCompacto?->id,
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
            'grupo_frota_id' => $grupoSuvC?->id,
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
            'tipo_frota_id' => $tipoCarro?->id,
            'grupo_frota_id' => $grupoPickup?->id,
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
