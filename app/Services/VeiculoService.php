<?php

namespace App\Services;

use App\Models\DisponibilidadeInicialVeiculo;
use App\Models\Movimentacao;
use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class VeiculoService extends AbstractBaseService
{
    protected Model $model;
    public MovimentacaoService $movimentacaoService;
    public DisponibilidadeInicialVeiculoService $disponibilidadeInicialVeiculoService;

    public function __construct(Veiculo $veiculo, DisponibilidadeInicialVeiculoService $disponibilidadeInicialVeiculoService)
    {
        $this->model = $veiculo;
        $this->disponibilidadeInicialVeiculoService = $disponibilidadeInicialVeiculoService;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Veiculo) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Veiculo");
        }
        $this->model = $model;
    }


    /**
     * Define o serviço de movimentação.
     *
     * @param MovimentacaoService $movimentacaoService
     */
    public function setMovimentacaoService(MovimentacaoService $movimentacaoService): void
    {
        $this->movimentacaoService = $movimentacaoService;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    // overide

    /**
     * Summary of create

     * @param array $data
     * @override
     * @return Model
     */
    public function create(array $data)
    {
        $veiculo = $this->model->create($data);
        $veiculo->nome = 'vei' . str_pad($veiculo->id, 4, '0', STR_PAD_LEFT);
        $veiculo->save();

        $this->movimentacaoService->registraMovimentacaoCadastroVeiculo($data, $veiculo->id);

        $this->disponibilidadeInicialVeiculoService->create([
            'veiculo_id' => $veiculo->id,
            'estacao_id' => $data['local_disponibilidade'],
            'data_hora' => $data['data_hora_disponibilidade'],
        ]);

        return $veiculo;
    }


    /**
     * Summary of update
     * @param mixed $id
     * @param array $data
     * @override
     * @return Model
     */
    public function update($id, array $data)
    {
        $veiculo = $this->model->find($id);
        $naoMovimentouInicialmente = is_null($veiculo->data_hora_disponibilidade) || is_null($veiculo->local_disponibilidade);
        $veiculo->update($data);

        if($naoMovimentouInicialmente){
            $this->movimentacaoService->registraMovimentacaoCadastroVeiculo($data, $veiculo->id);
            $this->disponibilidadeInicialVeiculoService->create([
                'veiculo_id' => $veiculo->id,
                'estacao_id' => $data['local_disponibilidade'],
                'data_hora' => $data['data_hora_disponibilidade'],
            ]);
        }

        return $veiculo;
    }


    /**
     * Obtém veículos disponíveis por modelo e estação específica.
     *
     * @param int $modeloId O ID do modelo do veículo.
     * @param int $estacaoId O ID da estação onde o veículo está disponível.
     * @return \Illuminate\Database\Eloquent\Collection A coleção de veículos disponíveis.
     */
    public function getVeiculosDisponiveisPorModeloEEstacaoEspecifica($modeloId, $estacaoId)
    {
        return $this->model->where('modelo_id', $modeloId)
            ->where('local_disponibilidade', $estacaoId)
            ->where('status_veiculo_id', 1)
            ->get();
    }

    /**
     * Obtém veículos disponíveis por modelo, exceto em uma estação específica.
     *
     * @param int $modeloId O ID do modelo do veículo.
     * @param int $estacaoId O ID da estação que deve ser excluída.
     * @return \Illuminate\Database\Eloquent\Collection A coleção de veículos disponíveis.
     */
    public function getVeiculosDisponiveisPorModeloExcetoEstacao($modeloId, $estacaoId)
    {
        return $this->model->where('modelo_id', $modeloId)
            ->where('local_disponibilidade', '!=', $estacaoId)
            ->where('status_veiculo_id', 1)
            ->get();
    }

    public function obterVeiculoDisponivelPorModeloEstacao($modeloId, $estacaoId)
    {
        $veiculos = $this->getVeiculosDisponiveisPorModeloEEstacaoEspecifica($modeloId, $estacaoId);

        if ($veiculos->isEmpty()) {
            $veiculos = $this->getVeiculosDisponiveisPorModeloExcetoEstacao($modeloId, $estacaoId);
        }

        if ($veiculos->isEmpty()) {
            throw new \Exception('Nenhum veículo disponível para o modelo e estação informados.');
        }

        return $veiculos->first()->id;
    }

    /**
     * Obtém veículos disponíveis por modelo, estação e intervalo de datas.
     *
     * @param int $modeloId O ID do modelo do veículo.
     * @param int $estacaoId O ID da estação onde o veículo está disponível.
     * @param string $data_inicio A data de início do intervalo.
     * @param string $data_fim A data de fim do intervalo.
     * @param int $reservaId O ID da reserva.
     * @return \Illuminate\Database\Eloquent\Collection A coleção de veículos disponíveis.
     */
    public function getVeiculosDisponiveisPorModeloEEstacaoEIntervaloDatas(int $modeloId, int $estacaoId, string $data_inicio, string $data_fim, int $reservaId = null)
    {

        return $this->model->where('modelo_id', $modeloId)
            ->where('local_disponibilidade', $estacaoId)
            ->where('status_veiculo_id', 1)
            //->get();
            //$query = $this->model
            ->whereDoesntHave('movimentacoes', function ($query) use ($data_inicio, $data_fim, $reservaId) {
                if ($reservaId) {
                    $query->where('reserva_id', '!=', $reservaId);
                }
                $query = $query->whereIn('status_movimentacao_id', [2,6]);
                $query->where(function ($query) use ($data_inicio, $data_fim) {
                    $query->whereBetween('data_saida', [$data_inicio, $data_fim])
                          ->orWhereBetween('data_chegada_estimada', [$data_inicio, $data_fim])
                          ->orWhereBetween('data_chegada_real', [$data_inicio, $data_fim])
                          ->orWhere(function ($query) use ($data_inicio, $data_fim) {
                              $query->where('data_saida', '<=', $data_inicio)
                                    ->where('data_chegada_estimada', '>=', $data_fim)
                                    ->where('data_chegada_real', '>=', $data_fim);
                          });
                });
            })
            ->get();
    }

    /**
     * Obtém veículos ativos que não terão movimentações no período especificado.
     *
     * @param string $data_inicio A data de início do intervalo.
     * @param string $data_fim A data de fim do intervalo.
     * @return \Illuminate\Database\Eloquent\Collection A coleção de veículos ativos sem movimentações no período.
     */
    public function getVeiculosAtivosSemMovimentacoesNoPeriodo(string $data_inicio, string $data_fim)
    {
        // Subquery para obter IDs de veículos com movimentações no período
        $veiculosComMovimentacoes = Movimentacao::where(function ($query) use ($data_inicio, $data_fim) {
            $query->whereBetween('data_inicio', [$data_inicio, $data_fim])
                  ->orWhereBetween('data_fim', [$data_inicio, $data_fim])
                  ->orWhere(function ($query) use ($data_inicio, $data_fim) {
                      $query->where('data_inicio', '<=', $data_inicio)
                            ->where('data_fim', '>=', $data_fim);
                  });
        })->pluck('veiculo_id');

        // Obter veículos ativos que não estão na subquery
        return $this->model->where('status_veiculo_id', 1)
            ->whereNotIn('id', $veiculosComMovimentacoes)
            ->get();
    }

    /**
     * Obtém modelos pelo grupo de frotas que têm veículos disponíveis no período específico e no local informado.
     *
     * @param int $idGrupoFrota O ID do grupo de frotas.
     * @param int $idLocal O ID do local.
     * @param string $data_inicio A data de início do intervalo.
     * @param string $data_fim A data de fim do intervalo.
     * @return \Illuminate\Database\Eloquent\Collection A coleção de modelos com veículos disponíveis.
     */
    public function getModelosComVeiculosDisponiveis(int $idGrupoFrota, int $idLocal, string $data_inicio, string $data_fim)
    {
        return $this->model->where('grupo_frota_id', $idGrupoFrota)
        ->where('local_disponibilidade', $idLocal)
        ->where('status_veiculo_id', 1)
        ->where('data_hora_disponibilidade', '<=',$data_inicio)
        ->where('status_veiculo_id', 1)
        ->whereDoesntHave('movimentacoes', function ($query) use ($data_inicio, $data_fim) {
            $query->where(function ($query) use ($data_inicio, $data_fim) {
                $query->whereBetween('data_saida', [$data_inicio, $data_fim])
                      ->orWhereBetween('data_chegada_estimada', [$data_inicio, $data_fim])
                      ->orWhereBetween('data_chegada_real', [$data_inicio, $data_fim])
                      ->orWhere(function ($query) use ($data_inicio, $data_fim) {
                          $query->where('data_saida', '<=', $data_inicio)
                                ->where('data_chegada_estimada', '>=', $data_fim)
                                ->where('data_chegada_real', '>=', $data_fim);
                      });
            });
        })
        ->with('modelo')
        ->get()
        ->pluck('modelo')
        ->unique('id');
    }

    public function getTotalVeiculos()
    {
        return $this->model->count();
    }

    public function getVeiculosDisponiveis()
    {
        return $this->model->where('status_veiculo_id', 1)->count();
    }

    public function getVeiculosReservados()
    {
        return $this->model->where('status_veiculo_id', 2)->count();
    }

    public function getVeiculosIndisponiveis()
    {
        return $this->model->where('status_veiculo_id', 3)->count();
    }

}
