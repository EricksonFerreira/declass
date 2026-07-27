@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('veiculo.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <div class="veiculo-filter-sticky">
            <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
        </div>
    @endif

    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">
                    REGISTROS <span class="badge badge-success">{{ $veiculos->total() }}</span>
                </h3>

                <div class="align-bottom d-flex flex-row justify-between" style="gap:6px;flex-wrap:wrap;">
                    <form action="{{ route('veiculo.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>

                    {{-- Ordenação --}}
                    <form action="{{ route('veiculo.setOrdenacao') }}" method="POST" class="d-flex align-items-center"
                        style="gap:4px;">
                        @csrf
                        <select name="orderBy" class="form-control form-control-xs"
                            style="height:28px;font-size:.8rem;padding:2px 6px;border-radius:6px;border:1px solid #d0daea;"
                            onchange="this.form.submit()">
                            @php
                                $ordens = [
                                    'id' => 'ID',
                                    'nome' => 'Nome',
                                    'placa_licenca' => 'Placa',
                                    'renavam' => 'RENAVAM',
                                    'ano_fabricacao' => 'Ano Fab.',
                                    'kms' => 'Km',
                                    'created_at' => 'Cadastro',
                                ];
                            @endphp
                            @foreach ($ordens as $val => $label)
                                <option value="{{ $val }}" {{ ($orderBy ?? 'id') === $val ? 'selected' : '' }}>
                                    Ord.: {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <select name="orderDir" class="form-control form-control-xs"
                            style="height:28px;font-size:.8rem;padding:2px 6px;border-radius:6px;border:1px solid #d0daea;"
                            onchange="this.form.submit()">
                            <option value="asc" {{ ($orderDir ?? 'asc') === 'asc' ? 'selected' : '' }}>↑ Cresc.</option>
                            <option value="desc" {{ ($orderDir ?? 'asc') === 'desc' ? 'selected' : '' }}>↓ Decresc.
                            </option>
                        </select>
                    </form>

                    <div class="veiculo-disponibilidade-filter" title="Filtrar por disponibilidade (pagina atual)">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-avail="todos">
                            <i class="fas fa-list mr-1"></i>Todos
                        </button>

                        <button type="button" class="btn btn-xs btn-outline-secondary" data-avail="disponivel">
                            <i class="fas fa-check mr-1"></i>Disponível
                        </button>

                        <button type="button" class="btn btn-xs btn-outline-secondary" data-avail="parcial">
                            <i class="fas fa-adjust mr-1"></i>Parcial
                        </button>

                        <button type="button" class="btn btn-xs btn-outline-secondary" data-avail="indisponivel">
                            <i class="fas fa-times mr-1"></i>Indisponível
                        </button>
                    </div>

                    <div class="veiculo-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i> Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i> Tabela
                        </button>
                    </div>

                    <div class="d-flex align-items-end">
                        {{ $veiculos->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 veiculo-index-body" data-view="cards">
            <div class="veiculo-cards">
                @foreach ($veiculos as $veiculo)
                    @php
                        $nomeVeiculo = (string) ($veiculo->nome ?? 'Veiculo');
                        $placaVeiculo = (string) ($veiculo->placa_licenca ?? '');

                        $partesNome = preg_split('/\s+/', trim($nomeVeiculo)) ?: [];
                        $iniciais = collect($partesNome)
                            ->filter()
                            ->take(2)
                            ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                            ->implode('');

                        $iniciais = $iniciais ?: 'VC';

                        $resumoDisponibilidade = $resumoVeiculosIndex[$veiculo->id] ?? [
                            'percentual' => 0,
                            'status_mes' => 'off',
                            'dias_parciais' => 0,
                            'dias_indisponiveis' => 0,
                        ];

                        $percentualDisponibilidade = (int) ($resumoDisponibilidade['percentual'] ?? 0);
                        $statusMesDisponibilidade = (string) ($resumoDisponibilidade['status_mes'] ?? 'off');

                        $disponivel = $statusMesDisponibilidade === 'ok' && $percentualDisponibilidade >= 100;
                        $parcial =
                            $statusMesDisponibilidade === 'partial' ||
                            ($percentualDisponibilidade > 0 && $percentualDisponibilidade < 100);
                        $indisponivel = $percentualDisponibilidade <= 0;

                        $availKey = $disponivel ? 'disponivel' : ($parcial ? 'parcial' : 'indisponivel');

                        $accentClass = match ($availKey) {
                            'disponivel' => 'vc-status--green',
                            'parcial' => 'vc-status--amber',
                            default => 'vc-status--red',
                        };

                        $statusLabelDisponibilidade = match ($availKey) {
                            'disponivel' => 'Disponível',
                            'parcial' => 'Parcial',
                            default => 'Indisponível',
                        };

                        $statusBadgeClassDisponibilidade = match ($availKey) {
                            'disponivel' => 'vc__badge--green',
                            'parcial' => 'vc__badge--amber',
                            default => 'vc__badge--red',
                        };

                        $statusCor = (string) ($veiculo->statusVeiculo?->cor ?? 'secondary');
                        $statusNome = (string) ($veiculo->statusVeiculo?->nome ?? '');
                        $localNome = (string) ($veiculo->localDisponibilidade?->nome ?? '-');

                        $dataDisp = $veiculo->data_hora_disponibilidade
                            ? \Carbon\Carbon::parse($veiculo->data_hora_disponibilidade)->format('d/m/Y H:i')
                            : '-';

                        $showStatusBadge = $statusNome !== '';
                    @endphp

                    <article class="vc {{ $accentClass }}" data-avail="{{ $availKey }}"
                        onclick="window.location='{{ route('veiculo.show', $veiculo->id) }}';">
                        <div class="vc__accent"></div>

                        <div class="vc__head">
                            <div class="vc__avatar">{{ $iniciais }}</div>
                            <div class="vc__head-info">
                                <div class="vc__id">VEICULO #{{ $veiculo->id }}</div>
                                <div class="vc__title">{{ $nomeVeiculo }}</div>
                                <div class="vc__sub">{{ $placaVeiculo ? 'Placa: ' . $placaVeiculo : 'Placa: -' }}</div>
                            </div>
                            <div class="vc__badges">
                                <span class="vc__badge {{ $statusBadgeClassDisponibilidade }}">
                                    {{ $statusLabelDisponibilidade }} · {{ $percentualDisponibilidade }}%
                                </span>
                                @if ($showStatusBadge)
                                    <span class="badge badge-xs bg-{{ $statusCor }}" title="Status do veiculo">
                                        {{ $statusNome }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="vc__meta">
                            <div class="vc__meta-row">
                                <span class="vc__meta-label"><i class="fas fa-layer-group mr-1"></i>Grupo</span>
                                <strong class="vc__meta-value">{{ $veiculo->grupoFrota?->nome ?? '-' }}</strong>
                            </div>
                            <div class="vc__meta-row">
                                <span class="vc__meta-label"><i class="fas fa-truck-pickup mr-1"></i>Tipo</span>
                                <strong class="vc__meta-value">{{ $veiculo->tipoFrota?->nome ?? '-' }}</strong>
                            </div>
                            <div class="vc__meta-row">
                                <span class="vc__meta-label"><i class="fas fa-car mr-1"></i>Modelo</span>
                                <strong class="vc__meta-value">
                                    {{ $veiculo->marca?->nome ?? '-' }}{{ $veiculo->modelo?->nome ? ' / ' . $veiculo->modelo->nome : '' }}
                                </strong>
                            </div>
                            <div class="vc__meta-row">
                                <span class="vc__meta-label"><i class="fas fa-map-marker-alt mr-1"></i>Local</span>
                                <strong class="vc__meta-value">{{ $localNome }}</strong>
                            </div>
                            <div class="vc__meta-row vc__meta-row--full">
                                <span class="vc__meta-label"><i class="fas fa-clock mr-1"></i>Disponibilidade</span>
                                <strong class="vc__meta-value">
                                    {{ $statusLabelDisponibilidade }} no mês · {{ $percentualDisponibilidade }}%
                                </strong>
                                <small class="text-muted">
                                    Inicial: {{ $dataDisp }}
                                </small>
                            </div>
                        </div>

                        <div class="vc__foot">
                            {{-- <a href="{{ route('veiculo.show', $veiculo->id) }}" class="btn btn-xs btn-info"
                                onclick="event.stopPropagation();">
                                <i class="fas fa-eye mr-1"></i>Ver
                            </a> --}}
                            {{-- <a href="{{ route('veiculo.edit', $veiculo->id) }}" class="btn btn-xs btn-warning"
                                onclick="event.stopPropagation();">
                                <i class="fa fa-edit mr-1"></i>Editar
                            </a>
                          <x-delete-button
                                icone="fa fa-archive"
                                btnClass="btn-info"
                                titulo="Arquivar"
                                tituloMensagem="Arquivar veículo?"
                                mensagem="O veículo deixará de aparecer em novas reservas e operações, mas todo o histórico será preservado. Deseja continuar?"
                                textoConfirmacao="Sim, arquivar veículo"
                                corConfirmacao="#17a2b8"
                                :action="route('veiculo.destroy', $veiculo->id)"
                            /> --}}
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="veiculo-table table-responsive">
                <table class="table table-striped table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Tipo de Frota</th>
                            <th>Grupo de Frota</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Status</th>
                            <th>Local Disponivel Inicial</th>
                            <th style="width: 180px;">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($veiculos as $veiculo)
                            @php
                                $resumoDisponibilidade = $resumoVeiculosIndex[$veiculo->id] ?? [
                                    'percentual' => 0,
                                    'status_mes' => 'off',
                                ];

                                $percentualDisponibilidade = (int) ($resumoDisponibilidade['percentual'] ?? 0);
                                $statusMesDisponibilidade = (string) ($resumoDisponibilidade['status_mes'] ?? 'off');

                                $availKey = match (true) {
                                    $statusMesDisponibilidade === 'ok' && $percentualDisponibilidade >= 100
                                        => 'disponivel',
                                    $statusMesDisponibilidade === 'partial' ||
                                        ($percentualDisponibilidade > 0 && $percentualDisponibilidade < 100)
                                        => 'parcial',
                                    default => 'indisponivel',
                                };

                                $statusLabelDisponibilidade = match ($availKey) {
                                    'disponivel' => 'Disponível',
                                    'parcial' => 'Parcial',
                                    default => 'Indisponível',
                                };

                                $badgeDisponibilidade = match ($availKey) {
                                    'disponivel' => 'bg-green',
                                    'parcial' => 'bg-amber',
                                    default => 'bg-red',
                                };
                            @endphp
                            <tr data-avail="{{ $availKey }}"
                                onclick="window.location='{{ route('veiculo.show', $veiculo->id) }}';"
                                style="cursor: pointer;">
                                <td>{{ $veiculo->nome }}</td>
                                <td>{{ $veiculo->tipoFrota?->nome }}</td>
                                <td>{{ $veiculo->grupoFrota?->nome }}</td>
                                <td>{{ $veiculo->marca?->nome }}</td>
                                <td>{{ $veiculo->modelo?->nome }}</td>
                                <td>
                                    @php $stCor = optional($veiculo->statusVeiculo)->cor ?? 'secondary'; @endphp
                                    <span
                                        class="badge badge-xs bg-{{ $stCor }}">{{ $veiculo->statusVeiculo?->nome ?? '—' }}</span>
                                </td>
                                <td>
                                    <div>
                                        {{ $veiculo->localDisponibilidade?->nome ?? '—' }}
                                    </div>

                                    <div class="small text-muted">
                                        Inicial:
                                        {{ $veiculo->data_hora_disponibilidade
                                            ? \Carbon\Carbon::parse($veiculo->data_hora_disponibilidade)->format('d/m/Y H:i')
                                            : '—' }}
                                    </div>

                                    <span class="badge badge-xs {{ $badgeDisponibilidade }}">
                                        {{ $statusLabelDisponibilidade }} · {{ $percentualDisponibilidade }}%
                                    </span>
                                </td>
                                <td style="width: 180px;" onclick="event.stopPropagation();">
                                    <a href="{{ route('veiculo.show', $veiculo->id) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <a href="{{ route('veiculo.edit', $veiculo->id) }}" class="btn btn-xs btn-warning">
                                        <i class="fa fa-edit"></i> Editar
                                    </a>
                                    {{-- <x-delete-button :action="route('veiculo.destroy', $veiculo->id)" /> --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        (function() {
            const body = document.querySelector('.veiculo-index-body');
            const buttons = document.querySelectorAll('.veiculo-view-toggle [data-view]');
            const availButtons = document.querySelectorAll('.veiculo-disponibilidade-filter [data-avail]');
            if (!body || !buttons.length) return;

            function setView(view) {
                body.setAttribute('data-view', view);
                localStorage.setItem('veiculoIndexView', view);
                buttons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.view === view);
                });
            }

            function setAvailabilityFilter(key) {
                localStorage.setItem('veiculoAvailabilityFilter', key);
                availButtons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.avail === key);
                });

                const targets = body.querySelectorAll('[data-avail]');
                targets.forEach(el => {
                    if (key === 'todos') {
                        el.style.display = '';
                        return;
                    }
                    el.style.display = (el.dataset.avail === key) ? '' : 'none';
                });
            }

            const saved = localStorage.getItem('veiculoIndexView') || 'cards';
            setView(saved);

            buttons.forEach(btn => {
                btn.addEventListener('click', () => setView(btn.dataset.view));
            });

            if (availButtons.length) {
                const savedAvail = localStorage.getItem('veiculoAvailabilityFilter') || 'todos';
                setAvailabilityFilter(savedAvail);
                availButtons.forEach(btn => {
                    btn.addEventListener('click', () => setAvailabilityFilter(btn.dataset.avail));
                });
            }
        })();
    </script>
@stop

@section('css')
    <style>
        .veiculo-filter-sticky {
            position: sticky;
            top: 10px;
            z-index: 1030;
        }

        .veiculo-disponibilidade-filter {
            display: flex;
            gap: 5px;
            margin-left: 12px;
        }

        .veiculo-disponibilidade-filter .btn.active {
            background: #0f172a;
            color: #fff;
            border-color: #0f172a;
        }

        .veiculo-view-toggle {
            display: flex;
            gap: 5px;
            margin-left: 12px;
        }

        .veiculo-view-toggle .btn.active {
            background: #0f172a;
            color: #fff;
            border-color: #0f172a;
        }

        .veiculo-index-body[data-view="table"] .veiculo-cards {
            display: none;
        }

        .veiculo-index-body[data-view="cards"] .veiculo-table {
            display: none;
        }

        .veiculo-index-body[data-view="table"] .veiculo-table {
            max-height: calc(100vh - 360px);
            overflow: auto;
            border-radius: 6px;
        }

        .veiculo-index-body[data-view="table"] .veiculo-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8fafc;
        }

        .veiculo-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 16px;
            padding: 10px 8px 16px;
        }

        .vc {
            position: relative;
            background: #fff;
            border: 1px solid #e2e9f4;
            border-radius: 18px;
            overflow: hidden;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 10px rgba(11, 18, 32, .06);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .vc:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(11, 18, 32, .13);
        }

        .vc__accent {
            height: 3px;
            background: #e2e9f4;
        }

        .vc-status--green .vc__accent {
            background: linear-gradient(90deg, #10b981, #34d399);
        }

        .vc-status--red .vc__accent {
            background: linear-gradient(90deg, #ef4444, #f87171);
        }

        .vc-status--amber .vc__accent {
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
        }

        .vc-status--slate .vc__accent {
            background: linear-gradient(90deg, #64748b, #94a3b8);
        }

        .vc__head {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            padding: 14px 16px 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .vc__avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: #fff;
            font-size: .82rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            letter-spacing: .04em;
        }

        .vc__head-info {
            flex: 1;
            min-width: 0;
        }

        .vc__id {
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .vc__title {
            font-size: .92rem;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .vc__sub {
            font-size: .74rem;
            color: #94a3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 1px;
        }

        .vc__badges {
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: flex-end;
            margin-left: 6px;
        }

        .vc__badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .70rem;
            font-weight: 700;
            padding: 5px 8px;
            border-radius: 999px;
            line-height: 1;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .vc__badge--green {
            background: rgba(16, 185, 129, .12);
            color: #047857;
            border-color: rgba(16, 185, 129, .22);
        }

        .vc__badge--amber {
            background: rgba(245, 158, 11, .14);
            color: #b45309;
            border-color: rgba(245, 158, 11, .28);
        }

        .vc__badge--red {
            background: rgba(239, 68, 68, .12);
            color: #b91c1c;
            border-color: rgba(239, 68, 68, .22);
        }

        .vc__badge--slate {
            background: rgba(100, 116, 139, .12);
            color: #475569;
            border-color: rgba(100, 116, 139, .2);
        }

        .vc__meta {
            padding: 12px 16px 10px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 12px;
        }

        .vc__meta-row {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .vc__meta-row--full {
            grid-column: 1 / -1;
        }

        .vc__meta-label {
            font-size: .70rem;
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .vc__meta-value {
            font-size: .86rem;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .vc__foot {
            margin-top: auto;
            padding: 12px 16px 14px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        @media (max-width: 991.98px) {
            .veiculo-cards {
                grid-template-columns: 1fr;
                padding: 6px 4px 12px;
            }

            .veiculo-index-body[data-view="table"] .veiculo-cards {
                display: grid;
            }

            .veiculo-index-body[data-view="table"] .veiculo-table {
                display: none;
            }
        }
    </style>
@stop
