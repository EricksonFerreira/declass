<?php

namespace App\Services;

use App\Exceptions\TipoEStatusMovimentacaoException;
use App\Models\Movimentacao;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MovimentacaoService extends AbstractBaseService
{
    protected Model $model;

    const USUARIO_PADRAO = 6;
    private $mapaMovimentacoes = [
        'adicionarAoEstoque' => ['tipo' => 1, 'status' => 1],
        'realizarCheckIn' => ['tipo' => 2, 'status' => 2],
        'realizarCheckOut' => ['tipo' => 3, 'status' => 1],
        'registrarProblemaMecanico' => ['tipo' => 4, 'status' => 8],
        'transportarParaOficina' => ['tipo' => 5, 'status' => 2],
        'registrarChegadaNaOficina' => ['tipo' => 6, 'status' => 5],
        'registrarRetornoDaOficina' => ['tipo' => 7, 'status' => 2],
        'registrarChegadaNaEstacao' => ['tipo' => 8, 'status' => 1],
        'marcarVeiculoIndisponivel' => ['tipo' => 9, 'status' => 6],
        'transferirEntreEstacoes' => ['tipo' => 10, 'status' => 2],
        'marcarForaDeServico' => ['tipo' => 11, 'status' => 7],
        'registrarVendaDeVeiculo' => ['tipo' => 14, 'status' => 1],
    ];
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

    // /**
    //  * Registra uma movimentação para uma reserva.
    //  *
    //  * @param array $data Os dados da movimentação.
    //  * @param int $reservaId O ID da reserva.
    //  * @param int $tipoMovimentacao O tipo de movimentação (padrão: 11 é efetuar reserva).
    //  * @param int $statusMovimentacao O status da movimentação (padrão: 6 é agendada).
    //  * @return void
    //  */
    // public function registrarMovimentacaoReserva(array $data, int $reservaId,$tipoMovimentacao = 11, $statusMovimentacao = 6)
    // {
    //     $veiculo_id = null;
    //     if(isset($data['modelo_id']) && isset($data['estacao_retirada_id'])){
    //         $veiculo_id = $this->veiculoService->obterVeiculoDisponivelPorModeloEstacao($data['modelo_id'], $data['estacao_retirada_id']);
    //     }
    //     $valor = [
    //         'veiculo_id' => $veiculo_id,
    //         'tipo_movimentacao_id' => $tipoMovimentacao,
    //         'status_movimentacao_id' => $statusMovimentacao,
    //         'local_origem_id' => $data['estacao_retirada_id'] ?? null,
    //         'local_destino_id' => $data['estacao_devolucao_id'] ?? null,
    //         'data_saida' => $data['data_inicio'],
    //         'data_chegada_estimada' => $data['data_fim'],
    //         'reserva_id' => $reservaId ?? null,
    //         'modelo_id' => $data['modelo_id'] ?? null,
    //         'observacoes' => $data['observacoes'] ?? '',
    //         'user_id' => Auth::id() ?? 6
    //     ];

    //      $this->model->create($valor);
    // }

    // public function registraMovimentacaoCadastroVeiculo(array $data, $veiculo_id)
    // {

    //     $statusMovimentacao = Carbon::parse($data['data_hora_disponibilidade'])->gt(Carbon::now()) ? 6 : 1;

    //     $valor = [
    //         'veiculo_id' => $veiculo_id,
    //         'tipo_movimentacao_id' => 1, // adição veiculo
    //         'status_movimentacao_id' => 1, // concluido
    //         'local_destino_id' => $data['local_disponibilidade'] ?? null,
    //         'status_movimentacao_id' => $statusMovimentacao,
    //         'observacoes' => $data['observacoes'] ?? '',
    //         'user_id' => Auth::id() ?? 6
    //     ];

    //     $this->model->create($valor);
    // }

    // public function registraMovimentacaoCancelamentoReserva($reservaId)
    // {
    //     $movimentacao = $this->model->where('reserva_id', $reservaId)->first();

    //     if (!$movimentacao) {
    //         return null;
    //     }

    //     $valor = [
    //         'tipo_movimentacao_id' => 12, // Cancelamento Reserva
    //         'status_movimentacao_id' => 1, // concluido
    //         'observacoes' => 'Cancelamento de Reserva',
    //         'user_id' => Auth::id()
    //     ];

    //     $movimentacao->update($valor);
    // }

    // public function modificaMovimentacaoReserva(array $data, int $reservaId)
    // {
    //     $movimentacao = $this->model->where('reserva_id', $reservaId)->first();

    //     if (!$movimentacao) {
    //         return null;
    //     }

    //     $valor = [
    //         'modelo_id' => $data['modelo'] ?? null,
    //         // 'tipo_movimentacao_id' => '1',
    //         'local_origem_id' => $data['estacao_retirada_id'] ?? null,
    //         'local_destino_id' => $data['estacao_devolucao_id'] ?? null,
    //         // 'status_movimentacao_id' => '6',
    //         // 'reserva_id' => $reservaId ?? null,
    //         'observacoes' => $data['observacoes'] ?? '',
    //         'user_id' => Auth::id()
    //     ];

    //     $movimentacao->update($valor);
    // }

    // public function desativarMovimentacaoReserva($reservaId)
    // {
    //     $movimentacao = $this->model->where('reserva_id', $reservaId)->first();

    //     if (!$movimentacao) {
    //         return null;
    //     }

    //     $valor = [
    //         'status_movimentacao_id' => 3, // cancelado
    //     ];

    //     $movimentacao->update($valor);
    // }

// ------------------------------------------------------------------------------
    // Adição de Veículo
    public function adicionarAoEstoque($veiculo_id, $estacao_id, $observacao = null)
    {
        $this->registrarMovimentacao($veiculo_id, $estacao_id, null,null,null, $observacao, __FUNCTION__);
    }

    // Retirada de Veículo (Check-in)
    public function realizarCheckIn($veiculo_id, $estacao_id, $reserva_id, $observacao = null)
    {
        $this->registrarMovimentacao($veiculo_id, $estacao_id, null, null, $reserva_id, $observacao, __FUNCTION__);
    }

    // Devolução de Veículo (Check-out)
    public function realizarCheckOut($veiculo_id, $estacao_id, $reserva_id, $observacao = null)
    {
        $this->registrarMovimentacao($veiculo_id, null,null,$estacao_id, $reserva_id, $observacao, __FUNCTION__);
    }

    // Problema Mecânico
    public function registrarProblemaMecanico($veiculo_id, $observacao = 'Problema mecânico relatado')
    {
        $this->registrarMovimentacao($veiculo_id, null, null,null, null, $observacao, __FUNCTION__);
    }

    // Transporte para Oficina
    public function transportarParaOficina($veiculo_id, $observacao = 'Veículo transportado para oficina'){
        $this->registrarMovimentacao($veiculo_id, null, null,null,null, $observacao, __FUNCTION__);
    }

    // Chegada à Oficina
    public function registrarChegadaNaOficina($veiculo_id, $observacao = 'Veículo chegou à oficina'){
        $this->registrarMovimentacao($veiculo_id, null, null,null,null, $observacao, __FUNCTION__);
    }

    // Retorno da Oficina
    public function registrarRetornoDaOficina($veiculo_id,$estacao_id, $observacao = 'Veículo retornou da oficina'){
        $this->registrarMovimentacao($veiculo_id, null,null,$estacao_id, null, $observacao, __FUNCTION__);
    }

    // Chegada à Estação
    public function registrarChegadaNaEstacao($veiculo_id, $estacao_id, $observacao = 'Veículo chegou à estação'){
        $this->registrarMovimentacao($veiculo_id, $estacao_id, null,null,null, $observacao, __FUNCTION__);
    }

    // Veículo Indisponível
    public function marcarVeiculoIndisponivel($veiculo_id, $observacao = 'Veículo marcado como indisponível'){
        $this->registrarMovimentacao($veiculo_id,null,null, null, null, $observacao, __FUNCTION__);
    }

    // Transferência Entre Estações
    public function transferirEntreEstacoes($veiculo_id,$estacao_origem_id, $estacao_destino_id, $observacao = 'Veículo transferido entre estações'){
        $this->registrarMovimentacao($veiculo_id,null,$estacao_origem_id, $estacao_destino_id, null, $observacao, __FUNCTION__);
    }

    // Fora de Serviço
    public function marcarForaDeServico($veiculo_id, $observacao = 'Veículo marcado como fora de serviço'){
        $this->registrarMovimentacao($veiculo_id,null,null, null, null, $observacao, __FUNCTION__);
    }

    // Venda de Veículo
    public function registrarVendaDeVeiculo($veiculo_id, $observacao = 'Veículo vendido'){
        $this->registrarMovimentacao($veiculo_id,null,null, null, null, $observacao, __FUNCTION__);
    }


    private function registrarMovimentacao($veiculo_id, $local_id = null, $local_origem_id = null, $local_destino_id = null, $reserva_id = null, $observacao = null, $funcao)
    {
        $movimentacao = $this->obterTipoEStatusDaMovimentacao($funcao);
        $tipoMovimentacao = $movimentacao['tipo'];
        $statusMovimentacao = $movimentacao['status'];

        $valor = [
            'veiculo_id' => $veiculo_id,
            'tipo_movimentacao_id' => $tipoMovimentacao,
            'status_movimentacao_id' => $statusMovimentacao,
            'local_origem_id' =>   $local_id,
            'local_destino_id' => $local_origem_id,
            'local' => $local_destino_id,
            'data_registro' => Carbon::now(),
            'reserva_id' => $reserva_id,
            'observacoes' => $observacao,
            'user_id' => Auth::id() ?? self::USUARIO_PADRAO,
        ];

        $this->model->create($valor);
    }


    private function obterTipoEStatusDaMovimentacao(string $nomeFuncao): array
    {
        return $this->mapaMovimentacoes[$nomeFuncao] ?? throw new TipoEStatusMovimentacaoException("Função desconhecida: $nomeFuncao", 404);
    }
}
