<?php

namespace App\Services;

use App\Enums\StatusReservaEnum;
use App\Exceptions\ReservaJobException;
use App\Actions\Financeiro\CreateLancamentoAction;
use App\Models\Reserva;
use App\Models\Empresa;
use App\Traits\LogTrait;
use App\Services\ReservaCodigoService;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;
use DB;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaCache;

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
    protected CreateLancamentoAction $createLancamentoAction;

    public function __construct(
        Reserva $reserva,
        MovimentacaoService $movimentacaoService,
        OpcionalService $opcionalService,
        GrupoFrotaService $grupoFrotaService,
        ModeloService $modeloService,
        VeiculoService $veiculoService,
        SaldoService $saldoService,
        CreateLancamentoAction $createLancamentoAction
    ) {
        $this->model = $reserva;
        $this->movimentacaoService = $movimentacaoService;
        $this->opcionalService = $opcionalService;
        $this->grupoFrotaService = $grupoFrotaService;
        $this->modeloService = $modeloService;
        $this->veiculoService = $veiculoService;
        $this->saldoService = $saldoService;
        $this->createLancamentoAction = $createLancamentoAction;
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
                'taxa_coleta' => $data['taxa_coleta'],
                'taxa_entrega' => $data['taxa_entrega'],
                'user_id' => $data['user_id'],
                'tarifa_base' => $data['tarifa_base'],
                'quantidade_dias' => $data['quantidade_dias'],
                'status_reserva_id' => StatusReservaEnum::Pendente->value,
                'cupom_desconto_id' => $data['cupom_desconto']['id'] ?? null,
                'observacoes' => $data['observacoes'] ?? null,
                'created_at' => now(),
                'updated_at' => null,
            ];

            if (SchemaCache::hasColumn($this->model->getTable(), 'status_pagamento')) {
                $dadosReserva['status_pagamento'] = 'pendente';
            }

            if (SchemaCache::hasColumn($this->model->getTable(), 'pagamento_efetuado')) {
                $dadosReserva['pagamento_efetuado'] = false;
            }

            if (SchemaCache::hasColumn($this->model->getTable(), 'criado_por_user_id')) {
                $dadosReserva['criado_por_user_id'] = $this->resolverCriadorCadastroReserva($data);
            }

            if (SchemaCache::hasColumn($this->model->getTable(), 'origem_cadastro')) {
                $dadosReserva['origem_cadastro'] = $this->resolverOrigemCadastroReserva($data);
            }

            $empresaId = $this->currentEmpresaId();

            // Faltou hora inicio e hora fim
            $reserva = $this->model->create($dadosReserva);
            if($reserva == null) {
                return redirect()->back()->with('error', 'Erro ao criar reserva');
            }

            app(ReservaCodigoService::class)->sincronizar($reserva, 'reserva', [
                'empresa_id' => $empresaId,
                'status_label' => 'Pendente',
                'data_inicio' => $dadosReserva['data_inicio'] ?? null,
                'data_fim' => $dadosReserva['data_fim'] ?? null,
                'valor_total' => $dadosReserva['valor_total_final'] ?? null,
            ]);

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
            'status_reserva_id' => StatusReservaEnum::Pendente->value,
            'cupom_desconto_id' => $data['cupom_desconto']['id'] ?? null,
            'percentual_desconto' => $data['percentual_desconto'],
            'valor_total_final' => $data['valor_total_final'],
            'valor_total_com_desconto' => $data['valor_total_com_desconto'],
            'valor_total_tarifa_base' => $data['valor_total_tarifa_base'],
            'valor_opcionais' => $data['valor_opcionais'],
            'taxa_coleta' => $data['taxa_coleta'],
            'taxa_entrega' => $data['taxa_entrega'],
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
            $reserva->status_reserva_id = StatusReservaEnum::Cancelada->value;

            if (SchemaCache::hasColumn($this->model->getTable(), 'data_cancelamento')) {
                $reserva->data_cancelamento = now();
            }

            $veiculoIdOriginal = $reserva->veiculo_id;

            // Cancela reserva e libera vínculo do veículo (quando aplicável)
            if (SchemaCache::hasColumn($this->model->getTable(), 'veiculo_id')) {
                $reserva->veiculo_id = null;
            }

            $reserva->save();

            // Cancela movimentações vinculadas para não bloquear disponibilidade
            if (isset($this->movimentacaoService)) {
                $this->movimentacaoService->cancelarMovimentacoesReserva((int) $id, null, 'Cancelada por cancelamento de reserva');
            }

            // Integra��o Financeira: Registrar estornos por pagamento efetivado
            $transacoesPagas = $reserva->transacoes()
                ->whereIn('status', ['succeeded', 'approved', 'pago', 'paid', 'concluido', 'authorized'])
                ->orderBy('id')
                ->get();

            foreach ($transacoesPagas as $transacao) {
                $valorTransacao = round((float) ($transacao->valor ?? 0), 2);
                if ($valorTransacao <= 0) {
                    continue;
                }

                $categoria = \App\Models\CategoriaFinanceira::firstOrCreate(
                    ['empresa_id' => $reserva->empresa_id, 'nome' => 'Estorno de Reserva'],
                    ['tipo' => 'despesa', 'ativo' => true]
                );

                $chave = 'estorno_cancelamento|reserva|' . $reserva->id . '|transacao|' . $transacao->id;
                $jaExiste = \App\Models\LancamentoFinanceiro::query()
                    ->where('empresa_id', $reserva->empresa_id)
                    ->where('valor', '<', 0)
                    ->where('observacoes', 'like', '%'.$chave.'%')
                    ->exists();

                if ($jaExiste) {
                    continue;
                }

                app(\App\Actions\Financeiro\CreateLancamentoAction::class)->handle([
                    'empresa_id' => $reserva->empresa_id,
                    'descricao' => 'Estorno do pagamento da reserva #' . str_pad((string) $reserva->id, 4, '0', STR_PAD_LEFT),
                    'valor' => -1 * abs($valorTransacao),
                    'tipo' => 'despesa',
                    'categoria_financeira_id' => $categoria->id,
                    'data_vencimento' => now()->toDateString(),
                    'data_pagamento' => now()->toDateString(),
                    'status' => 'pago',
                    'reserva_id' => $reserva->id,
                    'veiculo_id' => $transacao->veiculo_id ?? $veiculoIdOriginal,
                    'user_id' => Auth::id() ?? $reserva->user_id,
                    'metodo_pagamento' => $transacao->metodo_pagamento ?? null,
                    'observacoes' => 'Gerado automaticamente no cancelamento da reserva. Pagamento original: transacao ' . $transacao->id . '. Chave: ' . $chave,
                ]);
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
        $resultado = $this->avaliarFuncionamentoEstacao($estacaoId, $dataHora, $tipo);

        return $resultado['ok'] ? null : ($resultado['message'] ?? null);
    }

    /**
     * @return array{ok:bool,taxa_extra:bool,message:?string,fora_expediente:bool}
     */
    public function avaliarFuncionamentoEstacaoPublico(int $estacaoId, string $dataHora, string $tipo): array
    {
        return $this->avaliarFuncionamentoEstacao($estacaoId, $dataHora, $tipo);
    }

    private function avaliarFuncionamentoEstacao(int $estacaoId, string $dataHora, string $tipo): array
    {
        if ($estacaoId <= 0 || trim($dataHora) === '') {
            return [
                'ok' => true,
                'taxa_extra' => false,
                'message' => null,
                'fora_expediente' => false,
            ];
        }

        try {
            $dt = Carbon::parse($dataHora);
        } catch (\Throwable) {
            return [
                'ok' => true,
                'taxa_extra' => false,
                'message' => null,
                'fora_expediente' => false,
            ];
        }

        $data = $dt->toDateString();
        $hora = $dt->format('H:i');
        $permiteTaxaExtra = $this->estacaoPermiteTaxaForaExpediente($estacaoId, $tipo);
        $ehFeriado = $this->dataEhFeriadoParaEstacao($estacaoId, $dt);

        // Feriados
        if ($ehFeriado && !$this->estacaoOperaFeriado($estacaoId)) {
            return [
                'ok' => false,
                'taxa_extra' => false,
                'message' => "A data/hora de {$tipo} cai em feriado para a estacao selecionada.",
                'fora_expediente' => false,
            ];
        }

        // Dias uteis (se configurado)
        if (SchemaCache::hasTable('estacoes_dias_uteis') && SchemaCache::hasTable('dias_uteis')) {
            $dias = DB::table('estacoes_dias_uteis')
                ->join('dias_uteis', 'dias_uteis.id', '=', 'estacoes_dias_uteis.dia_util_id')
                ->where('estacoes_dias_uteis.estacao_id', $estacaoId)
                ->pluck('dias_uteis.dia')
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values()
                ->all();

            if (!empty($dias)) {
                $dow = (int) $dt->dayOfWeek; // 0=Sun..6=Sat
                if (!in_array($dow, $dias, true)) {
                    return [
                        'ok' => false,
                        'taxa_extra' => false,
                        'message' => "A estacao selecionada nao funciona no dia escolhido para {$tipo}.",
                        'fora_expediente' => false,
                    ];
                }
            }
        }

        // Horarios
        $hor = $this->obterHorarioEstacaoParaData($estacaoId, $dt);
        if ($hor) {
            $funciona24h = false;
            if (property_exists($hor, 'funciona_24h')) {
                $funciona24h = in_array((string) $hor->funciona_24h, ['S', '1', 'true', 'TRUE'], true);
            }

            if ($funciona24h) {
                return [
                    'ok' => true,
                    'taxa_extra' => false,
                    'message' => null,
                    'fora_expediente' => false,
                ];
            }

            $abertura = $hor->hora_abertura ?? null;
            $fechamento = $hor->hora_fechamento ?? null;
            if ($abertura && $fechamento) {
                $a = substr((string) $abertura, 0, 5);
                $f = substr((string) $fechamento, 0, 5);
                if ($a === '00:00' && $f === '00:00') {
                    return [
                        'ok' => false,
                        'taxa_extra' => false,
                        'message' => "A estacao selecionada nao possui expediente configurado para {$tipo} neste dia.",
                        'fora_expediente' => false,
                    ];
                }
                $foraExpediente = ($hora < $a || $hora > $f);
                if ($foraExpediente && $permiteTaxaExtra) {
                    return [
                        'ok' => true,
                        'taxa_extra' => true,
                        'message' => null,
                        'fora_expediente' => true,
                    ];
                }
                if ($foraExpediente) {
                    return [
                        'ok' => false,
                        'taxa_extra' => false,
                        'message' => "Horario de {$tipo} fora do funcionamento da estacao ({$abertura} - {$fechamento}).",
                        'fora_expediente' => true,
                    ];
                }
            }

            $temIntervaloAlmoco = true;
            if (property_exists($hor, 'tem_intervalo_almoco')) {
                $temIntervaloAlmoco = in_array((string) $hor->tem_intervalo_almoco, ['S', '1', 'true', 'TRUE'], true);
            }

            $almocoIni = $hor->hora_inicio_almoco ?? null;
            $almocoFim = $hor->hora_fim_almoco ?? null;
            if ($temIntervaloAlmoco && $almocoIni && $almocoFim) {
                $ai = substr((string) $almocoIni, 0, 5);
                $af = substr((string) $almocoFim, 0, 5);
                if ($ai !== '00:00' || $af !== '00:00') {
                    if ($hora >= $ai && $hora <= $af) {
                        return [
                            'ok' => false,
                            'taxa_extra' => false,
                            'message' => "Horario de {$tipo} cai no intervalo de almoco da estacao ({$almocoIni} - {$almocoFim}).",
                            'fora_expediente' => false,
                        ];
                    }
                }
            }
        }

        return [
            'ok' => true,
            'taxa_extra' => false,
            'message' => null,
            'fora_expediente' => false,
        ];
    }

    private function dataEhFeriadoParaEstacao(int $estacaoId, Carbon $dt): bool
    {
        if (!SchemaCache::hasTable('feriados') || !SchemaCache::hasColumn('feriados', 'data')) {
            return false;
        }

        return DB::table('feriados')
            ->whereDate('data', $dt->toDateString())
            ->when(SchemaCache::hasColumn('feriados', 'estacao_id'), function ($q) use ($estacaoId) {
                $q->where(function ($sub) use ($estacaoId) {
                    $sub->where('estacao_id', $estacaoId)->orWhereNull('estacao_id');
                });
            })
            ->when(SchemaCache::hasColumn('feriados', 'ativo'), function ($q) {
                $q->where(function ($sub) {
                    $sub->where('ativo', 'S')->orWhere('ativo', 1);
                });
            })
            ->exists();
    }

    private function estacaoOperaFeriado(int $estacaoId): bool
    {
        if (!SchemaCache::hasTable('estacoes') || !SchemaCache::hasColumn('estacoes', 'opera_feriado')) {
            return false;
        }

        $valor = DB::table('estacoes')->where('id', $estacaoId)->value('opera_feriado');
        return in_array((string) $valor, ['S', '1', 'true', 'TRUE'], true);
    }

    private function obterHorarioEstacaoParaData(int $estacaoId, Carbon $dt): ?object
    {
        if (!SchemaCache::hasTable('horarios') || !SchemaCache::hasTable('horarios_diarios')) {
            return null;
        }

        $empresaId = $this->currentEmpresaId();
        if ($empresaId === null) {
            return null;
        }

        $tipoHorario = $this->resolverTipoHorarioDiarioParaData($estacaoId, $dt);
        if ($tipoHorario === null) {
            return null;
        }

        $horarioDiarioId = $this->resolverHorarioDiarioIdPorTipo($empresaId, $tipoHorario);
        if ($horarioDiarioId === null) {
            return null;
        }

        return DB::table('horarios')
            ->where('estacao_id', $estacaoId)
            ->where('horario_diario_id', $horarioDiarioId)
            ->first();
    }

    private function resolverTipoHorarioDiarioParaData(int $estacaoId, Carbon $dt): ?string
    {
        if ($this->dataEhFeriadoParaEstacao($estacaoId, $dt)) {
            return $this->estacaoOperaFeriado($estacaoId) ? 'feriados' : null;
        }

        if ($dt->isSaturday()) {
            return 'sabados';
        }

        if ($dt->isSunday()) {
            return 'domingos';
        }

        return 'dias_semana';
    }

    private function resolverHorarioDiarioIdPorTipo(int $empresaId, string $tipoHorario): ?int
    {
        static $cache = [];

        $cacheKey = $empresaId . '|' . $tipoHorario;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $patterns = match ($tipoHorario) {
            'sabados' => ['%sab%'],
            'domingos' => ['%dom%'],
            'feriados' => ['%feri%'],
            default => ['%semana%', '%dias%'],
        };

        $query = DB::table('horarios_diarios')
            ->where('empresa_id', $empresaId)
            ->where('ativo', 'S');

        $query->where(function ($subQuery) use ($patterns) {
            foreach ($patterns as $index => $pattern) {
                if ($index === 0) {
                    $subQuery->whereRaw('LOWER(nome) LIKE ?', [$pattern]);
                } else {
                    $subQuery->orWhereRaw('LOWER(nome) LIKE ?', [$pattern]);
                }
            }
        });

        $id = $query->value('id');
        $cache[$cacheKey] = $id ? (int) $id : null;

        return $cache[$cacheKey];
    }

    private function estacaoPermiteTaxaForaExpediente(int $estacaoId, string $tipo): bool
    {
        $coluna = $tipo === 'devolucao' ? 'cobrar_na_entrega' : 'cobrar_na_coleta';

        if (!SchemaCache::hasTable('estacoes') || !SchemaCache::hasColumn('estacoes', $coluna)) {
            return false;
        }

        $valor = DB::table('estacoes')->where('id', $estacaoId)->value($coluna);

        return in_array((string) $valor, ['S', '1', 'true', 'TRUE'], true);
    }

    public function obterDatasRetiradaDisponiveisParaEstacao(int $estacaoId, ?string $dataBase = null, int $limite = 14): array
    {
        if ($estacaoId <= 0) {
            return [];
        }

        $base = !empty($dataBase)
            ? Carbon::parse($dataBase)
            : now();

        $base = $base->startOfDay();
        $limite = max(1, min($limite, 60));
        $datas = [];

        for ($i = 0; $i < $limite; $i++) {
            $dia = $base->copy()->addDays($i);
            $horario = $this->obterHorarioEstacaoParaData($estacaoId, $dia);

            if (!$horario) {
                continue;
            }

            $funciona24h = false;
            if (property_exists($horario, 'funciona_24h')) {
                $funciona24h = in_array((string) $horario->funciona_24h, ['S', '1', 'true', 'TRUE'], true);
            }

            $horaSugestao = '08:00';
            if ($funciona24h) {
                $horaSugestao = '00:00';
            } elseif (!empty($horario->hora_abertura)) {
                $horaSugestao = substr((string) $horario->hora_abertura, 0, 5);
            }

            $dataHora = $dia->copy()->setTimeFromTimeString($horaSugestao)->format('Y-m-d\TH:i');
            $msg = $this->validarFuncionamentoEstacao($estacaoId, $dataHora, 'retirada');
            if ($msg !== null) {
                continue;
            }

            $datas[] = [
                'value' => $dataHora,
                'label' => Carbon::parse($dataHora)->format('d/m/Y H:i'),
            ];
        }

        return $datas;
    }

    public function obterAgendaDisponivelPorEstacao(int $estacaoId, string $tipo = 'retirada', ?string $dataBase = null, int $limite = 14, ?int $reservaId = null): array
    {
        if ($estacaoId <= 0) {
            return [];
        }

        $tipo = strtolower(trim($tipo)) ?: 'retirada';

        $quantidadeVeiculosPorEstacao = $this->veiculoService->getQuantidadeVeiculosAtivosPorEstacao([$estacaoId]);
        if ($tipo !== 'devolucao' && (int) ($quantidadeVeiculosPorEstacao[$estacaoId] ?? 0) <= 0) {
            return [];
        }

        $base = !empty($dataBase)
            ? Carbon::parse($dataBase)
            : now();

        $base = $base->startOfDay();
        $limite = max(1, min($limite, 60));
        $agenda = [];

        for ($i = 0; $i < $limite; $i++) {
            $dia = $base->copy()->addDays($i);
            $horario = $this->obterHorarioEstacaoParaData($estacaoId, $dia);
            if (!$horario) {
                continue;
            }

            $funciona24h = false;
            if (property_exists($horario, 'funciona_24h')) {
                $funciona24h = in_array((string) $horario->funciona_24h, ['S', '1', 'true', 'TRUE'], true);
            }

            $abertura = $horario->hora_abertura ?? null;
            $fechamento = $horario->hora_fechamento ?? null;
            $inicioAlmoco = $horario->hora_inicio_almoco ?? null;
            $fimAlmoco = $horario->hora_fim_almoco ?? null;
            $permiteTaxaExtra = $this->estacaoPermiteTaxaForaExpediente($estacaoId, $tipo);
            $slots = [];
            $inicio = null;
            $fim = null;

            if ($permiteTaxaExtra) {
                $inicio = $dia->copy()->startOfDay();
                $fim = $dia->copy()->endOfDay();
            } else {
                $inicio = $funciona24h
                    ? $dia->copy()->startOfDay()
                    : ($abertura ? Carbon::parse($dia->toDateString() . ' ' . substr((string) $abertura, 0, 5)) : null);
                $fim = $funciona24h
                    ? $dia->copy()->endOfDay()
                    : ($fechamento ? Carbon::parse($dia->toDateString() . ' ' . substr((string) $fechamento, 0, 5)) : null);
            }

            if (!$inicio || !$fim) {
                continue;
            }

            $passoMinutos = 30;
            $slotAtual = $inicio->copy();
            $temIntervaloAlmoco = true;
            if (property_exists($horario, 'tem_intervalo_almoco')) {
                $temIntervaloAlmoco = in_array((string) $horario->tem_intervalo_almoco, ['S', '1', 'true', 'TRUE'], true);
            }

            while ($slotAtual->lessThanOrEqualTo($fim)) {
                $slotHora = $slotAtual->format('H:i');
                if (!$permiteTaxaExtra && $temIntervaloAlmoco && $inicioAlmoco && $fimAlmoco) {
                    $ia = substr((string) $inicioAlmoco, 0, 5);
                    $fa = substr((string) $fimAlmoco, 0, 5);
                    if (!(($ia === '00:00' && $fa === '00:00') || $slotHora < $ia || $slotHora > $fa)) {
                        $slotAtual->addMinutes($passoMinutos);
                        continue;
                    }
                }

                $dataHora = $slotAtual->format('Y-m-d\TH:i');
                $avaliacao = $this->avaliarFuncionamentoEstacao($estacaoId, $dataHora, $tipo);
                if ($avaliacao['ok']) {
                    if ($tipo === 'devolucao' || $this->veiculoService->temVeiculoDisponivelNaEstacaoEmDataHora($estacaoId, $dataHora, $reservaId)) {
                        $slots[] = [
                            'value' => $dataHora,
                            'label' => $slotAtual->format('H:i'),
                            'taxa_extra' => $avaliacao['taxa_extra'] ?? false,
                            'status' => ($avaliacao['taxa_extra'] ?? false) ? 'taxa_extra' : 'normal',
                        ];
                    }
                }

                $slotAtual->addMinutes($passoMinutos);
            }

            if (!empty($slots)) {
                $agenda[] = [
                    'date' => $dia->format('Y-m-d'),
                    'label' => $dia->format('d/m/Y'),
                    'weekday' => (int) $dia->dayOfWeek,
                    'slots' => $slots,
                ];
            }
        }

        return $agenda;
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
        $data['percentual_desconto'] = (float) ($data['cupom_desconto']['valor'] ?? 0);

        $data['taxa_coleta']  = (float) ($data['taxa_coleta']  ?? 0);
        $data['taxa_entrega'] = (float) ($data['taxa_entrega'] ?? 0);

        $data['valor_opcionais'] = $this->calculaValorOpcionais($data['opcionais'], $data['quantidade_dias']);
        $data['valor_total_tarifa_base'] = $this->calcularValorTotalPorDias($data['tarifa_base'], $data['quantidade_dias']);

        $subtotal = $data['valor_total_tarifa_base'] + $data['valor_opcionais'] + $data['taxa_coleta'] + $data['taxa_entrega'];

        $data['valor_total_com_desconto'] = self::obtemValorTotalDesconto($subtotal, $data['percentual_desconto']);
        $data['valor_total_final'] = $subtotal - ($subtotal * ($data['percentual_desconto'] / 100));
        $data['valor_total_final'] = max(0, (float) $data['valor_total_final']);

        return $data;
    }

    private function resolverOrigemCadastroReserva(array $data): string
    {
        if (!empty($data['origem_cadastro'])) {
            return (string) $data['origem_cadastro'];
        }

        $routeName = request()?->route()?->getName();
        if (is_string($routeName) && str_starts_with($routeName, 'site.reserva.')) {
            return 'site';
        }

        return 'colaborador';
    }

    private function resolverCriadorCadastroReserva(array $data): ?int
    {
        if (array_key_exists('criado_por_user_id', $data)) {
            return $data['criado_por_user_id'] !== null ? (int) $data['criado_por_user_id'] : null;
        }

        $routeName = request()?->route()?->getName();
        if (is_string($routeName) && str_starts_with($routeName, 'site.reserva.')) {
            return null;
        }

        return Auth::id() ? (int) Auth::id() : null;
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
            ->whereIn('status_reserva_id', [StatusReservaEnum::Confirmada->value, StatusReservaEnum::NoShow->value])
            ->get();

        foreach ($reservas as $reserva) {
            $minutosAtraso = Carbon::parse($reserva->data_inicio)->diffInMinutes($now, false);
            if ($minutosAtraso <= 0) {
                continue;
            }

            if ((int) $reserva->status_reserva_id === StatusReservaEnum::Confirmada->value) {
                $reserva->status_reserva_id = StatusReservaEnum::NoShow->value;
                $reserva->save();
            }

            $limite = $this->obterLimiteAtrasoMinutos($reserva);
            if ($limite === null) {
                continue;
            }

            if ((int) $reserva->status_reserva_id === StatusReservaEnum::NoShow->value && $minutosAtraso >= $limite) {
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

        if ($reserva->isPagamentoEfetuado() && $reserva->usuario) {
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
        if ($reserva) {
            $reserva->status_reserva_id = 4; // Em uso
            $reserva->veiculo_id = $veiculo_id;
            $reserva->save();
        }
    }

    public function checkOutRealizado($reserva_id)
    {
        $reserva = $this->model->find($reserva_id);
        if ($reserva) {
            $reserva->status_reserva_id = \App\Enums\StatusReservaEnum::Checkout->value; // 5 = Concluída
            $reserva->save();
        }
    }
}


