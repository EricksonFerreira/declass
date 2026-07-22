@php
    $dispMes = (int) ($dispMes ?? date('n'));
    $dispAno = (int) ($dispAno ?? date('Y'));
    $dispEstacaoId = (int) ($dispEstacaoId ?? 0);
    $dispDiasNoMes = (int) ($dispDiasNoMes ?? cal_days_in_month(CAL_GREGORIAN, $dispMes, $dispAno));
    $dispFirstDow = (int) ($dispFirstDow ?? \Carbon\Carbon::create($dispAno, $dispMes, 1)->dayOfWeekIso);

    $nomesMeses = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro',
    ];

    $totalDias = max($dispDiasNoMes, 1);

    $diasDisponiveis = (int) ($dispStats['ok'] ?? 0);
    $diasParciais = (int) ($dispStats['partial'] ?? 0);
    $diasReservados = (int) ($dispStats['reserved'] ?? 0) + (int) ($dispStats['overdue'] ?? 0);
    $diasIndisponiveis = (int) ($dispStats['off'] ?? 0);
    $diasSemLocal = (int) ($dispStats['pending'] ?? 0);

    $percentualDiasDisponiveis = $totalDias > 0
        ? (int) round(($diasDisponiveis / $totalDias) * 100)
        : 0;

    $filtrosAno = range(2024, date('Y') + 1);
@endphp

<div class="vd-box">

    <div class="vd-box__head">
        <div class="vd-box__title">
            <span class="vd-box__icon">
                <i class="fas fa-calendar-alt"></i>
            </span>

            <div>
                <strong>Disponibilidade do Veículo</strong>
                <small>Visualize por dia, horários e motivo do bloqueio</small>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('veiculo.show', $veiculo->id) }}" class="vd__filters">
        <div class="vd__filters-row">
            <div class="vd__filters-item">
                <label class="vd__label">Ano</label>
                <select name="disp_ano" class="vt-select">
                    @foreach($filtrosAno as $anoOption)
                        <option value="{{ $anoOption }}" {{ (int) $dispAno === (int) $anoOption ? 'selected' : '' }}>
                            {{ $anoOption }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="vd__filters-item">
                <label class="vd__label">Mês</label>
                <select name="disp_mes" class="vt-select">
                    @foreach($nomesMeses as $numeroMes => $nomeMes)
                        <option value="{{ $numeroMes }}" {{ (int) $dispMes === (int) $numeroMes ? 'selected' : '' }}>
                            {{ $nomeMes }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="vd__filters-item">
                <label class="vd__label">Estação</label>
                <select name="disp_estacao_id" class="vt-select">
                    <option value="0">Todas</option>

                    @foreach($dispEstacoes as $id => $nome)
                        <option value="{{ $id }}" {{ (int) $dispEstacaoId === (int) $id ? 'selected' : '' }}>
                            {{ $nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="vd__filters-actions">
                <button type="submit" class="btn-xp btn-xp--sm">
                    <i class="fas fa-filter mr-1"></i>Aplicar
                </button>

                <a href="{{ route('veiculo.show', $veiculo->id) }}" class="btn-xp btn-xp--sm btn-xp--outline">
                    Limpar
                </a>
            </div>
        </div>
    </form>

    <div class="vd__summary">
        <div class="vd__kpi">
            <div class="vd__kpi-label">Período</div>
            <div class="vd__kpi-value">{{ $nomesMeses[$dispMes] ?? $dispMes }}/{{ $dispAno }}</div>
        </div>

        <div class="vd__kpi">
            <div class="vd__kpi-label">Disponível</div>
            <div class="vd__kpi-value">{{ $diasDisponiveis }}/{{ $totalDias }} ({{ $percentualDiasDisponiveis }}%)</div>
        </div>

        <div class="vd__kpi">
            <div class="vd__kpi-label">Parcial</div>
            <div class="vd__kpi-value">{{ $diasParciais }}</div>
        </div>

        <div class="vd__kpi">
            <div class="vd__kpi-label">Reservas</div>
            <div class="vd__kpi-value">{{ $diasReservados }}</div>
        </div>

        <div class="vd__kpi">
            <div class="vd__kpi-label">Indisponível</div>
            <div class="vd__kpi-value">{{ $diasIndisponiveis }}</div>
        </div>

        <div class="vd__kpi">
            <div class="vd__kpi-label">Sem local</div>
            <div class="vd__kpi-value">{{ $diasSemLocal }}</div>
        </div>
    </div>

    @if(!empty($reservaBloqueioHoras))
        <div class="vd__note">
            <i class="fas fa-clock mr-1"></i>
            Existe bloqueio pós-reserva de <strong>{{ $reservaBloqueioHoras }}h</strong>.
        </div>
    @endif

    <div class="vd__legend">
        <button type="button" class="vd__legend-btn is-active" data-vd-filter="all">
            <span class="vd__legend-dot is-ok"></span>Todos
        </button>

        <button type="button" class="vd__legend-btn" data-vd-filter="ok">
            <span class="vd__legend-dot is-ok"></span>Disponível
        </button>

        <button type="button" class="vd__legend-btn" data-vd-filter="partial">
            <span class="vd__legend-dot is-partial"></span>Parcial
        </button>

        <button type="button" class="vd__legend-btn" data-vd-filter="reserved">
            <span class="vd__legend-dot is-reserved"></span>Reserva
        </button>

        <button type="button" class="vd__legend-btn" data-vd-filter="off">
            <span class="vd__legend-dot is-off"></span>Indisponível
        </button>

        <button type="button" class="vd__legend-btn" data-vd-filter="pending">
            <span class="vd__legend-dot is-pending"></span>Sem local
        </button>
    </div>

    <div class="vd__weekdays">
        <div>Seg</div>
        <div>Ter</div>
        <div>Qua</div>
        <div>Qui</div>
        <div>Sex</div>
        <div>Sáb</div>
        <div>Dom</div>
    </div>

    <div class="vd__calendar" id="vdCalendar">
        @for($empty = 1; $empty < $dispFirstDow; $empty++)
            <div class="vd__day is-empty"></div>
        @endfor

        @for($dia = 1; $dia <= $dispDiasNoMes; $dia++)
            @php
                $cell = $dispGrade[$dia] ?? [
                    'tipo' => 'off',
                    'titulo' => 'Indisponível',
                    'percentual_disponivel' => 0,
                    'intervalos' => [],
                    'reserva_id' => null,
                    'reserva_codigo' => null,
                ];

                $tipo = (string) ($cell['tipo'] ?? 'off');
                $tituloCell = (string) ($cell['titulo'] ?? 'Indisponível');
                $percentualDia = (int) ($cell['percentual_disponivel'] ?? 0);

                $dataDia = \Carbon\Carbon::create($dispAno, $dispMes, $dia);
                $isHoje = $dataDia->isToday();

                $icone = match ($tipo) {
                    'ok' => 'fa-check',
                    'partial' => 'fa-adjust',
                    'reserved' => 'fa-calendar-check',
                    'overdue' => 'fa-clock',
                    'pending' => 'fa-map-marker-alt',
                    default => 'fa-times',
                };

                $filterTipo = in_array($tipo, ['overdue'], true) ? 'reserved' : $tipo;

                $intervalos = $cell['intervalos'] ?? [];

                if (empty($intervalos)) {
                    $intervalos = [[
                        'tipo' => $tipo,
                        'titulo' => $tituloCell,
                        'inicio' => '00:00',
                        'fim' => '23:59',
                        'percentual_inicio' => 0,
                        'percentual_largura' => 100,
                        'detalhes' => null,
                    ]];
                }

                $intervalosJson = e(json_encode($intervalos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            @endphp

            <button
                type="button"
                class="vd__day js-disp-day-detail {{ $isHoje ? 'is-today' : '' }}"
                data-vd-type="{{ $filterTipo }}"
                data-dia="{{ str_pad((string) $dia, 2, '0', STR_PAD_LEFT) }}"
                data-tipo="{{ $tipo }}"
                data-titulo="{{ $tituloCell }}"
                data-percentual="{{ $percentualDia }}"
                data-intervalos="{{ $intervalosJson }}"
                title="{{ $tituloCell }}"
            >
                <span class="vd__num">{{ $dia }}</span>

                <span class="vd__dot is-{{ $tipo }}">
                    <i class="fas {{ $icone }}"></i>
                </span>
            </button>
        @endfor
    </div>

    <div id="dispDayPanel" class="disp-day-panel">
        <div class="disp-day-panel__header">
            <div>
                <strong id="dispDayPanelTitle">Detalhes do dia</strong>
                <span id="dispDayPanelSubtitle">-</span>
            </div>

            <button type="button" class="disp-day-panel__close" id="dispDayPanelClose">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="disp-day-panel__body">
            <div class="disp-day-panel__status">
                <div>
                    <small>Status do dia</small>
                    <strong id="dispDayPanelStatus">-</strong>
                </div>

                <span id="dispDayPanelPercent" class="disp-day-panel__percent">0%</span>
            </div>

            <div class="disp-day-panel__section-title">Linha do tempo do dia</div>

            <div id="dispDayTimeline" class="disp-day-timeline"></div>

            <div class="disp-day-timeline-labels">
                <span>00:00</span>
                <span>06:00</span>
                <span>12:00</span>
                <span>18:00</span>
                <span>23:59</span>
            </div>

            <div class="disp-day-panel__section-title">Detalhes do bloqueio</div>

            <div id="dispDaySchedule" class="disp-day-schedule"></div>
        </div>
    </div>

    <div id="dispDayPanelBackdrop" class="disp-day-panel-backdrop"></div>

    <div class="vd__lists">
        <details class="vd__details">
            <summary>
                <strong>Reservas no período</strong>
                <span>({{ $dispReservas->count() }})</span>
            </summary>

            @if($dispReservas->isEmpty())
                <div class="disp-empty">Nenhuma reserva no período.</div>
            @else
                <div class="table-responsive mt-2">
                    <table class="table table-sm table-hover mb-0 disp-mov-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Status</th>
                                <th>Início</th>
                                <th>Fim</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($dispReservas as $reserva)
                                <tr>
                                    <td>
                                        <a href="{{ route('reserva.show', $reserva->id) }}" class="xp-link">
                                            #{{ $reserva->id }}
                                        </a>
                                    </td>

                                    <td>{{ optional($reserva->statusReserva)->nome ?? '—' }}</td>

                                    <td>
                                        {{ $reserva->data_inicio
                                            ? \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i')
                                            : '—'
                                        }}
                                    </td>

                                    <td>
                                        {{ $reserva->data_fim
                                            ? \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i')
                                            : '—'
                                        }}
                                    </td>

                                    <td class="text-right">
                                        <a href="{{ route('reserva.show', $reserva->id) }}" class="btn btn-xs btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </details>

        <details class="vd__details" open>
            <summary>
                <strong>Movimentações no período</strong>
                <span>({{ $dispMovimentacoes->count() }})</span>
            </summary>

            @if($dispMovimentacoes->isEmpty())
                <div class="disp-empty">Nenhuma movimentação no período.</div>
            @else
                <div class="table-responsive mt-2">
                    <table class="table table-sm table-hover mb-0 disp-mov-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Origem</th>
                                <th>Destino</th>
                                <th>Registro</th>
                                <th>Saída</th>
                                <th>Chegada prevista</th>
                                <th>Chegada real</th>
                                <th>Reserva</th>
                                <th>Usuário</th>
                                <th>Obs.</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($dispMovimentacoes as $mov)
                                @php
                                    $statusCor = optional($mov->statusMovimentacao)->cor ?? 'secondary';

                                    $statusBadgeClass = [
                                        'success' => 'badge-success',
                                        'info' => 'badge-info',
                                        'primary' => 'badge-primary',
                                        'warning' => 'badge-warning',
                                        'orange' => 'badge-warning',
                                        'danger' => 'badge-danger',
                                        'secondary' => 'badge-secondary',
                                        'gray' => 'badge-secondary',
                                        'grey' => 'badge-secondary',
                                        'purple' => 'badge-primary',
                                        'teal' => 'badge-info',
                                    ][strtolower((string) $statusCor)] ?? 'badge-secondary';
                                @endphp

                                <tr>
                                    <td>
                                        <a href="{{ route('movimentacao.edit', $mov->id) }}" class="xp-link">
                                            #{{ $mov->id }}
                                        </a>
                                    </td>

                                    <td>{{ optional($mov->tipoMovimentacao)->nome ?? '—' }}</td>

                                    <td>
                                        <span class="badge {{ $statusBadgeClass }}">
                                            {{ optional($mov->statusMovimentacao)->nome ?? '—' }}
                                        </span>
                                    </td>

                                    <td>{{ optional($mov->localOrigem)->nome ?? '—' }}</td>
                                    <td>{{ optional($mov->localDestino)->nome ?? '—' }}</td>

                                    <td>
                                        {{ $mov->data_registro
                                            ? \Carbon\Carbon::parse($mov->data_registro)->format('d/m/Y H:i')
                                            : '—'
                                        }}
                                    </td>

                                    <td>
                                        {{ $mov->data_saida
                                            ? \Carbon\Carbon::parse($mov->data_saida)->format('d/m/Y H:i')
                                            : '—'
                                        }}
                                    </td>

                                    <td>
                                        {{ $mov->data_chegada_prevista
                                            ? \Carbon\Carbon::parse($mov->data_chegada_prevista)->format('d/m/Y H:i')
                                            : '—'
                                        }}
                                    </td>

                                    <td>
                                        @if($mov->data_chegada_real)
                                            {{ \Carbon\Carbon::parse($mov->data_chegada_real)->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-danger font-weight-bold">Sem chegada</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($mov->reserva_id)
                                            <a href="{{ route('reserva.show', $mov->reserva_id) }}" class="xp-link">
                                                #{{ $mov->reserva?->codigoReserva?->codigo ?? $mov->reserva_id }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td>{{ optional($mov->usuario)->nome ?? '—' }}</td>

                                    <td class="obs-cell">
                                        {{ \Illuminate\Support\Str::limit((string) ($mov->observacoes ?? ''), 50) ?: '—' }}
                                    </td>

                                    <td class="text-right">
                                        <a href="{{ route('movimentacao.edit', $mov->id) }}" class="btn btn-xs btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </details>
    </div>
</div>

@once
    <style>
        .vd-box__head {
            margin-bottom: 14px;
            padding-bottom: 14px;
            border-bottom: 1px dashed #e5eaf3;
        }

        .vd-box__title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .vd-box__icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #eef2ff;
            color: #4338ca;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .vd-box__title strong {
            display: block;
            color: #0c1428;
            font-size: .9rem;
        }

        .vd-box__title small {
            display: block;
            color: #64748b;
            font-size: .74rem;
        }

        .vd__dot.is-partial {
            background: linear-gradient(90deg, #16a34a 0%, #16a34a 50%, #f59e0b 50%, #f59e0b 100%);
        }

        .vd__dot.is-overdue {
            background: #92400e;
        }

        .vd__legend-dot {
            width: 18px;
            height: 18px;
            border-radius: 999px;
            display: inline-flex;
        }

        .vd__legend-dot.is-ok {
            background: #16a34a;
        }

        .vd__legend-dot.is-partial {
            background: linear-gradient(90deg, #16a34a 0%, #16a34a 50%, #f59e0b 50%, #f59e0b 100%);
        }

        .vd__legend-dot.is-reserved {
            background: #d97706;
        }

        .vd__legend-dot.is-off {
            background: #dc2626;
        }

        .vd__legend-dot.is-pending {
            background: #64748b;
        }

        .vd__day {
            cursor: pointer;
            text-align: left;
            border: 1px solid #e9f0fa;
        }

        .vd__day:focus {
            outline: 2px solid rgba(67, 56, 202, .35);
            outline-offset: 2px;
        }

        .disp-day-panel {
            position: fixed;
            top: 80px;
            right: 24px;
            width: 460px;
            max-width: calc(100vw - 48px);
            background: #fff;
            border: 1px solid #dbe7f5;
            border-radius: 18px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .24);
            z-index: 1060;
            display: none;
            overflow: hidden;
        }

        .disp-day-panel.is-open {
            display: block;
        }

        .disp-day-panel-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .08);
            z-index: 1050;
        }

        .disp-day-panel-backdrop.is-open {
            display: block;
        }

        .disp-day-panel__header {
            padding: 16px 18px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .disp-day-panel__header strong {
            display: block;
            color: #0f172a;
        }

        .disp-day-panel__header span {
            display: block;
            color: #64748b;
            font-size: 12px;
            margin-top: 2px;
        }

        .disp-day-panel__close {
            border: 0;
            background: transparent;
            color: #0f172a;
            font-size: 16px;
            cursor: pointer;
        }

        .disp-day-panel__body {
            padding: 18px;
        }

        .disp-day-panel__status {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            border-radius: 14px;
            padding: 13px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .disp-day-panel__status small {
            display: block;
            color: #64748b;
            font-size: 11px;
        }

        .disp-day-panel__status strong {
            display: block;
            color: #0f172a;
            font-size: 13px;
            margin-top: 3px;
        }

        .disp-day-panel__percent {
            width: 54px;
            height: 54px;
            border-radius: 999px;
            border: 4px solid #22c55e;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #166534;
            background: #fff;
            font-weight: 900;
            font-size: 13px;
        }

        .disp-day-panel__section-title {
            font-weight: 800;
            color: #0f172a;
            font-size: 13px;
            margin: 12px 0 8px;
        }

        .disp-day-timeline {
            position: relative;
            height: 13px;
            border-radius: 999px;
            background: #e5e7eb;
            overflow: hidden;
        }

        .disp-day-timeline__segment {
            position: absolute;
            top: 0;
            height: 100%;
            min-width: 1px;
        }

        .disp-day-timeline__segment--ok {
            background: #16a34a;
        }

        .disp-day-timeline__segment--partial {
            background: #f59e0b;
        }

        .disp-day-timeline__segment--reserved {
            background: #d97706;
        }

        .disp-day-timeline__segment--overdue {
            background: #92400e;
        }

        .disp-day-timeline__segment--off {
            background: #dc2626;
        }

        .disp-day-timeline__segment--pending {
            background: #64748b;
        }

        .disp-day-timeline-labels {
            display: flex;
            justify-content: space-between;
            color: #64748b;
            font-size: 11px;
            margin-top: 7px;
        }

        .disp-day-schedule {
            border: 1px solid #e2e8f0;
            border-radius: 13px;
            overflow: hidden;
        }

        .disp-schedule-item {
            display: grid;
            grid-template-columns: 14px 105px 1fr;
            gap: 8px;
            align-items: flex-start;
            padding: 11px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
        }

        .disp-schedule-item:last-child {
            border-bottom: 0;
        }

        .disp-schedule-item__dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            margin-top: 4px;
        }

        .disp-schedule-item__dot--ok {
            background: #16a34a;
        }

        .disp-schedule-item__dot--partial {
            background: #f59e0b;
        }

        .disp-schedule-item__dot--reserved {
            background: #d97706;
        }

        .disp-schedule-item__dot--overdue {
            background: #92400e;
        }

        .disp-schedule-item__dot--off {
            background: #dc2626;
        }

        .disp-schedule-item__dot--pending {
            background: #64748b;
        }

        .disp-schedule-item__time {
            font-weight: 800;
            color: #334155;
        }

        .disp-schedule-item__content strong {
            display: block;
            color: #0f172a;
            line-height: 1.35;
        }

        .disp-block-details {
            margin-top: 8px;
            padding: 9px 10px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 11px;
            line-height: 1.6;
        }

        .disp-block-details div + div {
            margin-top: 2px;
        }

        .disp-empty {
            padding: 12px 13px;
            color: #64748b;
            font-size: 12px;
        }

        .disp-mov-table th {
            font-size: 11px;
            text-transform: uppercase;
            color: #334155;
            background: #f1f5f9;
            white-space: nowrap;
        }

        .disp-mov-table td {
            font-size: 12px;
            vertical-align: middle;
            white-space: nowrap;
        }

        @media (max-width: 767px) {
            .disp-day-panel {
                top: 70px;
                left: 12px;
                right: 12px;
                width: auto;
                max-width: none;
            }

            .disp-schedule-item {
                grid-template-columns: 14px 1fr;
            }

            .disp-schedule-item__time {
                grid-column: 2;
            }

            .disp-schedule-item__content {
                grid-column: 2;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const panel = document.getElementById('dispDayPanel');
            const backdrop = document.getElementById('dispDayPanelBackdrop');
            const closeBtn = document.getElementById('dispDayPanelClose');

            const title = document.getElementById('dispDayPanelTitle');
            const subtitle = document.getElementById('dispDayPanelSubtitle');
            const status = document.getElementById('dispDayPanelStatus');
            const percent = document.getElementById('dispDayPanelPercent');
            const timeline = document.getElementById('dispDayTimeline');
            const schedule = document.getElementById('dispDaySchedule');

            function escapeHtml(value) {
                return (value || '').toString()
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function tipoLabel(tipo) {
                const labels = {
                    ok: 'Disponível',
                    partial: 'Parcialmente disponível',
                    reserved: 'Reservado',
                    overdue: 'Reserva não finalizada',
                    off: 'Indisponível',
                    pending: 'Sem local'
                };

                return labels[tipo] || tipo;
            }

            function abrirPainel(button) {
                const dia = button.dataset.dia || '-';
                const tipo = button.dataset.tipo || 'off';
                const titulo = button.dataset.titulo || tipoLabel(tipo);
                const percentual = button.dataset.percentual || '0';

                let intervalos = [];

                try {
                    intervalos = JSON.parse(button.dataset.intervalos || '[]');
                } catch (e) {
                    intervalos = [];
                }

                if (!intervalos.length) {
                    intervalos = [{
                        tipo: tipo,
                        titulo: titulo,
                        inicio: '00:00',
                        fim: '23:59',
                        percentual_inicio: 0,
                        percentual_largura: 100,
                        detalhes: null
                    }];
                }

                title.textContent = 'Detalhes do dia — ' + dia;
                subtitle.textContent = tipoLabel(tipo);
                status.textContent = titulo;
                percent.textContent = percentual + '%';

                timeline.innerHTML = '';
                schedule.innerHTML = '';

                intervalos.forEach(function (item) {
                    const itemTipo = item.tipo || 'off';
                    const inicio = item.inicio || '--:--';
                    const fim = item.fim || '--:--';
                    const label = item.titulo || tipoLabel(itemTipo);
                    const left = Number(item.percentual_inicio || 0);
                    const width = Math.max(Number(item.percentual_largura || 0), 1);

                    const segment = document.createElement('span');
                    segment.className = 'disp-day-timeline__segment disp-day-timeline__segment--' + itemTipo;
                    segment.style.left = left + '%';
                    segment.style.width = width + '%';
                    segment.title = inicio + ' - ' + fim + ' | ' + label;

                    timeline.appendChild(segment);

                    let detalhesHtml = '';

                    if (item.detalhes && item.detalhes.origem === 'movimentacao') {
                        detalhesHtml = `
                            <div class="disp-block-details">
                                <div><strong>Motivo:</strong> ${escapeHtml(item.detalhes.motivo_bloqueio || '-')}</div>
                                <div><strong>Movimentação:</strong> #${escapeHtml(item.detalhes.id || '-')}</div>
                                <div><strong>Tipo:</strong> ${escapeHtml(item.detalhes.tipo_movimentacao || '-')}</div>
                                <div><strong>Status:</strong> ${escapeHtml(item.detalhes.status_movimentacao || '-')}</div>
                                <div><strong>Origem:</strong> ${escapeHtml(item.detalhes.local_origem || '-')}</div>
                                <div><strong>Destino:</strong> ${escapeHtml(item.detalhes.local_destino || '-')}</div>
                                <div><strong>Usuário:</strong> ${escapeHtml(item.detalhes.usuario || '-')}</div>
                                <div><strong>Registro:</strong> ${escapeHtml(item.detalhes.data_registro || '-')}</div>
                                <div><strong>Saída:</strong> ${escapeHtml(item.detalhes.data_saida || '-')}</div>
                                <div><strong>Chegada prevista:</strong> ${escapeHtml(item.detalhes.data_chegada_prevista || '-')}</div>
                                <div><strong>Chegada real:</strong> ${escapeHtml(item.detalhes.data_chegada_real || 'Não registrada')}</div>
                                ${
                                    item.detalhes.km_saida
                                        ? `<div><strong>KM saída:</strong> ${escapeHtml(item.detalhes.km_saida)}</div>`
                                        : ''
                                }
                                ${
                                    item.detalhes.km_chegada
                                        ? `<div><strong>KM chegada:</strong> ${escapeHtml(item.detalhes.km_chegada)}</div>`
                                        : ''
                                }
                                ${
                                    item.detalhes.reserva_id
                                        ? `<div><strong>Reserva:</strong> #${escapeHtml(item.detalhes.reserva_codigo || item.detalhes.reserva_id)}</div>`
                                        : ''
                                }
                                ${
                                    item.detalhes.ordem_servico_numero
                                        ? `<div><strong>OS:</strong> ${escapeHtml(item.detalhes.ordem_servico_numero)}</div>`
                                        : ''
                                }
                                ${
                                    item.detalhes.observacoes
                                        ? `<div><strong>Observações:</strong> ${escapeHtml(item.detalhes.observacoes)}</div>`
                                        : ''
                                }
                            </div>
                        `;
                    }

                    schedule.insertAdjacentHTML('beforeend', `
                        <div class="disp-schedule-item">
                            <span class="disp-schedule-item__dot disp-schedule-item__dot--${escapeHtml(itemTipo)}"></span>

                            <div class="disp-schedule-item__time">
                                ${escapeHtml(inicio)} - ${escapeHtml(fim)}
                            </div>

                            <div class="disp-schedule-item__content">
                                <strong>${escapeHtml(label)}</strong>
                                ${detalhesHtml}
                            </div>
                        </div>
                    `);
                });

                panel.classList.add('is-open');
                backdrop.classList.add('is-open');
            }

            function fecharPainel() {
                panel.classList.remove('is-open');
                backdrop.classList.remove('is-open');
            }

            document.querySelectorAll('.js-disp-day-detail').forEach(function (button) {
                button.addEventListener('click', function () {
                    abrirPainel(this);
                });
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', fecharPainel);
            }

            if (backdrop) {
                backdrop.addEventListener('click', fecharPainel);
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    fecharPainel();
                }
            });
        });
    </script>
@endonce
