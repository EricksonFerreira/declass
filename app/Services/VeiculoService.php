<?php

namespace App\Services;

use App\Models\DisponibilidadeInicialVeiculo;
use App\Models\Movimentacao;
use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VeiculoService extends AbstractBaseService
{
    protected Model $model;
    public MovimentacaoService $movimentacaoService;
    public DisponibilidadeInicialVeiculoService $disponibilidadeInicialVeiculoService;
    private ?array $statusVeiculoDisponivelIds = null;

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
        $this->injectEmpresaId($data, true);
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
        $veiculo = $this->applyEmpresaScope($this->model->newQuery())->find($id);
        if (!$veiculo) {
            return null;
        }

        $this->injectEmpresaId($data, true);
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
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->where('modelo_id', $modeloId)
            ->where('local_disponibilidade', $estacaoId)
            ->whereNotNull('local_disponibilidade');

        $query = $this->scopeStatusVeiculoDisponivel($query);

        return $query->get();
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
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->where('modelo_id', $modeloId)
            ->where('local_disponibilidade', '!=', $estacaoId)
            ->whereNotNull('local_disponibilidade');

        $query = $this->scopeStatusVeiculoDisponivel($query);

        return $query->get();
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
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->where('modelo_id', $modeloId)
            ->where('local_disponibilidade', $estacaoId)
            ->where(function ($query) use ($data_inicio) {
                $query->whereNull('data_hora_disponibilidade')
                    ->orWhere('data_hora_disponibilidade', '<=', $data_inicio);
            })
            ->whereDoesntHave('movimentacoes', function ($query) use ($data_inicio, $data_fim, $reservaId) {
                if ($reservaId) {
                    $query->where('movimentacoes.reserva_id', '!=', $reservaId);
                }

                $query->join('reservas', 'reservas.id', '=', 'movimentacoes.reserva_id')
                    ->whereIn('reservas.status_reserva_id', [1, 2, 4, 6])
                    ->where('reservas.data_inicio', '<', $data_fim)
                    ->where('reservas.data_fim', '>', $data_inicio);
            });

        $query = $this->scopeStatusVeiculoDisponivel($query);

        return $query->get();
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
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->whereNotIn('id', $veiculosComMovimentacoes)
            ->whereNotNull('local_disponibilidade');

        $query = $this->scopeStatusVeiculoDisponivel($query);

        return $query->get();
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
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->whereHas('modelo.gruposFrota', function ($query) use ($idGrupoFrota) {
                $query->where('grupos_frota.id', $idGrupoFrota);
            })
            ->where('local_disponibilidade', $idLocal)
            ->where(function ($query) use ($data_inicio) {
                $query->whereNull('data_hora_disponibilidade')
                    ->orWhere('data_hora_disponibilidade', '<=', $data_inicio);
            })
            ->whereDoesntHave('movimentacoes', function ($query) use ($data_inicio, $data_fim) {
                $query->join('reservas', 'reservas.id', '=', 'movimentacoes.reserva_id')
                    ->whereIn('reservas.status_reserva_id', [1, 2, 4, 6])
                    ->where('reservas.data_inicio', '<', $data_fim)
                    ->where('reservas.data_fim', '>', $data_inicio);
            })
            ->with('modelo');

        $query = $this->scopeStatusVeiculoDisponivel($query);

        return $query
            ->get()
            ->pluck('modelo')
            ->filter()
            ->unique('id')
            ->values();
    }

    /**
     * Retorna o total de veiculos ativos por estacao.
     *
     * @param array<int, int|string> $estacoesIds
     * @return array<int, int>
     */
    public function getQuantidadeVeiculosAtivosPorEstacao(array $estacoesIds = []): array
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->whereNotNull('local_disponibilidade');

        $query = $this->scopeStatusVeiculoDisponivel($query);

        if (!empty($estacoesIds)) {
            $query->whereIn('local_disponibilidade', array_map('intval', $estacoesIds));
        }

        $agrupados = $query
            ->selectRaw('local_disponibilidade, COUNT(*) as total')
            ->groupBy('local_disponibilidade')
            ->pluck('total', 'local_disponibilidade')
            ->toArray();

        $resultado = [];
        if (!empty($estacoesIds)) {
            foreach ($estacoesIds as $estacaoId) {
                $id = (int) $estacaoId;
                $resultado[$id] = (int) ($agrupados[$id] ?? 0);
            }

            return $resultado;
        }

        foreach ($agrupados as $estacaoId => $total) {
            $resultado[(int) $estacaoId] = (int) $total;
        }

        return $resultado;
    }

    public function getQuantidadeVeiculosDisponiveisPorEstacaoEPeriodo(
        int $estacaoId,
        string $dataInicio,
        string $dataFim,
        ?int $reservaId = null
    ): int {
        if ($estacaoId <= 0 || trim($dataInicio) === '' || trim($dataFim) === '') {
            return 0;
        }

        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->where('local_disponibilidade', $estacaoId)
            ->where(function ($subQuery) use ($dataInicio) {
                $subQuery->whereNull('data_hora_disponibilidade')
                    ->orWhere('data_hora_disponibilidade', '<=', $dataInicio);
            })
            ->whereDoesntHave('movimentacoes', function ($subQuery) use ($dataInicio, $dataFim, $reservaId) {
                if ($reservaId) {
                    $subQuery->where('movimentacoes.reserva_id', '!=', $reservaId);
                }

                $subQuery->join('reservas', 'reservas.id', '=', 'movimentacoes.reserva_id')
                    ->whereIn('reservas.status_reserva_id', [1, 2, 4, 6])
                    ->where('reservas.data_inicio', '<', $dataFim)
                    ->where('reservas.data_fim', '>', $dataInicio);
            });

        $query = $this->scopeStatusVeiculoDisponivel($query);

        return (int) $query->count();
    }

    public function getTotalVeiculos()
    {
        return $this->model->count();
    }

    public function getVeiculosDisponiveis()
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()));
        $query = $this->scopeStatusVeiculoDisponivel($query);
        return (int) $query->count();
    }

    public function getVeiculosReservados()
    {
        return $this->model->where('status_veiculo_id', 2)->count();
    }

    public function getVeiculosIndisponiveis()
    {
        return $this->model->where('status_veiculo_id', 3)->count();
    }

    public function getVeiculosDisponiveisParaCheckin($estacao_id){
        // Falta corrigir essa lógica
        return $this->model->where('local_disponibilidade', $estacao_id)
            ->where('disponivel','S')
            ->get();
    }

    private function scopeStatusVeiculoDisponivel($query)
    {
        $idsDisponiveis = $this->getStatusVeiculoDisponivelIds();

        if (empty($idsDisponiveis)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('status_veiculo_id', $idsDisponiveis);
    }

    private function getStatusVeiculoDisponivelIds(): array
    {
        if ($this->statusVeiculoDisponivelIds !== null) {
            return $this->statusVeiculoDisponivelIds;
        }

        $query = DB::table('status_veiculos')->select('id', 'nome');

        if (Schema::hasColumn('status_veiculos', 'ativo')) {
            $query->where('ativo', 'S');
        }

        if (Schema::hasColumn('status_veiculos', 'empresa_id')) {
            $empresaId = $this->currentEmpresaId();
            if ($empresaId !== null) {
                $query->where('empresa_id', $empresaId);
            }
        }

        $status = $query->get();

        $ids = [];
        foreach ($status as $item) {
            $nome = mb_strtolower(trim((string) $item->nome));
            if (in_array($nome, ['ativo', 'disponivel'], true)) {
                $ids[] = (int) $item->id;
            }
        }

        if (empty($ids)) {
            $fallback = DB::table('status_veiculos')->select('id');

            if (Schema::hasColumn('status_veiculos', 'empresa_id')) {
                $empresaId = $this->currentEmpresaId();
                if ($empresaId !== null) {
                    $fallback->where('empresa_id', $empresaId);
                }
            }

            $ids = $fallback
                ->where('id', 1)
                ->pluck('id')
                ->map(static fn ($id) => (int) $id)
                ->toArray();
        }

        $this->statusVeiculoDisponivelIds = array_values(array_unique($ids));

        return $this->statusVeiculoDisponivelIds;
    }


}

