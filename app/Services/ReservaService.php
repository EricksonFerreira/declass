<?php

namespace App\Services;

use App\Exceptions\ReservaJobException;
use App\Models\Reserva;
use App\Models\Empresa;
use App\Traits\LogTrait;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;
use DB;
use Illuminate\Support\Facades\Schema;

class ReservaService extends AbstractBaseService
{
    use LogTrait;

    protected Model $model;
    protected MovimentacaoService $movimentacaoService;
    protected OpcionalService $opcionalService;
    protected GrupoFrotaService $grupoFrotaService;
    protected ModeloService $modeloService;
    protected VeiculoService $veiculoService;
    protected SaldoService $saldoService;

    public function __construct(
        Reserva $reserva,
        MovimentacaoService $movimentacaoService,
        OpcionalService $opcionalService,
        GrupoFrotaService $grupoFrotaService,
        ModeloService $modeloService,
        VeiculoService $veiculoService,
        SaldoService $saldoService
    ) {
        $this->model = $reserva;
        $this->movimentacaoService = $movimentacaoService;
        $this->opcionalService = $opcionalService;
        $this->grupoFrotaService = $grupoFrotaService;
        $this->modeloService = $modeloService;
        $this->veiculoService = $veiculoService;
        $this->saldoService = $saldoService;
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
        $mensagemErroValidacao = $this->validarDados($data);

        if($mensagemErroValidacao) {
            return redirect()->back()->with('error', $mensagemErroValidacao);
        }

        try {

            $dadosReserva = [
                'estacao_retirada_id' => $data['estacao_retirada_id'],
                'estacao_devolucao_id' => $data['estacao_devolucao_id'],
                'data_inicio' => Carbon::parse($data['data_inicio'])->format('Y-m-d H:i:s'),
                'data_fim' => Carbon::parse($data['data_fim'])->format('Y-m-d H:i:s'),
                'grupo_frota_id' => $data['grupo_frota_id'],
                'modelo_id' => $data['modelo_id'],
                'valor_total_final' => $data['valor_total_final'],
                'valor_total_com_desconto' => $data['valor_total_com_desconto'],
                'valor_total_tarifa_base' => $data['valor_total_tarifa_base'],
                'valor_opcionais' => $data['valor_opcionais'],
                'percentual_desconto' => $data['percentual_desconto'],
                'user_id' => $data['user_id'],
                'tarifa_base' => $data['tarifa_base'],
                'quantidade_dias' => $data['quantidade_dias'],
                'status_reserva_id' => 1, // Pendente
                'cupom_desconto_id' => $data['cupom_desconto']['id'] ?? null,
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

            return $reserva;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        $mensagemErroValidacao = $this->validarDados($data);

        if($mensagemErroValidacao) {
            return redirect()->back()->with('error', $mensagemErroValidacao);
        }

        $reserva = $this->model->find($id);

        if (!$reserva) {
            return null;
        }


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


        return $reserva;
    }


    public function cancelar($id)
    {
        $reserva = $this->model->find($id);
        if (!$reserva) {
            return;
        }

        DB::beginTransaction();
        try {
            $reserva->status_reserva_id = 3; // Cancelada

            if (Schema::hasColumn($this->model->getTable(), 'data_cancelamento')) {
                $reserva->data_cancelamento = now();
            }

            // Cancela reserva e libera vínculo do veículo (quando aplicável)
            if (Schema::hasColumn($this->model->getTable(), 'veiculo_id')) {
                $reserva->veiculo_id = null;
            }

            $reserva->save();

            // Cancela movimentações vinculadas para não bloquear disponibilidade
            if (isset($this->movimentacaoService)) {
                $this->movimentacaoService->cancelarMovimentacoesReserva((int) $id, null, 'Cancelada por cancelamento de reserva');
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
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
        $statusExpirada = 11;
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
        $statusExpirada = 11;

        $this->atualizarStatusReservasPorTipo(
            $statusReserva,
            $statusExpirada,
            'Reserva expirada pela rotina de expiração',
            'Erro ao atualizar status das reservas criadas para expiradas',
            null,
            13,
            1
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
            return 'Data de início ou fim da reserva não informada';
        }

        if($data['estacao_retirada_id'] == null || $data['estacao_devolucao_id'] == null) {
        // if($data['estacao_retirada'] == null || $data['estacao_devolucao'] == null) {
            return 'Estação de retirada ou devolução não informada';
        }

        if($data['grupo_frota_id'] == null) {
            return 'Grupo de frota não informado';
        }

        if($data['modelo_id'] == null) {
            return 'Modelo não informado';
        }

        if($data['user_id'] == null) {
            return 'Usuário não informado';
        }

        if($data['data_inicio'] >= $data['data_fim']) {
            return 'Data de início não pode ser maior ou igual a data de fim';
        }

        if($data['data_inicio'] < now()) {
            return 'Data de início não pode ser menor que a data atual';
        }

        if($data['data_fim'] < now()) {
            return 'Data de fim não pode ser menor que a data atual';
        }

        // Validar funcionamento da estacao (dias uteis/feriados/horarios) para retirada e devolucao
        $msg = $this->validarFuncionamentoEstacao((int) $data['estacao_retirada_id'], (string) $data['data_inicio'], 'retirada');
        if ($msg) {
            return $msg;
        }
        $msg = $this->validarFuncionamentoEstacao((int) $data['estacao_devolucao_id'], (string) $data['data_fim'], 'devolucao');
        if ($msg) {
            return $msg;
        }

        // Verificar se existe raealmente alguem veiculo disponivel para a reserva
        $veiculoDisponivel = $this->veiculoService->getVeiculosDisponiveisPorModeloEEstacaoEIntervaloDatas($data['modelo_id'], $data['estacao_retirada_id'], $data['data_inicio'], $data['data_fim'],$reservaId);
        // dd($veiculoDisponivel);
        if($veiculoDisponivel->isEmpty()) {
            // dd($veiculoDisponivel);
            return 'Não existe veículo disponível para a reserva';
        }

    }

    private function validarFuncionamentoEstacao(int $estacaoId, string $dataHora, string $tipo): ?string
    {
        if ($estacaoId <= 0 || trim($dataHora) === '') {
            return null;
        }

        try {
            $dt = Carbon::parse($dataHora);
        } catch (\Throwable) {
            return null;
        }

        $data = $dt->toDateString();
        $hora = $dt->format('H:i');

        // Feriados
        if (Schema::hasTable('feriados') && Schema::hasColumn('feriados', 'data')) {
            $feriado = DB::table('feriados')
                ->whereDate('data', $data)
                ->when(Schema::hasColumn('feriados', 'estacao_id'), function ($q) use ($estacaoId) {
                    $q->where(function ($sub) use ($estacaoId) {
                        $sub->where('estacao_id', $estacaoId)->orWhereNull('estacao_id');
                    });
                })
                ->when(Schema::hasColumn('feriados', 'ativo'), function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('ativo', 'S')->orWhere('ativo', 1);
                    });
                })
                ->first();

            if ($feriado) {
                $nome = $feriado->nome ?? 'Feriado';
                return "A data/hora de {$tipo} cai em feriado ({$nome}) para a estacao selecionada.";
            }
        }

        // Dias uteis (se configurado)
        if (Schema::hasTable('estacoes_dias_uteis') && Schema::hasTable('dias_uteis')) {
            $dias = DB::table('estacoes_dias_uteis')
                ->join('dias_uteis', 'dias_uteis.id', '=', 'estacoes_dias_uteis.dia_util_id')
                ->where('estacoes_dias_uteis.estacao_id', $estacaoId)
                ->pluck('dias_uteis.dia')
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values()
                ->all();

            if (!empty($dias)) {
                $dow = (int) $dt->dayOfWeekIso; // 1=Mon..7=Sun
                if (!in_array($dow, $dias, true)) {
                    return "A estacao selecionada nao funciona no dia escolhido para {$tipo}.";
                }
            }
        }

        // Horarios
        if (Schema::hasTable('horarios') && Schema::hasColumn('horarios', 'estacao_id') && Schema::hasColumn('horarios', 'horario_diario_id')) {
            $dow = (int) $dt->dayOfWeekIso;
            $hor = DB::table('horarios')
                ->where('estacao_id', $estacaoId)
                ->where('horario_diario_id', $dow)
                ->first();

            if ($hor) {
                $funciona24h = false;
                if (property_exists($hor, 'funciona_24h')) {
                    $funciona24h = in_array((string) $hor->funciona_24h, ['S', '1', 'true', 'TRUE'], true);
                }
                if ($funciona24h) {
                    return null;
                }

                $abertura = $hor->hora_abertura ?? null;
                $fechamento = $hor->hora_fechamento ?? null;
                if ($abertura && $fechamento) {
                    $a = substr((string) $abertura, 0, 5);
                    $f = substr((string) $fechamento, 0, 5);
                    if ($hora < $a || $hora > $f) {
                        return "Horario de {$tipo} fora do funcionamento da estacao ({$abertura} - {$fechamento}).";
                    }
                }

                $almocoIni = $hor->hora_inicio_almoco ?? null;
                $almocoFim = $hor->hora_fim_almoco ?? null;
                if ($almocoIni && $almocoFim) {
                    $ai = substr((string) $almocoIni, 0, 5);
                    $af = substr((string) $almocoFim, 0, 5);
                    if ($ai !== '00:00' || $af !== '00:00') {
                        if ($hora >= $ai && $hora <= $af) {
                            return "Horario de {$tipo} cai no intervalo de almoco da estacao ({$almocoIni} - {$almocoFim}).";
                        }
                    }
                }
            }
        }

        return null;
    }

    public function validarFuncionamentoEstacaoParaApi(int $estacaoId, string $dataHora, string $tipo): ?string
    {
        return $this->validarFuncionamentoEstacao($estacaoId, $dataHora, $tipo);
    }

    public function adicionaLog($reservaId, $userId, $json, string $acao = '', string $nivel = 'info'): void
    {
        DB::table('reserva_logs')->insert([
            'reserva_id' => $reservaId,
            'user_id'    => $userId,
            'acao'       => $acao ?: (is_string($json) ? (json_decode($json, true)['acao'] ?? '') : ''),
            'nivel'      => $nivel,
            'detalhes'   => is_array($json) ? json_encode($json) : $json,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    public function processarDadosReserva($reservaData)
    {
        $data = $this->converteObjetoReservaParaArray($reservaData);
        $data['percentual_desconto'] = $data['valor_desconto'] ?? 0;

        $data['valor_opcionais'] = $this->calculaValorOpcionais($data['opcionais'], $data['quantidade_dias']);
        $data['valor_total_tarifa_base'] = $this->calcularValorTotalPorDias($data['tarifa_base'], $data['quantidade_dias']);
        $data['valor_total_com_desconto'] = self::obtemValorTotalDesconto(
                                                ($data['valor_total_tarifa_base'] + $data['valor_opcionais']),
                                                $data['percentual_desconto']);
        $data['valor_total_final'] = self::calcularValorTotalReserva(
                                                $data['valor_total_tarifa_base'],
                                                $data['valor_opcionais'],
                                                $data['percentual_desconto']);
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
    public function processarReservasAtrasadasEAjustarSaldo(): void
    {
        $now = Carbon::now();

        $reservas = $this->model
            ->with(['usuario', 'empresa'])
            ->where('data_inicio', '<', $now)
            ->whereIn('status_reserva_id', [2, 6])
            ->get();

        foreach ($reservas as $reserva) {
            $minutosAtraso = Carbon::parse($reserva->data_inicio)->diffInMinutes($now, false);
            if ($minutosAtraso <= 0) {
                continue;
            }

            if ((int) $reserva->status_reserva_id === 2) {
                $reserva->status_reserva_id = 6; // Atrasada
                $reserva->save();
            }

            $limite = $this->obterLimiteAtrasoMinutos($reserva);
            if ($limite === null) {
                continue;
            }

            if ((int) $reserva->status_reserva_id === 6 && $minutosAtraso >= $limite) {
                $this->cancelarReservaPorAtraso($reserva, $limite);
            }
        }
    }

    private function obterLimiteAtrasoMinutos(Reserva $reserva): ?int
    {
        $empresa = $reserva->empresa ?: Empresa::find($reserva->empresa_id);
        if (!$empresa) {
            return 120;
        }

        $limite = $empresa->reserva_atraso_max_minutos;
        if ($limite === null || $limite === '') {
            return 120;
        }

        return max(0, (int) $limite);
    }

    private function cancelarReservaPorAtraso(Reserva $reserva, int $limiteMinutos): void
    {
        $reserva->status_reserva_id = 3; // Cancelada
        $reserva->data_cancelamento = now();
        $reserva->observacoes = trim(($reserva->observacoes ?? '') . ' Reserva cancelada automaticamente por atraso.');
        $reserva->save();

        if ($reserva->pagamento_efetuado === 'S' && $reserva->usuario) {
            $valor = (float) ($reserva->valor_total_final ?? 0);
            if ($valor > 0) {
                $this->saldoService->creditar($reserva->usuario, $valor, [
                    'empresa_id' => $reserva->empresa_id,
                    'reserva_id' => $reserva->id,
                    'origem' => 'cancelamento_atraso',
                    'observacoes' => 'Saldo gerado por cancelamento automatico apos ' . $limiteMinutos . ' minutos de atraso.',
                ]);
            }
        }
    }

    private function calcularValorTotalPorDias($tarifaBase, $quantidadeDias){
        return $tarifaBase * $quantidadeDias;
    }

    public function obtemValorTotalDesconto($valorTotal, $percentualDesconto){
        return ($valorTotal * ($percentualDesconto / 100));
    }

    private function calcularValorTotalReserva($valorTotalPorDias, $valorOpcionais, $percentualDesconto){
        $valorTotal = ($valorTotalPorDias + $valorOpcionais);

        $valorTotalComDesconto = $valorTotal - self::obtemValorTotalDesconto($valorTotal, $percentualDesconto);

        return $valorTotalComDesconto;
    }
    private function atualizarStatusReservasPorTipo(array $statusReserva, int $novoStatus, string $observacao,string $mensagemErro, int $horasLimite = null, int $tipoMovimentacao = 12, int $statusMovimentacao = 7)
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

                $arrayMovimentacao = [
                    'observacoes' => $observacao,
                    'veiculo_id' => $reserva->veiculo_id,
                    'tipo_movimentacao_id' => $tipoMovimentacao,
                    'reserva_id' => $reserva->id,
                    'status_movimentacao_id' => $statusMovimentacao
                ];
                // $this->movimentacaoService->modificaStatusMovimentacaoReserva(
                //     $arrayMovimentacao,
                //     $reserva->id
                // );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ReservaJobException($mensagemErro);
        }
    }

    public function checkInRealizado($reserva_id,$veiculo_id)
    {
        $reserva = $this->model->find($reserva_id);
        $reserva->status_reserva_id = 4; // Em uso
        $reserva->veiculo_id = $veiculo_id;
        $reserva->save();
    }

    public function checkOutRealizado($reserva_id)
    {
        $reserva = $this->model->find($reserva_id);

        // Busca o status "Concluída" da empresa da reserva para suportar multi-empresa
        $statusConcluida = \App\Models\StatusReserva::where('empresa_id', $reserva->empresa_id)
            ->where('nome', 'like', 'Conclu%')
            ->first();

        $reserva->status_reserva_id = $statusConcluida ? $statusConcluida->id : 5;
        $reserva->save();
    }
}
