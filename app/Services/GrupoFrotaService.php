<?php

namespace App\Services;

use App\Models\GrupoFrota;
use Illuminate\Database\Eloquent\Model;
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
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\GrupoFrota');
        }

        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data = $this->normalizarCamposMonetarios($data);

            $grupoFrota = $this->model->create($data);

            if (isset($data['caracteristicas'])) {
                $grupoFrota->caracteristicas()->sync($data['caracteristicas']);
            }

            if (isset($data['opcionais'])) {
                $grupoFrota->opcionais()->sync($data['opcionais']);
            }

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
            if (!$item) {
                return null;
            }

            $data = $this->normalizarCamposMonetarios($data);
            $item->update($data);

            if (isset($data['caracteristicas']) && isset($data['caracteristicaValor'])) {
                $caracteristicas = [];
                foreach ($data['caracteristicas'] as $k => $caracteristicaId) {
                    $caracteristicas[$caracteristicaId] = ['valor' => $data['caracteristicaValor'][$k]];
                }
                $item->caracteristicas()->sync($caracteristicas);
            }

            if (isset($data['opcionais'])) {
                $item->opcionais()->sync($data['opcionais']);
            }

            if (isset($data['modelos'])) {
                $item->modelos()->sync($data['modelos']);
            }

            return $item;
        });
    }

    public function getModelosPorGrupoFrota($grupoFrotaId)
    {
        $grupoFrota = $this->model->find($grupoFrotaId);
        if ($grupoFrota) {
            return $grupoFrota->modelos()->get();
        }

        return collect();
    }

    public function getTiposOpcionaisComOpcionais($grupoFrotaId)
    {
        $tiposOpcionais = $this->tipoOpcionalService->getAll();

        $grupoFrota = $this->model->find($grupoFrotaId);
        if (!$grupoFrota) {
            return [];
        }

        $opcionais = $grupoFrota->opcionais()->get();
        $result = [];

        foreach ($tiposOpcionais as $tipoOpcional) {
            $result[$tipoOpcional->id] = [
                'tipo' => $tipoOpcional,
                'opcionais' => [],
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

    public function verificaGruposFrotaDisponiveis($estacao_retirada_id, $data_inicio, $estacao_devolucao_id, $data_fim)
    {
    }

    private function normalizarCamposMonetarios(array $data): array
    {
        if (!array_key_exists('valor_diario', $data)) {
            return $data;
        }

        $normalizado = $this->normalizarDecimal($data['valor_diario']);
        if ($normalizado !== null) {
            $data['valor_diario'] = $normalizado;
        }

        return $data;
    }

    private function normalizarDecimal($valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        if (is_int($valor) || is_float($valor)) {
            return (string) $valor;
        }

        $texto = trim((string) $valor);
        if ($texto === '') {
            return null;
        }

        $texto = str_replace(['R$', 'EUR', '$', ' '], '', $texto);
        $texto = preg_replace('/[^0-9,.\-]/', '', $texto) ?? '';
        if ($texto === '') {
            return null;
        }

        if (str_contains($texto, ',') && str_contains($texto, '.')) {
            $ultimaVirgula = strrpos($texto, ',');
            $ultimoPonto = strrpos($texto, '.');

            if ($ultimaVirgula !== false && $ultimoPonto !== false && $ultimaVirgula > $ultimoPonto) {
                $texto = str_replace('.', '', $texto);
                $texto = str_replace(',', '.', $texto);
            } else {
                $texto = str_replace(',', '', $texto);
            }
        } elseif (str_contains($texto, ',')) {
            $texto = str_replace('.', '', $texto);
            $texto = str_replace(',', '.', $texto);
        } elseif (substr_count($texto, '.') > 1) {
            $ultimaPosicao = strrpos($texto, '.');
            $parteInteira = str_replace('.', '', substr($texto, 0, $ultimaPosicao));
            $parteDecimal = substr($texto, $ultimaPosicao + 1);
            $texto = $parteInteira . '.' . $parteDecimal;
        }

        return $texto;
    }
}

