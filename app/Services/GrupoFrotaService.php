<?php

namespace App\Services;

use App\Models\GrupoFrota;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;
use Illuminate\Support\Facades\DB;

class GrupoFrotaService extends AbstractBaseService
{
    protected Model $model;
    protected TipoOpcionalService $tipoOpcionalService;
    public function __construct(GrupoFrota $grupoFrota, TipoOpcionalService $tipoOpcionalService)
    {
        $this->model = $grupoFrota;
        $this->tipoOpcionalService = $tipoOpcionalService;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof GrupoFrota) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\GrupoFrota");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Summary of create
     * @param array $data
     * @return Model
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Adiciona informações gerais
            $grupoFrota = $this->model->create($data);

            // Sincroniza características
            if (isset($data['caracteristicas'])) {
                $grupoFrota->caracteristicas()->sync($data['caracteristicas']);
            }

            // Sincroniza opcionais
            if (isset($data['opcionais'])) {
                $grupoFrota->opcionais()->sync($data['opcionais']);
            }

            // Sincroniza modelos
            if (isset($data['modelos'])) {
                $grupoFrota->modelos()->sync($data['modelos']);
            }

            return $grupoFrota;
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $item = $this->model->find($id);
            if ($item) {

                //converta valor_diario em numerico
                if (isset($data['valor_diario'])) {
                    $data['valor_diario'] = str_replace(',', '.', str_replace('.', '', $data['valor_diario']));
                }

                // Atualiza as informações gerais
                $item->update($data);


                // Sincroniza características com valores
                if (isset($data['caracteristicas']) && isset($data['caracteristicaValor'])) {
                    $caracteristicas = [];
                    foreach ($data['caracteristicas'] as $k => $caracteristicaId) {
                        $caracteristicas[$caracteristicaId] = ['valor' => $data['caracteristicaValor'][$k]];
                    }
                    $item->caracteristicas()->sync($caracteristicas);
                }

                // Sincroniza opcionais
                if (isset($data['opcionais'])) {
                    $item->opcionais()->sync($data['opcionais']);
                }

                // Sincroniza modelos
                if (isset($data['modelos'])) {
                    $item->modelos()->sync($data['modelos']);
                }

                return $item;
            }
            return null;
        });
    }


    public function getModelosPorGrupoFrota($grupoFrotaId)
    {
        $grupoFrota = $this->model->find($grupoFrotaId);
        if ($grupoFrota) {
            return $grupoFrota->modelos()->get();
        }
        return collect(); // Retorna uma coleção vazia se o grupo de frotas não for encontrado
    }
    public function getTiposOpcionaisComOpcionais($grupoFrotaId)
    {
        // Obtenha todos os tipos opcionais
        $tiposOpcionais = $this->tipoOpcionalService->getAll();

        $grupoFrota = $this->model->find($grupoFrotaId);
        if ($grupoFrota) {
            $opcionais = $grupoFrota->opcionais()->get();

            // Agrupar opcionais por tipo
            $result = [];
            foreach ($tiposOpcionais as $tipoOpcional) {
                $result[$tipoOpcional->id] = [
                    'tipo' => $tipoOpcional,
                    'opcionais' => []
                ];
            }

            foreach ($opcionais as $opcional) {
                $tipoId = $opcional['tipo_opcional_id'];
                if (isset($result[$tipoId])) {
                    $result[$tipoId]['opcionais'][] = $opcional;
                }
            }

            return $result;
        }
        return [];
    }

    /**
     * Obtém o grupo de frota pelo ID do modelo.
     *
     * @param int $modeloId
     * @return array|null
     */
    public function getGrupoFrotaByModelo($modeloId)
    {
        $grupoFrota = $this->model->whereHas('modelos', function ($query) use ($modeloId) {
            $query->where('modelo_id', $modeloId);
        })->select('nome', 'id')->first();

        if ($grupoFrota) {
            return [$grupoFrota->id => $grupoFrota->nome];
        }

        return null;
    }

    public function verificaGruposFrotaDisponiveis($estacao_retirada_id,$data_inicio,$estacao_devolucao_id,$data_fim){

    }
}
