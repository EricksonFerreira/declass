<?php

namespace App\Services;

use App\Enums\StatusReservaEnum;
use App\Enums\TipoDisponibilidadeVeiculoEnum;
use App\Models\Movimentacao;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Support\EmpresaContext;
use App\Support\SchemaCache;

class DisponibilidadeVeiculoService
{
    public function montarGradeMensal(Collection $veiculos, int $ano, int $mes): array
    {
        $diasNoMes = (int) cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
        $diasDoMes = range(1, $diasNoMes);

        $inicioMes = Carbon::create($ano, $mes, 1)->startOfDay();
        $fimMesExclusivo = $inicioMes->copy()->addMonth()->startOfDay();

        $reservaBloqueioHoras = $this->getReservaBloqueioHoras();
        $inicioMesComFolga = $inicioMes->copy()->subHours($reservaBloqueioHoras);

        $veiculoIds = $veiculos
            ->pluck('id')
            ->map(static fn($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $reservasPorVeiculo = collect();
        $movimentacoesPorVeiculo = collect();

        if (!empty($veiculoIds)) {
            $reservasPorVeiculo = $this->buscarReservasPorVeiculo(
                $veiculoIds,
                $inicioMesComFolga,
                $fimMesExclusivo
            );

            $movimentacoesPorVeiculo = $this->buscarMovimentacoesPorVeiculo(
                $veiculoIds,
                $inicioMes,
                $fimMesExclusivo
            );
        }

        $grade = [];
        $resumoVeiculos = [];
        $cards = [
            'total' => $veiculos->count(),
            'disponiveis' => 0,
            'parciais' => 0,
            'indisponiveis' => 0,
        ];

        foreach ($veiculos as $veiculo) {
            $veiculoId = (int) $veiculo->id;

            $reservas = $reservasPorVeiculo->get($veiculoId, collect());
            $movimentacoes = $movimentacoesPorVeiculo->get($veiculoId, collect());

            $minutosMes = 0;
            $minutosDisponiveisMes = 0;
            $quantidadeParcial = 0;
            $quantidadeIndisponivel = 0;

            foreach ($diasDoMes as $dia) {
                $diaInicio = Carbon::create($ano, $mes, $dia)->startOfDay();
                $diaFimExclusivo = $diaInicio->copy()->addDay();

                $cell = $this->montarCelulaDia(
                    $veiculo,
                    $reservas,
                    $movimentacoes,
                    $diaInicio,
                    $diaFimExclusivo,
                    $reservaBloqueioHoras
                );

                $grade[$veiculoId][$dia] = $cell;

                $minutosMes += (int) $cell['minutos_total'];
                $minutosDisponiveisMes += (int) $cell['minutos_disponiveis'];

                if ($cell['tipo'] === TipoDisponibilidadeVeiculoEnum::Parcial->value) {
                    $quantidadeParcial++;
                }

                if (in_array($cell['tipo'], [
                    TipoDisponibilidadeVeiculoEnum::Reservado->value,
                    TipoDisponibilidadeVeiculoEnum::ReservaNaoFinalizada->value,
                    TipoDisponibilidadeVeiculoEnum::Indisponivel->value,
                    TipoDisponibilidadeVeiculoEnum::SemLocal->value,
                ], true) && (int) $cell['minutos_disponiveis'] === 0) {
                    $quantidadeIndisponivel++;
                }
            }

            $percentual = $minutosMes > 0
                ? (int) round(($minutosDisponiveisMes / $minutosMes) * 100)
                : 0;

            $statusMes = $this->definirStatusMes($percentual, $quantidadeParcial, $quantidadeIndisponivel, $diasNoMes);

            if ($percentual === 100) {
                $cards['disponiveis']++;
            } elseif ($percentual > 0) {
                $cards['parciais']++;
            } else {
                $cards['indisponiveis']++;
            }

            $resumoVeiculos[$veiculoId] = [
                'percentual' => $percentual,
                'minutos_total' => $minutosMes,
                'minutos_disponiveis' => $minutosDisponiveisMes,
                'status_mes' => $statusMes,
                'dias_parciais' => $quantidadeParcial,
                'dias_indisponiveis' => $quantidadeIndisponivel,
            ];
        }

        return [
            'diasDoMes' => $diasDoMes,
            'grade' => $grade,
            'resumoVeiculos' => $resumoVeiculos,
            'cards' => $cards,
            'reservaBloqueioHoras' => $reservaBloqueioHoras,
        ];
    }

    private function montarCelulaDia(
        object $veiculo,
        Collection $reservas,
        Collection $movimentacoes,
        Carbon $diaInicio,
        Carbon $diaFimExclusivo,
        int $reservaBloqueioHoras
    ): array {
        $minutosTotal = $diaInicio->diffInMinutes($diaFimExclusivo);
        $bloqueios = [];

        if (empty($veiculo->local_disponibilidade)) {
            $bloqueios[] = [
                'inicio' => $diaInicio->copy(),
                'fim' => $diaFimExclusivo->copy(),
                'tipo' => TipoDisponibilidadeVeiculoEnum::SemLocal->value,
                'titulo' => 'Sem local de disponibilidade',
                'reserva_id' => null,
                'movimentacao_id' => null,
            ];
        }

        if (!empty($veiculo->data_hora_disponibilidade)) {
            $inicioDisponibilidade = Carbon::parse($veiculo->data_hora_disponibilidade);

            if ($inicioDisponibilidade->gt($diaInicio)) {
                $bloqueios[] = [
                    'inicio' => $diaInicio->copy(),
                    'fim' => $this->menorData($inicioDisponibilidade, $diaFimExclusivo),
                    'tipo' => TipoDisponibilidadeVeiculoEnum::Indisponivel->value,
                    'titulo' => 'Disponível a partir de ' . $inicioDisponibilidade->format('d/m/Y H:i'),
                    'reserva_id' => null,
                    'movimentacao_id' => null,
                ];
            }
        }

        foreach ($movimentacoes as $movimentacao) {
            /*
     * Movimentações vinculadas a reserva não bloqueiam aqui,
     * porque a própria reserva já deve representar a ocupação.
     */
            if (!empty($movimentacao->reserva_id)) {
                continue;
            }

            $inicioMov = !empty($movimentacao->data_saida)
                ? Carbon::parse($movimentacao->data_saida)
                : (!empty($movimentacao->data_registro) ? Carbon::parse($movimentacao->data_registro) : null);

            $fimMov = !empty($movimentacao->data_chegada_real)
                ? Carbon::parse($movimentacao->data_chegada_real)
                : Carbon::create(2999, 12, 31)->startOfDay();

            if (!$inicioMov || !$this->intervalosCruzam($inicioMov, $fimMov, $diaInicio, $diaFimExclusivo)) {
                continue;
            }

            $tipoMovimentacao = $movimentacao->tipoMovimentacao->nome ?? 'Movimentação';
            $statusMovimentacao = $movimentacao->statusMovimentacao->nome ?? 'Sem status';
            $origem = $movimentacao->localOrigem->nome ?? 'Origem não informada';
            $destino = $movimentacao->localDestino->nome ?? 'Destino não informado';
            $usuario = $movimentacao->usuario->nome ?? 'Usuário não informado';

            $dataRegistroFormatada = !empty($movimentacao->data_registro)
                ? Carbon::parse($movimentacao->data_registro)->format('d/m/Y H:i')
                : null;

            $dataSaidaFormatada = !empty($movimentacao->data_saida)
                ? Carbon::parse($movimentacao->data_saida)->format('d/m/Y H:i')
                : null;

            $dataChegadaPrevistaFormatada = !empty($movimentacao->data_chegada_prevista)
                ? Carbon::parse($movimentacao->data_chegada_prevista)->format('d/m/Y H:i')
                : null;

            $dataChegadaRealFormatada = !empty($movimentacao->data_chegada_real)
                ? Carbon::parse($movimentacao->data_chegada_real)->format('d/m/Y H:i')
                : null;

            $tituloMovimentacao = 'Indisponível por ' . $tipoMovimentacao . ' #' . (int) $movimentacao->id;

            $bloqueios[] = [
                'inicio' => $this->maiorData($inicioMov, $diaInicio),
                'fim' => $this->menorData($fimMov, $diaFimExclusivo),
                'tipo' => TipoDisponibilidadeVeiculoEnum::Indisponivel->value,
                'titulo' => $tituloMovimentacao,
                'reserva_id' => null,
                'movimentacao_id' => (int) $movimentacao->id,
                'detalhes' => [
                    'origem' => 'movimentacao',
                    'id' => (int) $movimentacao->id,
                    'tipo_movimentacao' => $tipoMovimentacao,
                    'status_movimentacao' => $statusMovimentacao,
                    'local_origem' => $origem,
                    'local_destino' => $destino,
                    'usuario' => $usuario,
                    'reserva_id' => $movimentacao->reserva_id ? (int) $movimentacao->reserva_id : null,
                    'reserva_codigo' => $movimentacao->reserva?->codigoReserva?->codigo,
                    'ordem_servico_id' => $movimentacao->ordem_servico_id ? (int) $movimentacao->ordem_servico_id : null,
                    'ordem_servico_numero' => $movimentacao->ordemServico->numero ?? null,
                    'data_registro' => $dataRegistroFormatada,
                    'data_saida' => $dataSaidaFormatada,
                    'data_chegada_prevista' => $dataChegadaPrevistaFormatada,
                    'data_chegada_real' => $dataChegadaRealFormatada,
                    'km_saida' => $movimentacao->km_saida,
                    'km_chegada' => $movimentacao->km_chegada,
                    'observacoes' => $movimentacao->observacoes,
                    'motivo_bloqueio' => empty($movimentacao->data_chegada_real)
                        ? 'A movimentação ainda não possui chegada real registrada.'
                        : 'A movimentação cruza este dia.',
                ],
            ];
        }

        foreach ($reservas as $reserva) {
            $inicioReserva = !empty($reserva->data_inicio) ? Carbon::parse($reserva->data_inicio) : null;
            $fimReserva = !empty($reserva->data_fim) ? Carbon::parse($reserva->data_fim) : null;

            if (!$inicioReserva || !$fimReserva) {
                continue;
            }

            $status = (int) $reserva->status_reserva_id;

            if ($status === StatusReservaEnum::Cancelada->value || $status === StatusReservaEnum::Expirada->value) {
                continue;
            }

            $finalizada = $status === StatusReservaEnum::Checkout->value;

            $fimComFolga = $reservaBloqueioHoras > 0
                ? $fimReserva->copy()->addHours($reservaBloqueioHoras)
                : $fimReserva->copy();

            $codigoReserva = $this->codigoReserva($reserva);

            $tipo = TipoDisponibilidadeVeiculoEnum::Reservado->value;
            $titulo = 'Reservado #' . $codigoReserva;

            if (!$finalizada && now()->gt($fimReserva)) {
                $tipo = TipoDisponibilidadeVeiculoEnum::ReservaNaoFinalizada->value;
                $titulo = 'Reserva não finalizada #' . $codigoReserva;

                /*
                 * Regra conservadora:
                 * se a reserva venceu e não foi concluída, considera bloqueada até regularizar.
                 * Caso você queira bloquear só até agora, troque por:
                 * $fimComFolga = $this->maiorData(now(), $fimComFolga);
                 */
                $fimComFolga = Carbon::create(2999, 12, 31)->startOfDay();
            }

            if (!$this->intervalosCruzam($inicioReserva, $fimComFolga, $diaInicio, $diaFimExclusivo)) {
                continue;
            }

            $bloqueios[] = [
                'inicio' => $this->maiorData($inicioReserva, $diaInicio),
                'fim' => $this->menorData($fimComFolga, $diaFimExclusivo),
                'tipo' => $tipo,
                'titulo' => $titulo,
                'reserva_id' => (int) $reserva->id,
                'reserva_codigo' => $codigoReserva,
                'movimentacao_id' => null,
            ];
        }

        $bloqueios = $this->normalizarBloqueios($bloqueios);

        $minutosBloqueados = collect($bloqueios)->sum(function (array $bloqueio) {
            return $bloqueio['inicio']->diffInMinutes($bloqueio['fim']);
        });

        $minutosBloqueados = min($minutosBloqueados, $minutosTotal);
        $minutosDisponiveis = max($minutosTotal - $minutosBloqueados, 0);

        $intervalos = $this->montarLinhaTempo($diaInicio, $diaFimExclusivo, $bloqueios);

        $tipoDia = $this->definirTipoDia($bloqueios, $minutosDisponiveis, $minutosTotal);
        $titulo = $this->montarTituloDia($tipoDia, $minutosDisponiveis, $minutosTotal, $bloqueios);

        /*
        * Proteção:
        * mesmo que não exista bloqueio, o painel lateral precisa receber
        * um intervalo para desenhar a linha do tempo corretamente.
        */
        if (empty($intervalos)) {
            $intervalos[] = [
                'tipo' => $tipoDia,
                'titulo' => $titulo,
                'inicio' => '00:00',
                'fim' => '23:59',
                'inicio_iso' => $diaInicio->toDateTimeString(),
                'fim_iso' => $diaFimExclusivo->toDateTimeString(),
                'reserva_id' => null,
                'movimentacao_id' => null,
                'detalhes' => null,
                'percentual_inicio' => 0,
                'percentual_largura' => 100,
            ];
        }

        return [
            'tipo' => $tipoDia,
            'titulo' => $titulo,
            'reserva_id' => collect($bloqueios)->pluck('reserva_id')->filter()->first(),
            'reserva_codigo' => collect($bloqueios)->pluck('reserva_codigo')->filter()->first(),
            'minutos_total' => $minutosTotal,
            'minutos_disponiveis' => $minutosDisponiveis,
            'minutos_bloqueados' => $minutosBloqueados,
            'percentual_disponivel' => $minutosTotal > 0 ? (int) round(($minutosDisponiveis / $minutosTotal) * 100) : 0,
            'intervalos' => $intervalos,
        ];
    }

    private function buscarReservasPorVeiculo(array $veiculoIds, Carbon $inicio, Carbon $fim): Collection
    {
        return Reserva::query()
            ->select(['id', 'veiculo_id', 'data_inicio', 'data_fim', 'status_reserva_id'])
            ->with(['codigoReserva:id,reserva_id,codigo'])
            ->whereIn('veiculo_id', $veiculoIds)
            ->whereIn('status_reserva_id', [
                StatusReservaEnum::Pendente->value,
                StatusReservaEnum::Confirmada->value,
                StatusReservaEnum::Checkin->value,
                StatusReservaEnum::Checkout->value,
                StatusReservaEnum::NoShow->value,
            ])
            ->where('data_inicio', '<', $fim->toDateTimeString())
            ->where('data_fim', '>', $inicio->toDateTimeString())
            ->orderBy('data_inicio')
            ->get()
            ->groupBy('veiculo_id');
    }

    private function buscarMovimentacoesPorVeiculo(array $veiculoIds, Carbon $inicio, Carbon $fim): Collection
    {
        return Movimentacao::query()
            ->with([
                'tipoMovimentacao:id,nome',
                'statusMovimentacao:id,nome,cor',
                'localOrigem:id,nome',
                'localDestino:id,nome',
                'usuario:id,nome',
                'reserva:id',
                'reserva.codigoReserva:id,reserva_id,codigo',
                'ordemServico:id,numero,status',
            ])
            ->select([
                'id',
                'nome',
                'veiculo_id',
                'tipo_movimentacao_id',
                'local_origem_id',
                'local_destino_id',
                'ordem_servico_id',
                'data_saida',
                'data_registro',
                'data_chegada_prevista',
                'data_chegada_real',
                'km_saida',
                'km_chegada',
                'status_movimentacao_id',
                'reserva_id',
                'observacoes',
                'user_id',
            ])
            ->whereIn('veiculo_id', $veiculoIds)
            ->whereIn('status_movimentacao_id', [1, 2, 5, 6, 7])
            ->where(function ($query) use ($fim) {
                $query->whereNull('data_saida')
                    ->orWhere('data_saida', '<', $fim->toDateTimeString());
            })
            ->where(function ($query) use ($inicio) {
                $query->whereNull('data_chegada_real')
                    ->orWhere('data_chegada_real', '>=', $inicio->toDateTimeString());
            })
            ->orderBy('data_saida')
            ->orderBy('data_registro')
            ->get()
            ->groupBy('veiculo_id');
    }

    private function montarLinhaTempo(Carbon $diaInicio, Carbon $diaFimExclusivo, array $bloqueios): array
    {
        $intervalos = [];
        $cursor = $diaInicio->copy();

        foreach ($bloqueios as $bloqueio) {
            if ($cursor->lt($bloqueio['inicio'])) {
                $intervalos[] = [
                    'tipo' => TipoDisponibilidadeVeiculoEnum::Disponivel->value,
                    'titulo' => 'Disponível',
                    'inicio' => $cursor->format('H:i'),
                    'fim' => $bloqueio['inicio']->copy()->subMinute()->format('H:i'),
                    'inicio_iso' => $cursor->toDateTimeString(),
                    'fim_iso' => $bloqueio['inicio']->toDateTimeString(),
                    'reserva_id' => null,
                    'movimentacao_id' => null,
                    'detalhes' => null,
                    'percentual_inicio' => $this->percentualNoDia($diaInicio, $diaFimExclusivo, $cursor),
                    'percentual_largura' => $this->percentualLargura($diaInicio, $diaFimExclusivo, $cursor, $bloqueio['inicio']),
                ];
            }

            $intervalos[] = [
                'tipo' => $bloqueio['tipo'],
                'titulo' => $bloqueio['titulo'],
                'inicio' => $bloqueio['inicio']->format('H:i'),
                'fim' => $bloqueio['fim']->copy()->subMinute()->format('H:i'),
                'inicio_iso' => $bloqueio['inicio']->toDateTimeString(),
                'fim_iso' => $bloqueio['fim']->toDateTimeString(),
                'reserva_id' => $bloqueio['reserva_id'],
                'reserva_codigo' => $bloqueio['reserva_codigo'] ?? null,
                'movimentacao_id' => $bloqueio['movimentacao_id'],
                'detalhes' => $bloqueio['detalhes'] ?? null,
                'percentual_inicio' => $this->percentualNoDia($diaInicio, $diaFimExclusivo, $bloqueio['inicio']),
                'percentual_largura' => $this->percentualLargura($diaInicio, $diaFimExclusivo, $bloqueio['inicio'], $bloqueio['fim']),
            ];

            if ($bloqueio['fim']->gt($cursor)) {
                $cursor = $bloqueio['fim']->copy();
            }
        }

        if ($cursor->lt($diaFimExclusivo)) {
            $intervalos[] = [
                'tipo' => TipoDisponibilidadeVeiculoEnum::Disponivel->value,
                'titulo' => 'Disponível',
                'inicio' => $cursor->format('H:i'),
                'fim' => $diaFimExclusivo->copy()->subMinute()->format('H:i'),
                'inicio_iso' => $cursor->toDateTimeString(),
                'fim_iso' => $diaFimExclusivo->toDateTimeString(),
                'reserva_id' => null,
                'movimentacao_id' => null,
                'detalhes' => null,
                'percentual_inicio' => $this->percentualNoDia($diaInicio, $diaFimExclusivo, $cursor),
                'percentual_largura' => $this->percentualLargura($diaInicio, $diaFimExclusivo, $cursor, $diaFimExclusivo),
            ];
        }

        return $intervalos;
    }

    private function normalizarBloqueios(array $bloqueios): array
    {
        $bloqueios = array_values(array_filter($bloqueios, function (array $bloqueio) {
            return $bloqueio['inicio']->lt($bloqueio['fim']);
        }));

        usort($bloqueios, function (array $a, array $b) {
            return $a['inicio']->timestamp <=> $b['inicio']->timestamp;
        });

        $resultado = [];

        foreach ($bloqueios as $bloqueio) {
            if (empty($resultado)) {
                $resultado[] = $bloqueio;
                continue;
            }

            $ultimoIndex = count($resultado) - 1;
            $ultimo = $resultado[$ultimoIndex];

            if ($bloqueio['inicio']->lte($ultimo['fim'])) {
                $tipo = $this->tipoMaisPrioritario($ultimo['tipo'], $bloqueio['tipo']);

                $resultado[$ultimoIndex] = [
                    'inicio' => $ultimo['inicio'],
                    'fim' => $bloqueio['fim']->gt($ultimo['fim']) ? $bloqueio['fim'] : $ultimo['fim'],
                    'tipo' => $tipo,
                    'titulo' => $this->tituloMaisPrioritario($ultimo, $bloqueio, $tipo),
                    'reserva_id' => $bloqueio['reserva_id'] ?? $ultimo['reserva_id'],
                    'reserva_codigo' => $bloqueio['reserva_codigo'] ?? $ultimo['reserva_codigo'] ?? null,
                    'movimentacao_id' => $bloqueio['movimentacao_id'] ?? $ultimo['movimentacao_id'],
                    'detalhes' => $bloqueio['detalhes'] ?? $ultimo['detalhes'] ?? null,
                ];
                continue;
            }

            $resultado[] = $bloqueio;
        }

        return $resultado;
    }

    private function definirTipoDia(array $bloqueios, int $minutosDisponiveis, int $minutosTotal): string
    {
        if (empty($bloqueios)) {
            return TipoDisponibilidadeVeiculoEnum::Disponivel->value;
        }

        if ($minutosDisponiveis > 0 && $minutosDisponiveis < $minutosTotal) {
            return TipoDisponibilidadeVeiculoEnum::Parcial->value;
        }

        return collect($bloqueios)
            ->sortByDesc(fn(array $bloqueio) => TipoDisponibilidadeVeiculoEnum::from($bloqueio['tipo'])->prioridade())
            ->first()['tipo'];
    }

    private function montarTituloDia(string $tipo, int $minutosDisponiveis, int $minutosTotal, array $bloqueios): string
    {
        if ($tipo === TipoDisponibilidadeVeiculoEnum::Disponivel->value) {
            return 'Disponível o dia todo';
        }

        if ($tipo === TipoDisponibilidadeVeiculoEnum::Parcial->value) {
            $percentual = $minutosTotal > 0 ? (int) round(($minutosDisponiveis / $minutosTotal) * 100) : 0;
            return 'Parcialmente disponível - ' . $percentual . '% do dia livre';
        }

        $principal = collect($bloqueios)
            ->sortByDesc(fn(array $bloqueio) => TipoDisponibilidadeVeiculoEnum::from($bloqueio['tipo'])->prioridade())
            ->first();

        return $principal['titulo'] ?? TipoDisponibilidadeVeiculoEnum::from($tipo)->label();
    }

    private function definirStatusMes(int $percentual, int $diasParciais, int $diasIndisponiveis, int $diasNoMes): string
    {
        if ($percentual >= 100) {
            return TipoDisponibilidadeVeiculoEnum::Disponivel->value;
        }

        if ($percentual <= 0 || $diasIndisponiveis >= $diasNoMes) {
            return TipoDisponibilidadeVeiculoEnum::Indisponivel->value;
        }

        return TipoDisponibilidadeVeiculoEnum::Parcial->value;
    }

    private function tipoMaisPrioritario(string $tipoA, string $tipoB): string
    {
        $enumA = TipoDisponibilidadeVeiculoEnum::from($tipoA);
        $enumB = TipoDisponibilidadeVeiculoEnum::from($tipoB);

        return $enumA->prioridade() >= $enumB->prioridade()
            ? $enumA->value
            : $enumB->value;
    }

    private function tituloMaisPrioritario(array $a, array $b, string $tipo): string
    {
        if ($a['tipo'] === $tipo) {
            return $a['titulo'];
        }

        if ($b['tipo'] === $tipo) {
            return $b['titulo'];
        }

        return TipoDisponibilidadeVeiculoEnum::from($tipo)->label();
    }

    private function codigoReserva(Reserva $reserva): string
    {
        return (string) ($reserva->codigoReserva->codigo ?? $reserva->id);
    }

    private function intervalosCruzam(Carbon $inicioA, Carbon $fimA, Carbon $inicioB, Carbon $fimB): bool
    {
        return $inicioA->lt($fimB) && $fimA->gt($inicioB);
    }

    private function maiorData(Carbon $a, Carbon $b): Carbon
    {
        return $a->gt($b) ? $a->copy() : $b->copy();
    }

    private function menorData(Carbon $a, Carbon $b): Carbon
    {
        return $a->lt($b) ? $a->copy() : $b->copy();
    }

    private function percentualNoDia(Carbon $diaInicio, Carbon $diaFim, Carbon $momento): float
    {
        $total = max($diaInicio->diffInMinutes($diaFim), 1);
        $minutos = $diaInicio->diffInMinutes($momento);

        return round(($minutos / $total) * 100, 2);
    }

    private function percentualLargura(Carbon $diaInicio, Carbon $diaFim, Carbon $inicio, Carbon $fim): float
    {
        $total = max($diaInicio->diffInMinutes($diaFim), 1);
        $minutos = $inicio->diffInMinutes($fim);

        return round(($minutos / $total) * 100, 2);
    }

    private function getReservaBloqueioHoras(): int
    {
        $empresaContext = app(EmpresaContext::class);

        if (Auth::user()?->isAdministrador()) {
            $empresaId = $empresaContext->empresaId();
        } else {
            $empresaId = $empresaContext->empresaId() ?? Auth::user()?->empresa_id;
        }

        if (!$empresaId) {
            return 0;
        }

        if (!SchemaCache::hasTable('empresas') || !SchemaCache::hasColumn('empresas', 'reserva_bloqueio_pos_reserva_horas')) {
            return 0;
        }

        $horas = (int) (DB::table('empresas')
            ->where('id', (int) $empresaId)
            ->value('reserva_bloqueio_pos_reserva_horas') ?? 0);

        return max($horas, 0);
    }
}
