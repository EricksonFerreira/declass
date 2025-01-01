<?php

namespace App\Services;

use App\Exceptions\ReservaJobException;
use App\Models\Reserva;
use App\Traits\LogTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;
use DB;

class ReservaService extends AbstractBaseService
{
    use LogTrait;

    protected Model $model;
    protected MovimentacaoService $movimentacaoService;
    protected OpcionalService $opcionalService;
    protected GrupoFrotaService $grupoFrotaService;
    protected ModeloService $modeloService;
    protected VeiculoService $veiculoService;

    public function __construct(
        Reserva $reserva,
        MovimentacaoService $movimentacaoService,
        OpcionalService $opcionalService,
        GrupoFrotaService $grupoFrotaService,
        ModeloService $modeloService,
        VeiculoService $veiculoService
    ) {
        $this->model = $reserva;
        $this->movimentacaoService = $movimentacaoService;
        $this->opcionalService = $opcionalService;
        $this->grupoFrotaService = $grupoFrotaService;
        $this->modeloService = $modeloService;
        $this->veiculoService = $veiculoService;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Reserva) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Reserva");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    # override
    public function create($data)
    {
        $this->validarDados($data);

        try {

            $dadosReserva = [
                'estacao_retirada_id' => $data['estacao_retirada_id'],
                'estacao_devolucao_id' => $data['estacao_devolucao_id'],
                'data_inicio' => Carbon::parse($data['data_inicio'])->format('Y-m-d H:i:s'),
                'data_fim' => Carbon::parse($data['data_fim'])->format('Y-m-d H:i:s'),
                'grupo_frota_id' => $data['grupo_frota_id'],
                'modelo_id' => $data['modelo_id'],
                'valor_total' => $data['valor_total'],
                'user_id' => $data['user_id'],
                'tarifa_base' => $data['tarifa_base'],
                'quantidade_dias' => $data['quantidade_dias'],
                'status_reserva_id' => 1, // Pendente
                'cupom_desconto_id' => $data['cupom_desconto']['id'] ?? null,
                'valor_desconto' => $data['cupom_desconto']['valor'] ?? null,
                'observacoes' => $data['observacoes'] ?? null,
                'created_at' => now(),
                'updated_at' => null,
            ];

            // Faltou hora inicio e hora fim
            $reserva = $this->model->create($dadosReserva);
            if($reserva == null) {
                return redirect()->back()->with('error', 'Erro ao criar reserva');
            }

            // Criar a reserva
            // $reserva = $this->model->create($data);

            // Atualizar os opcionais
            $this->atualizarOpcionais($reserva, $data);

            // Registra Movimentacao
            $this->movimentacaoService->registrarMovimentacaoReserva([
                'estacao_retirada_id' => $data['estacao_retirada_id'],
                'estacao_devolucao_id' => $data['estacao_devolucao_id'],
                'data_inicio' => Carbon::parse($data['data_inicio'])->format('Y-m-d H:i:s'),
                'data_fim' => Carbon::parse($data['data_fim'])->format('Y-m-d H:i:s'),
                'observacoes' => 'Reserva criada',
                'grupo_frota_id' => $data['grupo_frota_id'],
                'modelo_id' => $data['modelo_id'],

            ], $reserva->id);

            return $reserva;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        $this->validarDados($data);

        // dd($data);
        $reserva = $this->model->find($id);

        if (!$reserva) {
            return null;
        }

        $this->movimentacaoService->desativarMovimentacaoReserva($reserva->id);

        $dadosReserva = [
            'estacao_retirada_id' => $data['estacao_retirada_id'],
            'estacao_devolucao_id' => $data['estacao_devolucao_id'],
            'data_inicio' => Carbon::parse($data['data_inicio'])->format('Y-m-d H:i:s'),
            'data_fim' => Carbon::parse($data['data_fim'])->format('Y-m-d H:i:s'),
            'grupo_frota_id' => $data['grupo_frota_id'],
            'modelo_id' => $data['modelo_id'],
            'user_id' => $data['user_id'],
            'tarifa_base' => $data['tarifa_base'],
            'quantidade_dias' => $data['quantidade_dias'],
            'status_reserva_id' => 1, // Pendente
            'cupom_desconto_id' => $data['cupom_desconto']['id'] ?? null,
            'valor_desconto' => $data['cupom_desconto']['valor'] ?? null,
            'observacoes' => $data['observacoes'] ?? null,
            'updated_at' => now(),
        ];

        // Atualizar a reserva
        $reserva->update($dadosReserva);


        // Atualizar os opcionais
        $this->atualizarOpcionais($reserva, $data);

        // Registra Movimentacao
        // $this->movimentacaoService->modificaMovimentacaoReserva($dadosReserva, $reserva->id);

        // Registra Movimentacao
        $this->movimentacaoService->registrarMovimentacaoReserva([
            'estacao_retirada_id' => $data['estacao_retirada_id'],
            'estacao_devolucao_id' => $data['estacao_devolucao_id'],
            'data_inicio' => Carbon::parse($data['data_inicio'])->format('Y-m-d H:i:s'),
            'data_fim' => Carbon::parse($data['data_fim'])->format('Y-m-d H:i:s'),
            'observacoes' => 'Reserva criada',
            'grupo_frota_id' => $data['grupo_frota_id'],
            'modelo_id' => $data['modelo_id'],

        ], $reserva->id);

        return $reserva;
    }


    /**
     * Obtém todos os modelos que têm veículos disponíveis em um local específico para todos os grupos de frotas.
     *
     * @param int $localId O ID do local.
     * @return array A lista de modelos com veículos disponíveis.
     */
    public function obterModelosComVeiculosDisponiveisPorGrupoFrota(int $grupoFrotaId, int $localId, $data_inicio, $data_fim,$reservaId = null): array
    {
        $gruposFrota = $this->grupoFrotaService->getById($grupoFrotaId);
        $modelosDisponiveis = [];

        // foreach ($gruposFrota as $grupo) {
        $modelos = $this->modeloService->getModelosPorGrupoFrota($gruposFrota->id);

        foreach ($modelos as $modelo) {
            $veiculosDisponiveis = $this->veiculoService->getVeiculosDisponiveisPorModeloEEstacaoEIntervaloDatas($modelo->id, $localId, $data_inicio, $data_fim,$reservaId);

            if ($veiculosDisponiveis->isNotEmpty()) {
                $modelosDisponiveis[] = $modelo;
            }
        }
        // }

        return $modelosDisponiveis;
    }

    /**
     * Atualiza os opcionais de uma reserva.
     *
     * @param Reserva $reserva
     * @param array $data
     * @return void
     */
    private function atualizarOpcionais(Reserva $reserva, array $data)
    {
        // Desanexar todos os opcionais existentes
        $reserva->opcionais()->detach();

        // Verificar se há opcionais para anexar
        if (isset($data['opcionais']) && !empty($data['opcionais'])) {
            $opcionais = [];
            foreach ($data['opcionais'] as $opcional) {


                // dd($opcional);
                $preco_total = $opcional['tipo_cobranca'] == 'por_dia' ? $opcional['preco_por_dia'] * $data['quantidade_dias'] : $opcional['preco_integral'];

                $opcionais[$opcional['id']] = [
                    'preco_total' => $preco_total, // Valor padrão para preço total
                    'quantidade' => $opcional['quantidade'] // Valor padrão para quantidade
                ];
            }
            // Anexar os novos opcionais com valores
            $opcionais = array_map(function($opcional) {
                $opcional['created_at'] = now();
                $opcional['updated_at'] = now();
                return $opcional;
            }, $opcionais);

            $reserva->opcionais()->attach($opcionais);
        }
    }
    public function atualizarStatusReservasConfirmadasNaoRetiradasParaAtrasadas()
    {
        $statusReserva = [2]; // Confirmada
        $statusAtrasado = 6;
        $this->atualizarStatusReservasPorTipo(
            $statusReserva,
            $statusAtrasado,
            'Reserva atrasada pela rotina de atraso',
            'Erro ao atualizar status das reservas confirmadas para atrasadas'
        );

        $idJob = 1; // Rotina de atraso das reservas confirmadas e nao retiradas
        $this->adicionaJobLog($idJob);
    }

    public function atualizarStatusReservasAtrasadasParaExpiradas()
    {
        $statusReserva = [6]; // Atrasada
        $statusExpirada = 7;
        $horasLimite = 2;
        $this->atualizarStatusReservasPorTipo(
            $statusReserva,
            $statusExpirada,
            'Reserva expirada pela rotina de expiração',
            'Erro ao atualizar status das reservas atrasadas para expiradas',
            $horasLimite
        );

        $idJob = 2; // Rotina de expiração das reservas atrasadas
        $this->adicionaJobLog($idJob);

    }

    public function atualizarStatusReservasCriadasParaExpiradas()
    {
        $statusReserva = [1]; // Criada
        $statusExpirada = 7;
        $this->atualizarStatusReservasPorTipo(
            $statusReserva,
            $statusExpirada,
            'Reserva expirada pela rotina de expiração',
            'Erro ao atualizar status das reservas criadas para expiradas'
        );

        $idJob = 3; // Rotina de expiração de reservas criadas
        $this->adicionaJobLog($idJob);
    }

    // public function cancelarReservasAguardandoPagamentoExpiradas()
    // {
    //     $statusReserva = [10, 1]; // Aguardando Pagamento e Pendente
    //     $statusCancelado = 3;

    //     DB::beginTransaction();
    //     try {
    //         $reservas = $this->model->where('data_inicio', '<', now())->whereIn('status_reserva_id', $statusReserva)->get();
    //         foreach ($reservas as $reserva) {
    //             $reserva->status_reserva_id = $statusCancelado;
    //             $reserva->save();
    //             $this->movimentacaoService->registrarMovimentacaoReserva(
    //                 ['observacoes' => 'Reserva cancelada por expiração de prazo de pagamento pela rotina de cancelamento'],
    //                 $reserva->id,
    //                 6,
    //                 3
    //             );
    //         }
    //         DB::commit();
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         throw new \Exception($e->getMessage());
    //     }
    // }

    // public function atualizarStatusReservasPagasNaoRetiradasParaAtrasadas()
    // {
    //     $statusReserva = [2]; // Confirmada
    //     $statusAtrasado = 6;
    //     DB::beginTransaction();
    //     try {
    //         $reservas = $this->model->where('data_inicio', '<', now())->whereIn('status_reserva_id', $statusReserva)->get();
    //         foreach ($reservas as $reserva) {
    //             $reserva->status_reserva_id = $statusAtrasado;
    //             $reserva->save();
    //             $this->movimentacaoService->registrarMovimentacaoReserva(
    //                 ['observacoes' => 'Reserva atrasada por não retirada pela rotina de atraso'],
    //                 $reserva->id,
    //                 12,
    //                 7
    //             );
    //         }
    //         DB::commit();
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         throw new \Exception($e->getMessage());
    //     }
    // }

    public function calculaQuantidadeDias($data_inicio, $data_fim)
    {
        $data_inicio = new \DateTime($data_inicio);
        $data_fim = new \DateTime($data_fim);
        $intervalo = $data_inicio->diff($data_fim);

        return $intervalo->days + 1;
    }

    private function validarDados(array $data,$reservaId = null)
    {

        if($data['data_inicio'] == null || $data['data_fim'] == null) {
            return redirect()->back()->with('error', 'Data de início ou fim da reserva não informada');
        }

        if($data['estacao_retirada_id'] == null || $data['estacao_devolucao_id'] == null) {
        // if($data['estacao_retirada'] == null || $data['estacao_devolucao'] == null) {
            return redirect()->back()->with('error', 'Estação de retirada ou devolução não informada');
        }

        if($data['grupo_frota_id'] == null) {
            return redirect()->back()->with('error', 'Grupo de frota não informado');
        }

        if($data['modelo_id'] == null) {
            return redirect()->back()->with('error', 'Modelo não informado');
        }

        if($data['user_id'] == null) {
            return redirect()->back()->with('error', 'Usuário não informado');
        }

        if($data['data_inicio'] >= $data['data_fim']) {
            return redirect()->back()->with('error', 'Data de início não pode ser maior ou igual a data de fim');
        }

        if($data['data_inicio'] < now()) {
            return redirect()->back()->with('error', 'Data de início não pode ser menor que a data atual');
        }

        if($data['data_fim'] < now()) {
            return redirect()->back()->with('error', 'Data de fim não pode ser menor que a data atual');
        }

        // Verificar se existe raealmente alguem veiculo disponivel para a reserva
        $veiculoDisponivel = $this->veiculoService->getVeiculosDisponiveisPorModeloEEstacaoEIntervaloDatas($data['modelo_id'], $data['estacao_retirada_id'], $data['data_inicio'], $data['data_fim'],$reservaId);
        // dd($veiculoDisponivel);
        if($veiculoDisponivel->isEmpty()) {
            // dd($veiculoDisponivel);
            return redirect()->back()->with('error', 'Não existe veículo disponível para a reserva');
        }

    }

    public function adicionaLog($reservaId, $userId, $json){
        DB::table('reserva_logs')->insert([
            'reserva_id' => $reservaId,
            'user_id' => $userId,
            'detalhes' => $json,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
    public function processarDadosReserva($reservaData)
    {
        $data = $this->converteObjetoReservaParaArray($reservaData);

        $data['valor_opcionais'] = $this->calculaValorOpcionais($data['opcionais'], $data['quantidade_dias']);
        $data['valor_total_por_dias'] = $this->calcularValorTotalPorDias($data['tarifa_base'], $data['quantidade_dias']);
        $data['valor_total'] = $this->calcularValorTotalReserva($data['valor_total_por_dias'], $data['valor_opcionais'], $data['valor_desconto'] ?? 0);

        return $data;
    }
    private function converteObjetoReservaParaArray($objeto){
        return array_map(function($item) {
            if (is_object($item)) {
                return json_decode(json_encode($item), true);
            } elseif (is_array($item)) {
                return array_map(function($subItem) {
                    return is_object($subItem) ? json_decode(json_encode($subItem), true) : $subItem;
                }, $item);
            }
            return $item;
        }, (array) $objeto);
    }
    private function calculaValorOpcionais($opcionais,$quantidadeDias){
        $valorOpcionais = 0;
        foreach($opcionais as $opcional){
            if($opcional['tipo_cobranca'] == 'por_dia'){
                $valorOpcionais += ($opcional['preco_por_dia'] * $opcional['quantidade']) * $quantidadeDias;
            }else{
                $valorOpcionais += $opcional['preco_integral'];
            }
        }
        return $valorOpcionais;
    }
    private function calcularValorTotalPorDias($tarifaBase, $quantidadeDias){
        return $tarifaBase * $quantidadeDias;
    }
    private function calcularValorTotalReserva($valorTotalPorDias, $valorOpcionais, $valorDesconto){
        $valorTotal = ($valorTotalPorDias + $valorOpcionais);

        if($valorDesconto > 0){
            $valorTotalComDesconto = $valorTotal - ($valorTotal * ($valorDesconto / 100));
        }else{
            $valorTotalComDesconto = $valorTotal;
        }

        return number_format($valorTotalComDesconto, 2, '.', ',');
    }
    private function atualizarStatusReservasPorTipo(array $statusReserva, int $novoStatus, string $observacao,string $mensagemErro, int $horasLimite = null)
    {
        DB::beginTransaction();
        try {
            $query = $this->model->where('data_inicio', '<', now())
                ->whereIn('status_reserva_id', $statusReserva);

            if ($horasLimite) {
                $query->where('created_at', '<', now()->subHours($horasLimite));
            }

            $reservas = $query->get();

            foreach ($reservas as $reserva) {
                $reserva->status_reserva_id = $novoStatus;
                $reserva->save();
                $this->movimentacaoService->registrarMovimentacaoReserva(
                    ['observacoes' => $observacao],
                    $reserva->id,
                    12,
                    7
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ReservaJobException($mensagemErro);
        }
    }
}
// public class ReservaDTO{
//     {#1678 ▼ // app\Http\Controllers\ReservaController.php:124
//   +"tarifa_base": "75.00"
//   +"quantidade_dias": 5
//   +"estacao_retirada": {#1688 ▼
//     +"id": "6"
//     +"nome": "Xpand Cars6"
//   }
//   +"data_inicio": "2024-09-26T08:09"
//   +"estacao_devolucao": {#1687 ▼
//     +"id": "7"
//     +"nome": "Xpand Cars7"
//   }
//   +"data_fim": "2024-10-01T08:09"
//   +"grupo_frota": {#1723 ▼
//     +"id": 1
//     +"nome": "Wagon/Estate Auto Unspecified Drive Diesel Air"
//   }
//   +"modelo": {#1719 ▼value:
//     +"id": null
//     +"nome": null
//   }
//   +"opcionais": array:3 [▼
//     0 => {#1720 ▼
//       +"id": 1
//       +"nome": "Cadeira de Criança"
//       +"tipo_cobranca": "por_dia"
//       +"tipo_opcional_id": 1
//       +"preco_por_dia": "17.00"
//       +"preco_integral": null
//       +"descricao": null
//       +"ativo": "S"
//       +"created_at": "2024-08-23T10:50:51.000000Z"
//       +"updated_at": "2024-08-23T16:18:41.000000Z"
//       +"pivot": {#1721 ▶}
//       +"quantidade": 1
//     }
//     1 => {#1724 ▶}
//     2 => {#1728 ▶}
//   ]
//   +"user": null
//   +"status": null
//   +"cupom_desconto": null
// }



///}
