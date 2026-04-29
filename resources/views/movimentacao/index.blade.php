@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('movimentacao.create')" />
@stop

@section('content')
    @php
        $filtroStatusAtivo = session('movimentacao.filtro.status_movimentacao_id') ?? null;
    @endphp

    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif

    <div class="mv-idx-page">

        {{-- ── Barra de status rápido ── --}}
        <div class="mv-idx-statusbar mb-3">
            <div class="mv-idx-statusbar__label">
                <i class="fas fa-filter"></i> Filtrar por status:
            </div>
            @php
                $filtroStatusAtivo = session('movimentacao.filtro.status_movimentacao_id') ?? null;

                if (is_array($filtroStatusAtivo)) {
                    $filtroStatusAtivo = $filtroStatusAtivo['valor'] ?? null;
                }
            @endphp

            <div class="mv-idx-statusbar__chips">
                <a href="{{ route('movimentacao.index') }}"
                    class="mv-status-chip {{ empty($filtroStatusAtivo) ? 'is-active' : '' }}"
                    onclick="limparFiltroStatus(event, this)">
                    <i class="fas fa-list"></i> Todas
                </a>

                @php
                    $corMap = [
                        'success' => ['bg' => '#d1fae5', 'txt' => '#065f46', 'border' => '#6ee7b7'],
                        'info' => ['bg' => '#dbeafe', 'txt' => '#1e40af', 'border' => '#93c5fd'],
                        'warning' => ['bg' => '#fef3c7', 'txt' => '#92400e', 'border' => '#fcd34d'],
                        'danger' => ['bg' => '#fee2e2', 'txt' => '#991b1b', 'border' => '#fca5a5'],
                        'secondary' => ['bg' => '#f1f5f9', 'txt' => '#475569', 'border' => '#cbd5e1'],
                        'purple' => ['bg' => '#ede9fe', 'txt' => '#5b21b6', 'border' => '#c4b5fd'],
                        'orange' => ['bg' => '#ffedd5', 'txt' => '#9a3412', 'border' => '#fdba74'],
                        'teal' => ['bg' => '#ccfbf1', 'txt' => '#115e59', 'border' => '#5eead4'],
                    ];
                @endphp

                @foreach ($statusList as $st)
                    @php
                        $cor = $st->cor ?? 'secondary';
                        if (!is_string($cor) || !isset($corMap[$cor])) {
                            $cor = 'secondary';
                        }

                        $cm = $corMap[$cor];
                        $isActive = (string) ($filtroStatusAtivo ?? '') === (string) $st->id;

                        $styleAtivo = $isActive
                            ? 'background:' .
                                $cm['bg'] .
                                ';color:' .
                                $cm['txt'] .
                                ';border-color:' .
                                $cm['border'] .
                                ';'
                            : '';
                    @endphp

                    <button type="button" class="mv-status-chip {{ $isActive ? 'is-active' : '' }}"
                        style="{{ $styleAtivo }}" data-status-id="{{ $st->id }}" data-cor-bg="{{ $cm['bg'] }}"
                        data-cor-txt="{{ $cm['txt'] }}" data-cor-border="{{ $cm['border'] }}"
                        onclick="filtrarPorStatus(this)">
                        <span class="mv-status-dot" style="background: {{ $cm['txt'] }}"></span>
                        {{ $st->nome }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- ── Toggle table / cards ── --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center" style="gap:8px">
                <span class="text-muted small">
                    REGISTROS <span class="badge badge-success">{{ $movimentacaos->total() }}</span>
                </span>
            </div>
            <div class="d-flex align-items-center" style="gap:8px">
                <form action="{{ route('movimentacao.setPerPage') }}" method="POST" class="d-flex align-items-center"
                    style="gap:4px">
                    @csrf
                    <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                </form>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active" id="btnViewTable"
                        title="Ver como tabela">
                        <i class="fas fa-list"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btnViewCards" title="Ver como cards">
                        <i class="fas fa-th"></i>
                    </button>
                </div>
                {{ $movimentacaos->links() }}
            </div>
        </div>

        {{-- ════ TABELA ════ --}}
        <div id="viewTable" class="card border-0 shadow-sm">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead style="background:#f8fafd;border-bottom:2px solid #e2e9f4">
                        <tr>
                            <th class="pl-3" style="width:50px">#</th>
                            <th>Tipo / Status</th>
                            <th>Usuário</th>
                            <th>Veículo</th>
                            <th>Origem → Destino</th>
                            <th>Datas</th>
                            <th class="text-center" style="width:140px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movimentacaos as $mov)
                            @php
                                $cor = $mov->statusMovimentacao?->cor ?? 'secondary';
                                $isConcluido =
                                    strtolower($mov->statusMovimentacao?->nome ?? '') === 'concluído' ||
                                    strtolower($mov->statusMovimentacao?->nome ?? '') === 'concluido';
                                $veiculoLabel = null;
                                if ($mov->veiculo) {
                                    $veiculoLabel = $mov->veiculo->nome ?? 'Veículo #' . $mov->veiculo->id;
                                    if (!empty($mov->veiculo->placa_licenca)) {
                                        $veiculoLabel .= ' · ' . $mov->veiculo->placa_licenca;
                                    }
                                }
                            @endphp
                            <tr data-mov-id="{{ $mov->id }}" data-status-cor="{{ $cor }}"
                                data-status-nome="{{ strtolower($mov->statusMovimentacao?->nome ?? '') }}"
                                data-concluir-url="{{ route('movimentacao.concluir', $mov->id) }}">
                                <td class="pl-3">
                                    <span class="badge badge-secondary">#{{ $mov->id }}</span>
                                </td>
                                <td>
                                    <div class="font-weight-bold small">{{ $mov->tipoMovimentacao?->nome ?? '—' }}</div>
                                    <span
                                        class="badge badge-{{ $cor }} mt-1">{{ $mov->statusMovimentacao?->nome ?? '—' }}</span>
                                </td>
                                <td class="small">{{ $mov->usuario?->nome ?? '—' }}</td>
                                <td class="small">
                                    <div class="font-weight-bold">{{ $veiculoLabel ?? '—' }}</div>
                                    @if ($mov->veiculo?->grupoFrota)
                                        <small class="text-muted">{{ $mov->veiculo->grupoFrota->nome }}</small>
                                    @endif
                                </td>
                                <td class="small">
                                    <div>{{ $mov->localOrigem?->nome ?? '—' }}</div>
                                    @if ($mov->localDestino)
                                        <div class="text-muted"><i
                                                class="fas fa-arrow-right fa-xs mr-1"></i>{{ $mov->localDestino->nome }}
                                        </div>
                                    @endif
                                </td>
                                <td class="small">
                                    @if ($mov->data_saida)
                                        <div><span class="text-muted">Saída:</span>
                                            {{ \Carbon\Carbon::parse($mov->data_saida)->format('d/m/Y H:i') }}</div>
                                    @endif
                                    @if ($mov->data_chegada_real)
                                        <div><span class="text-success">Chegada:</span>
                                            {{ \Carbon\Carbon::parse($mov->data_chegada_real)->format('d/m/Y H:i') }}</div>
                                    @elseif($mov->data_chegada_prevista)
                                        <div><span class="text-muted">Prev.:</span>
                                            {{ \Carbon\Carbon::parse($mov->data_chegada_prevista)->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center" style="gap:4px;flex-wrap:wrap">
                                        <a href="{{ route('movimentacao.edit', $mov->id) }}"
                                            class="btn btn-xs btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if (!$isConcluido)
                                            <button type="button" class="btn btn-xs btn-outline-success btn-concluir-mov"
                                                title="Concluir movimentação" data-id="{{ $mov->id }}"
                                                data-url="{{ route('movimentacao.concluir', $mov->id) }}">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        @endif
                                        <x-delete-button :action="route('movimentacao.destroy', $mov->id)" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-route fa-2x mb-2 d-block"></i>
                                    Nenhuma movimentação encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ════ CARDS ════ --}}
        <div id="viewCards" style="display:none">
            @forelse($movimentacaos as $mov)
                @php
                    $cor = $mov->statusMovimentacao?->cor ?? 'secondary';
                    $isConcluido =
                        strtolower($mov->statusMovimentacao?->nome ?? '') === 'concluído' ||
                        strtolower($mov->statusMovimentacao?->nome ?? '') === 'concluido';
                    $veiculoLabel = null;
                    if ($mov->veiculo) {
                        $veiculoLabel = $mov->veiculo->nome ?? 'Veículo #' . $mov->veiculo->id;
                        if (!empty($mov->veiculo->placa_licenca)) {
                            $veiculoLabel .= ' · ' . $mov->veiculo->placa_licenca;
                        }
                    }
                    $corMap2 = [
                        'success' => ['bg' => '#d1fae5', 'txt' => '#065f46', 'accent' => '#10b981'],
                        'info' => ['bg' => '#dbeafe', 'txt' => '#1e40af', 'accent' => '#3b82f6'],
                        'warning' => ['bg' => '#fef3c7', 'txt' => '#92400e', 'accent' => '#f59e0b'],
                        'danger' => ['bg' => '#fee2e2', 'txt' => '#991b1b', 'accent' => '#ef4444'],
                        'secondary' => ['bg' => '#f1f5f9', 'txt' => '#475569', 'accent' => '#94a3b8'],
                        'purple' => ['bg' => '#ede9fe', 'txt' => '#5b21b6', 'accent' => '#8b5cf6'],
                        'orange' => ['bg' => '#ffedd5', 'txt' => '#9a3412', 'accent' => '#f97316'],
                        'teal' => ['bg' => '#ccfbf1', 'txt' => '#115e59', 'accent' => '#14b8a6'],
                    ];
                    $cm2 = $corMap2[$cor] ?? $corMap2['secondary'];
                @endphp
                <div class="mv-card mb-3">
                    <div class="mv-card__accent" style="background:{{ $cm2['accent'] }}"></div>
                    <div class="mv-card__body">
                        <div class="mv-card__top">
                            <div class="d-flex align-items-center" style="gap:8px">
                                <span class="badge badge-secondary">#{{ $mov->id }}</span>
                                <span
                                    class="badge badge-{{ $cor }}">{{ $mov->statusMovimentacao?->nome ?? '—' }}</span>
                            </div>
                            <div class="mv-card__actions">
                                <a href="{{ route('movimentacao.edit', $mov->id) }}" class="btn btn-xs btn-outline-primary"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if (!$isConcluido)
                                    <button type="button" class="btn btn-xs btn-outline-success btn-concluir-mov"
                                        title="Concluir" data-id="{{ $mov->id }}"
                                        data-url="{{ route('movimentacao.concluir', $mov->id) }}">
                                        <i class="fas fa-check-double mr-1"></i>Concluir
                                    </button>
                                @endif
                                <x-delete-button :action="route('movimentacao.destroy', $mov->id)" />
                            </div>
                        </div>

                        <div class="mv-card__title">{{ $mov->tipoMovimentacao?->nome ?? '—' }}</div>

                        <div class="mv-card__grid">
                            <div class="mv-card__field">
                                <span class="mv-card__field-label"><i class="fas fa-car"></i> Veículo</span>
                                <span>{{ $veiculoLabel ?? '—' }}</span>
                            </div>
                            <div class="mv-card__field">
                                <span class="mv-card__field-label"><i class="fas fa-user"></i> Usuário</span>
                                <span>{{ $mov->usuario?->nome ?? '—' }}</span>
                            </div>
                            <div class="mv-card__field">
                                <span class="mv-card__field-label"><i class="fas fa-map-marker-alt"></i> Origem</span>
                                <span>{{ $mov->localOrigem?->nome ?? '—' }}</span>
                            </div>
                            <div class="mv-card__field">
                                <span class="mv-card__field-label"><i class="fas fa-flag-checkered"></i> Destino</span>
                                <span>{{ $mov->localDestino?->nome ?? '—' }}</span>
                            </div>
                            @if ($mov->data_saida)
                                <div class="mv-card__field">
                                    <span class="mv-card__field-label"><i class="fas fa-sign-out-alt"></i> Saída</span>
                                    <span>{{ \Carbon\Carbon::parse($mov->data_saida)->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif
                            @if ($mov->data_chegada_real)
                                <div class="mv-card__field">
                                    <span class="mv-card__field-label" style="color:#10b981"><i
                                            class="fas fa-check-circle"></i> Chegada</span>
                                    <span
                                        style="color:#10b981;font-weight:600">{{ \Carbon\Carbon::parse($mov->data_chegada_real)->format('d/m/Y H:i') }}</span>
                                </div>
                            @elseif($mov->data_chegada_prevista)
                                <div class="mv-card__field">
                                    <span class="mv-card__field-label"><i class="fas fa-clock"></i> Prevista</span>
                                    <span>{{ \Carbon\Carbon::parse($mov->data_chegada_prevista)->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif
                        </div>

                        @if ($mov->reserva_id || $mov->ordem_servico_id)
                            <div class="mv-card__links">
                                @if ($mov->reserva_id)
                                    <span class="badge badge-light border">
                                        <i class="fas fa-calendar-check mr-1"></i>Reserva #{{ $mov->reserva_id }}
                                    </span>
                                @endif
                                @if ($mov->ordem_servico_id)
                                    <span class="badge badge-light border">
                                        <i class="fas fa-tools mr-1"></i>OS #{{ $mov->ordem_servico_id }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="fas fa-route fa-2x mb-2 d-block"></i>
                    Nenhuma movimentação encontrada.
                </div>
            @endforelse
        </div>

    </div>

    {{-- Modal de confirmação de conclusão --}}
    <form id="formConcluir" method="POST" action="">
        @csrf
    </form>
@stop

@section('css')
    <style>
        /* ── Status bar ── */
        .mv-idx-statusbar {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            border: 1px solid #e2e9f4;
            border-radius: 14px;
            padding: 10px 16px;
            flex-wrap: wrap;
        }

        .mv-idx-statusbar__label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #64748b;
            white-space: nowrap;
        }

        .mv-idx-statusbar__chips {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .mv-status-chip {
            appearance: none;
            border: 1.5px solid #e2e9f4;
            background: #f8fafd;
            border-radius: 999px;
            padding: 4px 12px;
            font-size: .75rem;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: all .15s;
        }

        .mv-status-chip:hover {
            border-color: #4338ca;
            color: #4338ca;
            background: #eef2ff;
            text-decoration: none;
        }

        .mv-status-chip.is-active {
            background: #1a2848;
            border-color: #1a2848;
            color: #fff;
        }

        .mv-status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
        }

        /* ── Cards ── */
        .mv-card {
            position: relative;
            background: #fff;
            border: 1px solid #e2e9f4;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(12, 20, 40, .05);
            transition: box-shadow .2s;
        }

        .mv-card:hover {
            box-shadow: 0 4px 16px rgba(12, 20, 40, .1);
        }

        .mv-card__accent {
            height: 4px;
            width: 100%;
        }

        .mv-card__body {
            padding: 14px 16px;
        }

        .mv-card__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            flex-wrap: wrap;
            gap: 6px;
        }

        .mv-card__actions {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        .mv-card__title {
            font-size: .92rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .mv-card__grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 8px 12px;
        }

        .mv-card__field {
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: .78rem;
        }

        .mv-card__field-label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #94a3b8;
        }

        .mv-card__links {
            margin-top: 10px;
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        /* ── Tabela ── */
        #viewTable table thead th {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: none;
            padding: 10px 8px;
        }
    </style>
@stop

@section('js')
    <script>
        (function() {
            'use strict';

            // ── Toggle table/cards ────────────────────────────────────
            const SK_VIEW = 'mvIdxView';
            var savedView = localStorage.getItem(SK_VIEW) || 'table';

            function setView(v) {
                document.getElementById('viewTable').style.display = v === 'table' ? '' : 'none';
                document.getElementById('viewCards').style.display = v === 'cards' ? '' : 'none';
                document.getElementById('btnViewTable').classList.toggle('active', v === 'table');
                document.getElementById('btnViewCards').classList.toggle('active', v === 'cards');
                localStorage.setItem(SK_VIEW, v);
            }

            setView(savedView);
            document.getElementById('btnViewTable').addEventListener('click', function() {
                setView('table');
            });
            document.getElementById('btnViewCards').addEventListener('click', function() {
                setView('cards');
            });

            // ── Filtro por status (session-based via form POST) ────────
            window.filtrarPorStatus = function(btn) {
                var statusId = btn.dataset.statusId;

                // Atualiza visual imediatamente
                document.querySelectorAll('.mv-status-chip').forEach(function(c) {
                    c.classList.remove('is-active');
                });
                btn.classList.add('is-active');

                // Submete via filtro
                var form = document.createElement('form');
                form.method = 'GET';
                form.action = '{{ route('movimentacao.filtro') }}';
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'status_movimentacao_id';
                input.value = statusId;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            };

            window.limparFiltroStatus = function(e, el) {
                // Navegação normal para limpar filtro de status
            };

            // ── Concluir movimentação ─────────────────────────────────
            document.querySelectorAll('.btn-concluir-mov').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var movId = this.dataset.id;
                    var url = this.dataset.url;
                    var rowEl = this;

                    Swal.fire({
                        title: 'Concluir movimentação #' + movId + '?',
                        html: '<p class="text-muted mb-3">Informe a data/hora de chegada real (opcional — padrão: agora).</p>' +
                            '<input type="datetime-local" id="swal-chegada" class="swal2-input" ' +
                            'value="' + new Date(new Date().getTime() - new Date()
                                .getTimezoneOffset() * 60000).toISOString().slice(0, 16) + '">',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-check-double mr-1"></i>Sim, concluir',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6b7280',
                        focusConfirm: false,
                        preConfirm: function() {
                            return {
                                chegada_real: document.getElementById('swal-chegada').value
                            };
                        }
                    }).then(function(result) {
                        if (!result.isConfirmed) return;

                        var chegadaReal = result.value.chegada_real;

                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute(
                                        'content'),
                                },
                                body: JSON.stringify({
                                    chegada_real: chegadaReal
                                })
                            })
                            .then(function(r) {
                                return r.json();
                            })
                            .then(function(data) {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Concluído!',
                                        text: 'Movimentação #' + movId +
                                            ' concluída com sucesso.',
                                        icon: 'success',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(function() {
                                        window.location.reload();
                                    });
                                }
                            })
                            .catch(function() {
                                Swal.fire('Erro',
                                    'Não foi possível concluir a movimentação.', 'error'
                                );
                            });
                    });
                });
            });

        })();
    </script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'movimentacao-index';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.page-title', popover: { title: 'Lista de movimentações', description: 'Aqui você acompanha todas as movimentações de veículos, com status, origem, destino, usuário e datas.' } },
        { element: '.btn-create-resource, a[href*="movimentacao/create"]', popover: { title: 'Nova movimentação', description: 'Use este botão para registrar uma nova saída, transferência, retorno ou envio para manutenção.', side: 'left' } },
        { element: '.mv-idx-statusbar', popover: { title: 'Filtro por status', description: 'Os chips no topo permitem filtrar rapidamente a listagem pelas situações cadastradas para a movimentação.', side: 'bottom' } },
        { element: '#btnViewTable, #btnViewCards', popover: { title: 'Modos de visualização', description: 'Alterne entre a visão em tabela e a visão em cards para analisar os registros da forma mais prática.', side: 'left' } },
        { element: '#viewTable table thead tr', popover: { title: 'Dados exibidos', description: 'A tabela mostra tipo e status, usuário, veículo, origem, destino, datas e as ações disponíveis para cada movimentação.', side: 'bottom' } },
        { element: '.btn-concluir-mov, #viewTable tbody tr:first-child td:last-child', popover: { title: 'Concluir e editar', description: 'Você pode editar o registro, concluir a movimentação informando a chegada real ou excluir quando necessário.', side: 'left' } },
    ];

    function marcarFeito() {
        localStorage.setItem(CACHE_KEY, '1');
        fetch(CONCLUIR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
    }

    function buildTour() {
        var _pulou = false;
        return window.driver.js.driver({
            animate: true, smoothScroll: true, showProgress: true,
            allowClose: true,
            progressText: 'Passo @{{current}} de @{{total}}',
            nextBtnText: 'Próximo →', prevBtnText: '← Anterior', doneBtnText: 'Entendido ✓',
            onDestroyStarted: () => { if (!_pulou) marcarFeito(); },
            onPopoverRender: (popover, { driver }) => {
                if (popover.wrapper.querySelector('.drv-skip-btn')) return;
                var btnTour = document.createElement('button');
                btnTour.className = 'drv-skip-btn drv-skip-tour-btn'; btnTour.type = 'button'; btnTour.textContent = 'Pular tour';
                btnTour.addEventListener('click', function () { _pulou = true; marcarFeito(); driver.destroy(); });
                var btnStep = document.createElement('button');
                btnStep.className = 'drv-skip-btn drv-skip-step-btn'; btnStep.type = 'button'; btnStep.textContent = 'Pular passo';
                btnStep.addEventListener('click', function () { driver.moveNext(); });
                var nav = popover.footer.querySelector('.driver-popover-navigation-btns');
                if (nav) { nav.prepend(btnStep); }
                popover.footer.prepend(btnTour);
            },
            steps: steps,
        });
    }

    window.restartPageTour = function () {
        localStorage.removeItem(CACHE_KEY);
        fetch(RESETAR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
            .then(() => buildTour().drive());
    };

    if (localStorage.getItem(CACHE_KEY) && !forceTour) return;
    fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.feito && !forceTour) { localStorage.setItem(CACHE_KEY, '1'); return; }
            setTimeout(() => buildTour().drive(), 350);
        });
})();
</script>
@stop
