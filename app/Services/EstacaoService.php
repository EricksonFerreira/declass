<?php

namespace App\Services;

use App\Models\Estacao;
use App\Models\Horario;
use App\Models\HorarioDiario;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EstacaoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Estacao $estacao)
    {
        $this->model = $estacao;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Estacao) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Estacao");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function salvarHorariosDiarios($estacao, $horariosDiarios)
    {
        $estacao->horariosDiarios()->sync($horariosDiarios);
    }

    /**
     * Create a new Estacao and attach active HorarioDiario.
     *
     * @override
     * @param array $data
     * @return \App\Models\Estacao
     */
    public function create(array $data)
    {
        $estacao = $this->model->create($data);

        $this->adicionarHorarios($data,$estacao->id);
        if(isset($data['diasUteis']) && !empty($data['diasUteis'])){
            $this->adicionaDiasUteis($data['diasUteis'], $estacao->id);
        }
        return $estacao;
    }

    /**
     * Update Estacao and attach active HorarioDiario.
     *
     * @override
     * @param $id
     * @param array $data
     */
    public function update($id, array $data)
    {
        $estacao = $this->model->find($id);
        if ($estacao) {
            $estacao->update($data);
        }
        $this->adicionarHorarios($data,$id);
        if(isset($data['diasUteis']) && !empty($data['diasUteis'])){
            $this->adicionaDiasUteis($data['diasUteis'], $id);
        }
    }


    public function salvarNovosHorariosDiarios($estacao)
    {
        $horarios_diarios = HorarioDiario::where('ativo', 'S')->get();

        $horariosSyncData = [];
        foreach ($horarios_diarios as $horario_diario) {
            $horariosSyncData[$horario_diario->id] = ['created_at' => Carbon::now()];
        }

        $estacao->horarios()->sync($horariosSyncData);
    }


    public function adicionaDiasUteis($diasUteis, $estacaoId)
    {
        // Encontrar a estação pelo ID
        $estacao = Estacao::find($estacaoId);

        // Deletar todos os dias úteis da estação
        $estacao->diasUteis()->detach();

        if ($estacao) {
            // Adicionar os dias úteis na tabela pivot
            $estacao->diasUteis()->attach($diasUteis);
        } else {
            // Lidar com o caso em que a estação não é encontrada
            throw new \Exception("Estação não encontrada");
        }
    }

    public function adicionarHorarios($horarios, $estacaoId)
    {
        DB::beginTransaction();

        try {
            // Deletar todos os horários da estação
            Horario::where('estacao_id', $estacaoId)->delete();

            // Iterar sobre cada índice do array de horários
            for ($i = 0; $i < count($horarios['hora_abertura']); $i++) {
                // Filtrar os horários para incluir apenas as chaves desejadas para o índice atual
                $horariosFiltrados = [
                    "estacao_id" => $estacaoId,
                    "horario_diario_id" => $horarios['horario_diario_id'][$i],
                    "hora_abertura" => $horarios["hora_abertura"][$i],
                    "hora_fechamento" => $horarios["hora_fechamento"][$i],
                    "hora_inicio_almoco" => $horarios["hora_inicio_almoco"][$i],
                    "hora_fim_almoco" => $horarios["hora_fim_almoco"][$i],
                    "hora_inicio_fora_expediente" => $horarios["hora_inicio_fora_expediente"][$i],
                    "hora_fim_fora_expediente" => $horarios["hora_fim_fora_expediente"][$i]
                ];

                // Inserir os horários na tabela 'horarios'
                Horario::create($horariosFiltrados);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
