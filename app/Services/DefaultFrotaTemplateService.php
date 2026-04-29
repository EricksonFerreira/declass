<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Garante que a empresa-padrao tenha os dados de frota completos
 * (tipos, grupos, segmentos, marcas, modelos e vínculos grupo↔modelo)
 * antes de replicar para novas empresas.
 *
 * Todos os métodos são idempotentes — seguros para rodar múltiplas vezes.
 */
class DefaultFrotaTemplateService
{
    // -------------------------------------------------------------------------
    // Catálogos estáticos
    // -------------------------------------------------------------------------

    /** @return array<int, array{codigo: string, nome: string, ativo: string}> */
    private function tiposFrota(): array
    {
        return [
            ['codigo' => 'CAR',    'nome' => 'Carro',        'ativo' => 'S'],
            ['codigo' => 'SUV',    'nome' => 'SUV',          'ativo' => 'S'],
            ['codigo' => 'PICKUP', 'nome' => 'Pickup',        'ativo' => 'S'],
            ['codigo' => 'VAN',    'nome' => 'Van / Minivan', 'ativo' => 'S'],
            ['codigo' => 'MOTO',   'nome' => 'Motocicleta',  'ativo' => 'S'],
            ['codigo' => 'TRUCK',  'nome' => 'Utilitário',   'ativo' => 'S'],
        ];
    }

    /**
     * Grupos baseados nas categorias Localiza / Movida / Unidas.
     * tipo_codigo → referencia o código do tipo_frota acima.
     *
     * @return array<int, array{tipo_codigo:string,codigo:string,codigo_sipp:string,nome:string,valor_diario:float,ativo:string,ordem:int}>
     */
    private function gruposFrota(): array
    {
        return [
            // ── Carros ────────────────────────────────────────────────────────
            ['tipo_codigo' => 'CAR',    'codigo' => 'MINI', 'codigo_sipp' => 'MBMR', 'nome' => 'Mini',          'valor_diario' =>  79.90, 'ativo' => 'S', 'ordem' =>  10],
            ['tipo_codigo' => 'CAR',    'codigo' => 'ECO',  'codigo_sipp' => 'ECMR', 'nome' => 'Econômico',     'valor_diario' =>  99.90, 'ativo' => 'S', 'ordem' =>  20],
            ['tipo_codigo' => 'CAR',    'codigo' => 'COM',  'codigo_sipp' => 'CCMR', 'nome' => 'Compacto',      'valor_diario' => 129.90, 'ativo' => 'S', 'ordem' =>  30],
            ['tipo_codigo' => 'CAR',    'codigo' => 'INT',  'codigo_sipp' => 'ICMR', 'nome' => 'Intermediário', 'valor_diario' => 169.90, 'ativo' => 'S', 'ordem' =>  40],
            ['tipo_codigo' => 'CAR',    'codigo' => 'STD',  'codigo_sipp' => 'SCMR', 'nome' => 'Standard',      'valor_diario' => 199.90, 'ativo' => 'S', 'ordem' =>  50],
            ['tipo_codigo' => 'CAR',    'codigo' => 'PRE',  'codigo_sipp' => 'FCAR', 'nome' => 'Premium',       'valor_diario' => 299.90, 'ativo' => 'S', 'ordem' =>  60],
            ['tipo_codigo' => 'CAR',    'codigo' => 'LUX',  'codigo_sipp' => 'PCAR', 'nome' => 'Luxo',          'valor_diario' => 449.90, 'ativo' => 'S', 'ordem' =>  70],
            // ── SUV ───────────────────────────────────────────────────────────
            ['tipo_codigo' => 'SUV',    'codigo' => 'SUVC', 'codigo_sipp' => 'SFAR', 'nome' => 'SUV Compacto',  'valor_diario' => 199.90, 'ativo' => 'S', 'ordem' =>  80],
            ['tipo_codigo' => 'SUV',    'codigo' => 'SUVM', 'codigo_sipp' => 'SUAR', 'nome' => 'SUV Médio',     'valor_diario' => 269.90, 'ativo' => 'S', 'ordem' =>  90],
            ['tipo_codigo' => 'SUV',    'codigo' => 'SUVG', 'codigo_sipp' => 'IFAR', 'nome' => 'SUV Grande',    'valor_diario' => 349.90, 'ativo' => 'S', 'ordem' => 100],
            // ── Pickup ────────────────────────────────────────────────────────
            ['tipo_codigo' => 'PICKUP', 'codigo' => 'PICK', 'codigo_sipp' => 'PKAR', 'nome' => 'Pickup',        'valor_diario' => 289.90, 'ativo' => 'S', 'ordem' => 110],
            // ── Van ───────────────────────────────────────────────────────────
            ['tipo_codigo' => 'VAN',    'codigo' => 'MINV', 'codigo_sipp' => 'FVAR', 'nome' => 'Minivan',       'valor_diario' => 249.90, 'ativo' => 'S', 'ordem' => 120],
            ['tipo_codigo' => 'VAN',    'codigo' => 'VAN',  'codigo_sipp' => 'LVAR', 'nome' => 'Van',           'valor_diario' => 399.90, 'ativo' => 'S', 'ordem' => 130],
        ];
    }

    /** @return array<int, array{nome: string, ativo: string}> */
    private function segmentos(): array
    {
        return [
            ['nome' => 'Hatch',    'ativo' => 'S'],
            ['nome' => 'Sedan',    'ativo' => 'S'],
            ['nome' => 'SUV',      'ativo' => 'S'],
            ['nome' => 'Pickup',   'ativo' => 'S'],
            ['nome' => 'Minivan',  'ativo' => 'S'],
            ['nome' => 'Van',      'ativo' => 'S'],
            ['nome' => 'Esportivo','ativo' => 'S'],
        ];
    }

    /**
     * Marcas e seus modelos com segmento e grupo de referência.
     * grupo_codigo → referencia o codigo do grupo_frota acima.
     *
     * @return array<int, array{
     *   nome: string,
     *   modelos: array<int, array{nome: string, segmento: string, grupo_codigo: string}>
     * }>
     */
    private function marcasEModelos(): array
    {
        return [
            [
                'nome' => 'Fiat',
                'modelos' => [
                    ['nome' => 'Mobi',         'segmento' => 'Hatch',  'grupo_codigo' => 'MINI'],
                    ['nome' => 'Argo',          'segmento' => 'Hatch',  'grupo_codigo' => 'ECO'],
                    ['nome' => 'Cronos',        'segmento' => 'Sedan',  'grupo_codigo' => 'COM'],
                    ['nome' => 'Pulse',         'segmento' => 'SUV',    'grupo_codigo' => 'SUVC'],
                    ['nome' => 'Fastback',      'segmento' => 'SUV',    'grupo_codigo' => 'SUVC'],
                    ['nome' => 'Toro',          'segmento' => 'Pickup', 'grupo_codigo' => 'PICK'],
                    ['nome' => 'Strada',        'segmento' => 'Pickup', 'grupo_codigo' => 'PICK'],
                ],
            ],
            [
                'nome' => 'Volkswagen',
                'modelos' => [
                    ['nome' => 'Gol',           'segmento' => 'Hatch',  'grupo_codigo' => 'ECO'],
                    ['nome' => 'Polo',          'segmento' => 'Hatch',  'grupo_codigo' => 'COM'],
                    ['nome' => 'Virtus',        'segmento' => 'Sedan',  'grupo_codigo' => 'COM'],
                    ['nome' => 'T-Cross',       'segmento' => 'SUV',    'grupo_codigo' => 'SUVC'],
                    ['nome' => 'Taos',          'segmento' => 'SUV',    'grupo_codigo' => 'SUVM'],
                    ['nome' => 'Tiguan',        'segmento' => 'SUV',    'grupo_codigo' => 'SUVM'],
                    ['nome' => 'Amarok',        'segmento' => 'Pickup', 'grupo_codigo' => 'PICK'],
                ],
            ],
            [
                'nome' => 'Chevrolet',
                'modelos' => [
                    ['nome' => 'Onix',          'segmento' => 'Hatch',  'grupo_codigo' => 'ECO'],
                    ['nome' => 'Onix Plus',     'segmento' => 'Sedan',  'grupo_codigo' => 'COM'],
                    ['nome' => 'Cruze',         'segmento' => 'Sedan',  'grupo_codigo' => 'INT'],
                    ['nome' => 'Tracker',       'segmento' => 'SUV',    'grupo_codigo' => 'SUVC'],
                    ['nome' => 'Equinox',       'segmento' => 'SUV',    'grupo_codigo' => 'SUVM'],
                    ['nome' => 'Trailblazer',   'segmento' => 'SUV',    'grupo_codigo' => 'SUVG'],
                    ['nome' => 'S10',           'segmento' => 'Pickup', 'grupo_codigo' => 'PICK'],
                ],
            ],
            [
                'nome' => 'Toyota',
                'modelos' => [
                    ['nome' => 'Yaris',         'segmento' => 'Hatch',  'grupo_codigo' => 'COM'],
                    ['nome' => 'Corolla',       'segmento' => 'Sedan',  'grupo_codigo' => 'INT'],
                    ['nome' => 'Corolla Cross', 'segmento' => 'SUV',    'grupo_codigo' => 'SUVC'],
                    ['nome' => 'RAV4',          'segmento' => 'SUV',    'grupo_codigo' => 'SUVM'],
                    ['nome' => 'Hilux',         'segmento' => 'Pickup', 'grupo_codigo' => 'PICK'],
                    ['nome' => 'SW4',           'segmento' => 'SUV',    'grupo_codigo' => 'SUVG'],
                ],
            ],
            [
                'nome' => 'Hyundai',
                'modelos' => [
                    ['nome' => 'HB20',          'segmento' => 'Hatch',  'grupo_codigo' => 'ECO'],
                    ['nome' => 'HB20S',         'segmento' => 'Sedan',  'grupo_codigo' => 'COM'],
                    ['nome' => 'Creta',         'segmento' => 'SUV',    'grupo_codigo' => 'SUVC'],
                    ['nome' => 'Tucson',        'segmento' => 'SUV',    'grupo_codigo' => 'SUVM'],
                    ['nome' => 'Santa Fe',      'segmento' => 'SUV',    'grupo_codigo' => 'SUVG'],
                ],
            ],
            [
                'nome' => 'Honda',
                'modelos' => [
                    ['nome' => 'Fit',           'segmento' => 'Hatch',  'grupo_codigo' => 'ECO'],
                    ['nome' => 'City',          'segmento' => 'Sedan',  'grupo_codigo' => 'COM'],
                    ['nome' => 'HR-V',          'segmento' => 'SUV',    'grupo_codigo' => 'SUVC'],
                    ['nome' => 'CR-V',          'segmento' => 'SUV',    'grupo_codigo' => 'SUVM'],
                    ['nome' => 'Civic',         'segmento' => 'Sedan',  'grupo_codigo' => 'INT'],
                ],
            ],
            [
                'nome' => 'Renault',
                'modelos' => [
                    ['nome' => 'Kwid',          'segmento' => 'Hatch',  'grupo_codigo' => 'MINI'],
                    ['nome' => 'Sandero',       'segmento' => 'Hatch',  'grupo_codigo' => 'ECO'],
                    ['nome' => 'Logan',         'segmento' => 'Sedan',  'grupo_codigo' => 'ECO'],
                    ['nome' => 'Duster',        'segmento' => 'SUV',    'grupo_codigo' => 'SUVC'],
                    ['nome' => 'Oroch',         'segmento' => 'Pickup', 'grupo_codigo' => 'PICK'],
                ],
            ],
            [
                'nome' => 'Jeep',
                'modelos' => [
                    ['nome' => 'Renegade',      'segmento' => 'SUV',    'grupo_codigo' => 'SUVC'],
                    ['nome' => 'Compass',       'segmento' => 'SUV',    'grupo_codigo' => 'SUVM'],
                    ['nome' => 'Commander',     'segmento' => 'SUV',    'grupo_codigo' => 'SUVG'],
                    ['nome' => 'Gladiator',     'segmento' => 'Pickup', 'grupo_codigo' => 'PICK'],
                ],
            ],
            [
                'nome' => 'Ford',
                'modelos' => [
                    ['nome' => 'EcoSport',      'segmento' => 'SUV',    'grupo_codigo' => 'SUVC'],
                    ['nome' => 'Ranger',        'segmento' => 'Pickup', 'grupo_codigo' => 'PICK'],
                    ['nome' => 'Bronco',        'segmento' => 'SUV',    'grupo_codigo' => 'SUVG'],
                ],
            ],
            [
                'nome' => 'Nissan',
                'modelos' => [
                    ['nome' => 'March',         'segmento' => 'Hatch',  'grupo_codigo' => 'ECO'],
                    ['nome' => 'Versa',         'segmento' => 'Sedan',  'grupo_codigo' => 'COM'],
                    ['nome' => 'Kicks',         'segmento' => 'SUV',    'grupo_codigo' => 'SUVC'],
                    ['nome' => 'Frontier',      'segmento' => 'Pickup', 'grupo_codigo' => 'PICK'],
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Entry point
    // -------------------------------------------------------------------------

    /**
     * Semeia todos os dados padrão de frota para a empresa-padrao.
     * Idempotente — seguro para chamar em toda criação de empresa.
     */
    public function seed(int $empresaId): void
    {
        $mapaTipos    = $this->seedTiposFrota($empresaId);
        $mapaGrupos   = $this->seedGruposFrota($empresaId, $mapaTipos);
        $mapaSegmentos = $this->seedSegmentos($empresaId);
        $this->seedMarcasModelosVinculos($empresaId, $mapaGrupos, $mapaSegmentos);
    }

    // -------------------------------------------------------------------------
    // Tipos de frota
    // -------------------------------------------------------------------------

    /** @return array<string, int>  mapa codigo => id */
    private function seedTiposFrota(int $empresaId): array
    {
        if (!Schema::hasTable('tipo_frota')) {
            return [];
        }

        $hasEmpresaId = Schema::hasColumn('tipo_frota', 'empresa_id');
        $hasCodigo    = Schema::hasColumn('tipo_frota', 'codigo');
        $hasAtivo     = Schema::hasColumn('tipo_frota', 'ativo');
        $mapa         = [];

        foreach ($this->tiposFrota() as $tipo) {
            $where = $hasEmpresaId ? ['empresa_id' => $empresaId, 'nome' => $tipo['nome']] : ['nome' => $tipo['nome']];

            $existente = DB::table('tipo_frota')->where($where)->first();

            if ($existente) {
                $mapa[$tipo['codigo']] = (int) $existente->id;
                // Garante que o codigo esteja preenchido caso o registro seja antigo
                if ($hasCodigo && empty($existente->codigo)) {
                    DB::table('tipo_frota')->where('id', $existente->id)->update(['codigo' => $tipo['codigo']]);
                }
                continue;
            }

            $payload = ['nome' => $tipo['nome']];
            if ($hasCodigo)    { $payload['codigo']     = $tipo['codigo']; }
            if ($hasAtivo)     { $payload['ativo']      = $tipo['ativo']; }
            if ($hasEmpresaId) { $payload['empresa_id'] = $empresaId; }
            $payload['created_at'] = now();
            $payload['updated_at'] = now();

            $id = (int) DB::table('tipo_frota')->insertGetId($payload);
            $mapa[$tipo['codigo']] = $id;
        }

        return $mapa;
    }

    // -------------------------------------------------------------------------
    // Grupos de frota
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, int> $mapaTipos
     * @return array<string, int>  mapa codigo => id
     */
    private function seedGruposFrota(int $empresaId, array $mapaTipos): array
    {
        if (!Schema::hasTable('grupos_frota')) {
            return [];
        }

        $hasEmpresaId = Schema::hasColumn('grupos_frota', 'empresa_id');
        $hasCodigo    = Schema::hasColumn('grupos_frota', 'codigo');
        $hasSipp      = Schema::hasColumn('grupos_frota', 'codigo_sipp');
        $hasOrdem     = Schema::hasColumn('grupos_frota', 'ordem');
        $hasValor     = Schema::hasColumn('grupos_frota', 'valor_diario');
        $hasAtivo     = Schema::hasColumn('grupos_frota', 'ativo');
        $mapa         = [];

        foreach ($this->gruposFrota() as $grupo) {
            // Chave de unicidade: empresa + codigo
            $where = $hasEmpresaId ? ['empresa_id' => $empresaId] : [];
            if ($hasCodigo) {
                $where['codigo'] = $grupo['codigo'];
            } else {
                $where['nome'] = $grupo['nome'];
            }

            $existente = DB::table('grupos_frota')->where($where)->first();

            if ($existente) {
                $mapa[$grupo['codigo']] = (int) $existente->id;
                continue;
            }

            $tipoId  = $mapaTipos[$grupo['tipo_codigo']] ?? null;
            $payload = ['nome' => $grupo['nome']];

            if ($tipoId !== null)  { $payload['tipo_frota_id'] = $tipoId; }
            if ($hasCodigo)        { $payload['codigo']        = $grupo['codigo']; }
            if ($hasSipp)          { $payload['codigo_sipp']   = $grupo['codigo_sipp']; }
            if ($hasValor)         { $payload['valor_diario']  = $grupo['valor_diario']; }
            if ($hasOrdem)         { $payload['ordem']         = $grupo['ordem']; }
            if ($hasAtivo)         { $payload['ativo']         = $grupo['ativo']; }
            if ($hasEmpresaId)     { $payload['empresa_id']    = $empresaId; }
            $payload['created_at'] = now();
            $payload['updated_at'] = now();

            $id = (int) DB::table('grupos_frota')->insertGetId($payload);
            $mapa[$grupo['codigo']] = $id;
        }

        return $mapa;
    }

    // -------------------------------------------------------------------------
    // Segmentos
    // -------------------------------------------------------------------------

    /** @return array<string, int>  mapa nome => id */
    private function seedSegmentos(int $empresaId): array
    {
        if (!Schema::hasTable('segmentos')) {
            return [];
        }

        $hasEmpresaId = Schema::hasColumn('segmentos', 'empresa_id');
        $hasAtivo     = Schema::hasColumn('segmentos', 'ativo');
        $mapa         = [];

        foreach ($this->segmentos() as $seg) {
            $where = $hasEmpresaId ? ['empresa_id' => $empresaId, 'nome' => $seg['nome']] : ['nome' => $seg['nome']];

            $existente = DB::table('segmentos')->where($where)->first();

            if ($existente) {
                $mapa[$seg['nome']] = (int) $existente->id;
                continue;
            }

            $payload = ['nome' => $seg['nome']];
            if ($hasAtivo)     { $payload['ativo']      = $seg['ativo']; }
            if ($hasEmpresaId) { $payload['empresa_id'] = $empresaId; }
            $payload['created_at'] = now();
            $payload['updated_at'] = now();

            $id = (int) DB::table('segmentos')->insertGetId($payload);
            $mapa[$seg['nome']] = $id;
        }

        return $mapa;
    }

    // -------------------------------------------------------------------------
    // Marcas, modelos e vínculos grupo↔modelo
    // -------------------------------------------------------------------------

    /**
     * @param array<string, int> $mapaGrupos
     * @param array<string, int> $mapaSegmentos
     */
    private function seedMarcasModelosVinculos(int $empresaId, array $mapaGrupos, array $mapaSegmentos): void
    {
        if (!Schema::hasTable('marcas') || !Schema::hasTable('modelos')) {
            return;
        }

        $hasMarcaEmpresa  = Schema::hasColumn('marcas', 'empresa_id');
        $hasModeloEmpresa = Schema::hasColumn('modelos', 'empresa_id');
        $hasSegmentoId    = Schema::hasColumn('modelos', 'segmento_id');
        $hasModeloAtivo   = Schema::hasColumn('modelos', 'ativo');
        $hasMarcaAtivo    = Schema::hasColumn('marcas', 'ativo');
        $temPivot         = Schema::hasTable('grupo_frota_modelo');

        foreach ($this->marcasEModelos() as $marcaDef) {
            // ── Marca ────────────────────────────────────────────────────────
            $marcaWhere = $hasMarcaEmpresa
                ? ['empresa_id' => $empresaId, 'nome' => $marcaDef['nome']]
                : ['nome' => $marcaDef['nome']];

            $marcaExistente = DB::table('marcas')->where($marcaWhere)->first();

            if ($marcaExistente) {
                $marcaId = (int) $marcaExistente->id;
            } else {
                $payload = ['nome' => $marcaDef['nome']];
                if ($hasMarcaAtivo)    { $payload['ativo']      = 'S'; }
                if ($hasMarcaEmpresa)  { $payload['empresa_id'] = $empresaId; }
                $payload['created_at'] = now();
                $payload['updated_at'] = now();

                $marcaId = (int) DB::table('marcas')->insertGetId($payload);
            }

            // ── Modelos ──────────────────────────────────────────────────────
            foreach ($marcaDef['modelos'] as $modeloDef) {
                $segmentoId = $mapaSegmentos[$modeloDef['segmento']] ?? null;

                $modeloWhere = $hasModeloEmpresa
                    ? ['empresa_id' => $empresaId, 'nome' => $modeloDef['nome'], 'marca_id' => $marcaId]
                    : ['nome' => $modeloDef['nome'], 'marca_id' => $marcaId];

                $modeloExistente = DB::table('modelos')->where($modeloWhere)->first();

                if ($modeloExistente) {
                    $modeloId = (int) $modeloExistente->id;
                } else {
                    $mp = ['nome' => $modeloDef['nome'], 'marca_id' => $marcaId];
                    if ($hasModeloAtivo)   { $mp['ativo']      = 'S'; }
                    if ($hasModeloEmpresa) { $mp['empresa_id'] = $empresaId; }
                    if ($hasSegmentoId && $segmentoId !== null) { $mp['segmento_id'] = $segmentoId; }
                    $mp['created_at'] = now();
                    $mp['updated_at'] = now();

                    $modeloId = (int) DB::table('modelos')->insertGetId($mp);
                }

                // ── Vínculo grupo↔modelo ──────────────────────────────────
                if (!$temPivot) {
                    continue;
                }

                $grupoId = $mapaGrupos[$modeloDef['grupo_codigo']] ?? null;
                if ($grupoId === null) {
                    continue;
                }

                $pivotExiste = DB::table('grupo_frota_modelo')
                    ->where('grupo_frota_id', $grupoId)
                    ->where('modelo_id', $modeloId)
                    ->exists();

                if (!$pivotExiste) {
                    DB::table('grupo_frota_modelo')->insert([
                        'grupo_frota_id' => $grupoId,
                        'modelo_id'      => $modeloId,
                    ]);
                }
            }
        }
    }
}
