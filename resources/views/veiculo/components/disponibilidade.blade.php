@php
    $meses = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Marco',
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
    $anos = range(2024, ((int) date('Y') + 1));
    $mesNome = $meses[(int) ($dispMes ?? date('n'))] ?? 'Mes';
    $totalDias = (int) ($dispDiasNoMes ?? 0);
    $pct = $totalDias > 0 ? (int) round(((int) ($dispStats['ok'] ?? 0) / $totalDias) * 100) : 0;
    $dispEstacaoId = (int) ($dispEstacaoId ?? 0);
    $dispEstacoes = $dispEstacoes ?? [];
@endphp

<div class="config-card mb-4">
    <div class="config-block">
        <div class="config-block-label">
            <i class="fas fa-calendar-alt"></i>
            <div>
                <strong>Disponibilidade do Veiculo</strong>
                <span>Visualize por dia e filtre o periodo</span>
            </div>
        </div>

        <form method="GET" action="{{ route('veiculo.show', $veiculo->id) }}" class="vd__filters">
            <div class="vd__filters-row">
                <div class="vd__filters-item">
                    <label class="vd__label">Ano</label>
                    <select name="disp_ano" class="form-control form-control-sm">
                        @foreach($anos as $a)
                            <option value="{{ $a }}" {{ (int) ($dispAno ?? date('Y')) === (int) $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="vd__filters-item">
                    <label class="vd__label">Mes</label>
                    <select name="disp_mes" class="form-control form-control-sm">
                        @foreach($meses as $m => $nome)
                            <option value="{{ $m }}" {{ (int) ($dispMes ?? date('n')) === (int) $m ? 'selected' : '' }}>{{ $nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="vd__filters-item">
                    <label class="vd__label">Estacao</label>
                    <select name="disp_estacao_id" class="form-control form-control-sm">
                        <option value="0">Todas</option>
                        @foreach($dispEstacoes as $id => $nome)
                            <option value="{{ $id }}" {{ (int) $dispEstacaoId === (int) $id ? 'selected' : '' }}>{{ $nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="vd__filters-actions">
                    <button type="submit" class="btn-xp btn-xp--sm">
                        <i class="fas fa-filter mr-1"></i>Aplicar
                    </button>
                    <a href="{{ route('veiculo.show', $veiculo->id) }}" class="btn-xp btn-xp--outline btn-xp--sm">
                        Limpar
                    </a>
                </div>
            </div>
        </form>

        @if($dispEstacaoId > 0 && (int) ($veiculo->local_disponibilidade ?? 0) !== $dispEstacaoId)
            <div class="vd__note">
                <i class="fas fa-map-marker-alt mr-1"></i>
                Esta grade esta considerando a estacao selecionada. O veiculo esta em <strong>{{ optional($veiculo->localDisponibilidade)->nome ?? 'outra estacao' }}</strong>.
            </div>
        @endif

        <div class="vd__summary">
            <div class="vd__kpi">
                <div class="vd__kpi-label">Periodo</div>
                <div class="vd__kpi-value">{{ $mesNome }}/{{ $dispAno ?? date('Y') }}</div>
            </div>
            <div class="vd__kpi">
                <div class="vd__kpi-label">Disponivel</div>
                <div class="vd__kpi-value">{{ (int) ($dispStats['ok'] ?? 0) }}/{{ $totalDias }} ({{ $pct }}%)</div>
            </div>
            <div class="vd__kpi">
                <div class="vd__kpi-label">Reservas</div>
                <div class="vd__kpi-value">{{ (int) ($dispStats['reserved'] ?? 0) }}</div>
            </div>
            <div class="vd__kpi">
                <div class="vd__kpi-label">Indisponivel</div>
                <div class="vd__kpi-value">{{ (int) ($dispStats['off'] ?? 0) }}</div>
            </div>
            <div class="vd__kpi">
                <div class="vd__kpi-label">Sem local</div>
                <div class="vd__kpi-value">{{ (int) ($dispStats['pending'] ?? 0) }}</div>
            </div>
        </div>

        @if(!empty($reservaBloqueioHoras) && (int) $reservaBloqueioHoras > 0)
            <div class="vd__note">
                <i class="fas fa-broom mr-1"></i>
                Folga pos-reserva configurada: <strong>{{ (int) $reservaBloqueioHoras }}h</strong>
            </div>
        @endif

        <div class="vd__legend">
            <button type="button" class="vd__legend-btn is-active" data-vd-filter="all">
                <span class="vd__dot is-ok"></span> Todos
            </button>
            <button type="button" class="vd__legend-btn" data-vd-filter="ok">
                <span class="vd__dot is-ok"></span> Disponivel
            </button>
            <button type="button" class="vd__legend-btn" data-vd-filter="reserved">
                <span class="vd__dot is-reserved"></span> Reserva
            </button>
            <button type="button" class="vd__legend-btn" data-vd-filter="off">
                <span class="vd__dot is-off"></span> Indisponivel
            </button>
            <button type="button" class="vd__legend-btn" data-vd-filter="pending">
                <span class="vd__dot is-pending"></span> Sem local
            </button>
        </div>

        <div class="vd__weekdays">
            <div>Seg</div><div>Ter</div><div>Qua</div><div>Qui</div><div>Sex</div><div>Sab</div><div>Dom</div>
        </div>

        <div class="vd__calendar" id="vdCalendar">
            @php
                $offset = max(0, ((int) ($dispFirstDow ?? 1)) - 1);
            @endphp

            @for($i = 0; $i < $offset; $i++)
                <div class="vd__day is-empty"></div>
            @endfor

            @for($dia = 1; $dia <= (int) ($dispDiasNoMes ?? 0); $dia++)
                @php
                    $cell = $dispGrade[$dia] ?? ['tipo' => 'off', 'titulo' => 'Indisponivel', 'reserva_id' => null];
                    $tipo = $cell['tipo'] ?? 'off';
                    $title = $cell['titulo'] ?? '';
                    $reservaId = $cell['reserva_id'] ?? null;
                    $isToday = ((int) ($dispAno ?? date('Y')) === (int) date('Y')) && ((int) ($dispMes ?? date('n')) === (int) date('n')) && ((int) date('j') === (int) $dia);
                @endphp

                @if($tipo === 'reserved' && $reservaId)
                    <a
                        href="{{ route('reserva.show', $reservaId) }}"
                        class="vd__day is-{{ $tipo }} {{ $isToday ? 'is-today' : '' }}"
                        data-vd-type="{{ $tipo }}"
                        title="{{ $title }}"
                    >
                        <span class="vd__num">{{ $dia }}</span>
                        <span class="vd__dot is-reserved"><i class="fas fa-calendar-check"></i></span>
                    </a>
                @else
                    <div
                        class="vd__day is-{{ $tipo }} {{ $isToday ? 'is-today' : '' }}"
                        data-vd-type="{{ $tipo }}"
                        title="{{ $title }}"
                    >
                        <span class="vd__num">{{ $dia }}</span>
                        @if($tipo === 'ok')
                            <span class="vd__dot is-ok"><i class="fas fa-check"></i></span>
                        @elseif($tipo === 'pending')
                            <span class="vd__dot is-pending"><i class="fas fa-map-marker-alt"></i></span>
                        @else
                            <span class="vd__dot is-off"><i class="fas fa-times"></i></span>
                        @endif
                    </div>
                @endif
            @endfor
        </div>

        <div class="vd__lists">
            <details class="vd__details">
                <summary>
                    <strong>Reservas no periodo</strong>
                    <span class="text-muted">({{ $dispReservas?->count() ?? 0 }})</span>
                </summary>
                @if(($dispReservas?->count() ?? 0) === 0)
                    <div class="text-muted small py-2">Nenhuma reserva no periodo.</div>
                @else
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-hover xp-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Inicio</th>
                                    <th>Fim</th>
                                    <th class="text-right">Acoes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dispReservas as $r)
                                    <tr>
                                        <td><span class="xp-id">#{{ $r->id }}</span></td>
                                        <td>{{ $r->data_inicio ? \Carbon\Carbon::parse($r->data_inicio)->format('d/m/Y H:i') : '—' }}</td>
                                        <td>{{ $r->data_fim ? \Carbon\Carbon::parse($r->data_fim)->format('d/m/Y H:i') : '—' }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('reserva.show', $r->id) }}" class="btn-xp btn-xp--sm btn-xp--outline">
                                                <i class="fas fa-eye mr-1"></i>Ver
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </details>

            <details class="vd__details">
                <summary>
                    <strong>Movimentacoes no periodo</strong>
                    <span class="text-muted">({{ $dispMovimentacoes?->count() ?? 0 }})</span>
                </summary>
                @if(($dispMovimentacoes?->count() ?? 0) === 0)
                    <div class="text-muted small py-2">Nenhuma movimentacao no periodo.</div>
                @else
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-hover xp-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Saida</th>
                                    <th>Chegada</th>
                                    <th>Reserva</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dispMovimentacoes as $m)
                                    <tr>
                                        <td><span class="xp-id">#{{ $m->id }}</span></td>
                                        <td>{{ $m->data_saida ? \Carbon\Carbon::parse($m->data_saida)->format('d/m/Y H:i') : '—' }}</td>
                                        <td>{{ $m->data_chegada_real ? \Carbon\Carbon::parse($m->data_chegada_real)->format('d/m/Y H:i') : '—' }}</td>
                                        <td>
                                            @if($m->reserva_id)
                                                <a class="xp-link" href="{{ route('reserva.show', $m->reserva_id) }}">#{{ $m->reserva_id }}</a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
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
</div>
