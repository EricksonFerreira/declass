<?php

namespace App\Services;

use App\Models\Movimentacao;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MovimentacaoService extends AbstractBaseService
{
    protected Model $model;
    public VeiculoService $veiculoService;

    public function __construct(
        Movimentacao $movimentacao,
    ) {
        $this->model = $movimentacao;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Movimentacao) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Movimentacao");
        }
        $this->model = $model;
    }


    /**
     * Define o serviço de veículos.
     *
     * @param VeiculoService $veiculoService
     */
    public function setVeiculoService(VeiculoService $veiculoService): void
    {
        $this->veiculoService = $veiculoService;
    }


    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Registra uma movimentação para uma reserva.
     *
     * @param array $data Os dados da movimentação.
     * @param int $reservaId O ID da reserva.
     * @param int $tipoMovimentacao O tipo de movimentação (padrão: 11 é efetuar reserva).
     * @param int $statusMovimentacao O status da movimentação (padrão: 6 é agendada).
     * @return void
     */
    public function registrarMovimentacaoReserva(array $data, int $reservaId,$tipoMovimentacao = 11, $statusMovimentacao = 6)
    {
        $veiculo_id = null;
        if(isset($data['modelo_id']) && isset($data['estacao_retirada_id'])){
            $veiculo_id = $this->veiculoService->obterVeiculoDisponivelPorModeloEstacao($data['modelo_id'], $data['estacao_retirada_id']);
        }
        $valor = [
            'veiculo_id' => $veiculo_id,
            'tipo_movimentacao_id' => $tipoMovimentacao,
            'status_movimentacao_id' => $statusMovimentacao,
            'local_origem_id' => $data['estacao_retirada_id'] ?? null,
            'local_destino_id' => $data['estacao_devolucao_id'] ?? null,
            'data_saida' => $data['data_inicio'],
            'data_chegada_estimada' => $data['data_fim'],
            'reserva_id' => $reservaId ?? null,
            'modelo_id' => $data['modelo_id'] ?? null,
            'observacoes' => $data['observacoes'] ?? '',
            'user_id' => Auth::id() ?? 6
        ];

         $this->model->create($valor);
    }

    public function registraMovimentacaoCadastroVeiculo(array $data, $veiculo_id)
    {

        $statusMovimentacao = Carbon::parse($data['data_hora_disponibilidade'])->gt(Carbon::now()) ? 6 : 1;

        $valor = [
            'veiculo_id' => $veiculo_id,
            'tipo_movimentacao_id' => 1, // adição veiculo
            'status_movimentacao_id' => 1, // concluido
            'local_destino_id' => $data['local_disponibilidade'] ?? null,
            'status_movimentacao_id' => $statusMovimentacao,
            'observacoes' => $data['observacoes'] ?? '',
            'user_id' => Auth::id() ?? 6
        ];

        $this->model->create($valor);
    }

    public function registraMovimentacaoCancelamentoReserva($reservaId)
    {
        $movimentacao = $this->model->where('reserva_id', $reservaId)->first();

        if (!$movimentacao) {
            return null;
        }

        $valor = [
            'tipo_movimentacao_id' => 12, // Cancelamento Reserva
            'status_movimentacao_id' => 1, // concluido
            'observacoes' => 'Cancelamento de Reserva',
            'user_id' => Auth::id()
        ];

        $movimentacao->update($valor);
    }

    public function modificaMovimentacaoReserva(array $data, int $reservaId)
    {
        $movimentacao = $this->model->where('reserva_id', $reservaId)->first();

        if (!$movimentacao) {
            return null;
        }

        $valor = [
            'modelo_id' => $data['modelo'] ?? null,
            // 'tipo_movimentacao_id' => '1',
            'local_origem_id' => $data['estacao_retirada_id'] ?? null,
            'local_destino_id' => $data['estacao_devolucao_id'] ?? null,
            // 'status_movimentacao_id' => '6',
            // 'reserva_id' => $reservaId ?? null,
            'observacoes' => $data['observacoes'] ?? '',
            'user_id' => Auth::id()
        ];

        $movimentacao->update($valor);
    }

    public function desativarMovimentacaoReserva($reservaId)
    {
        $movimentacao = $this->model->where('reserva_id', $reservaId)->first();

        if (!$movimentacao) {
            return null;
        }

        $valor = [
            'status_movimentacao_id' => 3, // cancelado
        ];

        $movimentacao->update($valor);
    }
}
