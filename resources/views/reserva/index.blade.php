@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('reserva.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $reservas->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('reserva.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="reserva-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i> Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i> Tabela
                        </button>
                    </div>
                    <div class="d-flex align-items-end">
                        {{ $reservas->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 reserva-index-body" data-view="cards">
            <div class="reserva-cards">
                @foreach ($reservas as $reserva)
                    @php
                        $totalCalculado = $reserva->valor_total_final;
                        if ($totalCalculado === null) { $totalCalculado = $reserva->valor_total_com_desconto; }
                        if ($totalCalculado === null) { $totalCalculado = $reserva->valor_total_tarifa_base; }
                        if ($totalCalculado === null && $reserva->tarifa_base && $reserva->quantidade_dias) {
                            $totalCalculado = $reserva->tarifa_base * $reserva->quantidade_dias;
                        }
                        $totalCalculado    = (float) ($totalCalculado ?? 0);
                        $pagamentoEfetuado = ($reserva->pagamento_efetuado ?? 'N') === 'S';
                        $diasReserva       = (int) ($reserva->quantidade_dias ?? 0);
                        $statusNome        = strtolower((string) ($reserva->statusReserva?->nome ?? ''));
                        $statusClass       = match ($statusNome) {
                            'confirmada'         => 'rc-status--green',
                            'cancelada'          => 'rc-status--red',
                            'criada', 'pendente' => 'rc-status--amber',
                            default              => 'rc-status--slate',
                        };
                        $nomeCliente  = $reserva->usuario?->nome ?? 'Sem cliente';
                        $partesNome   = preg_split('/\s+/', trim($nomeCliente)) ?: [];
                        $iniciais     = collect($partesNome)->filter()->take(2)
                                            ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                        $iniciais     = $iniciais ?: 'CL';
                        $dataInicio   = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y') : '-';
                        $horaInicio   = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('H:i') : '';
                        $dataFim      = $reserva->data_fim    ? \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y')    : '-';
                        $horaFim      = $reserva->data_fim    ? \Carbon\Carbon::parse($reserva->data_fim)->format('H:i')      : '';
                    @endphp

                    <article class="rc {{ $statusClass }}" onclick="window.location='{{ route('reserva.show', $reserva->id) }}';">

                        {{-- Accent top bar --}}
                        <div class="rc__accent"></div>

                        {{-- Header: avatar + nome + badges --}}
                        <div class="rc__head">
                            <div class="rc__avatar">{{ $iniciais }}</div>
                            <div class="rc__head-info">
                                <div class="rc__id">Reserva #{{ $reserva->id }}</div>
                                <div class="rc__cliente">{{ $nomeCliente }}</div>
                                @if(!empty($reserva->usuario?->email))
                                    <div class="rc__email">{{ $reserva->usuario->email }}</div>
                                @endif
                            </div>
                            <div class="rc__badges">
                                <span class="rc__badge {{ $pagamentoEfetuado ? 'rc__badge--green' : 'rc__badge--amber' }}">
                                    {{ $pagamentoEfetuado ? 'Pago' : 'Pendente' }}
                                </span>
                                <span class="rc__badge rc__badge--status rc__badge--{{ strtolower(str_replace(' ', '-', $statusNome)) }}" title="{{ $reserva->statusReserva?->descricao }}">
                                    {{ $reserva->statusReserva?->nome ?? 'Status' }}
                                </span>
                            </div>
                        </div>

                        {{-- Rota compacta --}}
                        <div class="rc__route">
                            <div class="rc__stop">
                                <div class="rc__stop-dot rc__stop-dot--a"></div>
                                <div>
                                    <div class="rc__stop-label">Retirada</div>
                                    <div class="rc__stop-place">{{ $reserva->estacaoRetirada?->nome ?? '-' }}</div>
                                    <div class="rc__stop-date">{{ $dataInicio }}@if($horaInicio) <span>{{ $horaInicio }}</span>@endif</div>
                                </div>
                            </div>
                            <div class="rc__route-mid">
                                <div class="rc__route-line"></div>
                                <div class="rc__route-pill">{{ $diasReserva }}d</div>
                                <div class="rc__route-line"></div>
                            </div>
                            <div class="rc__stop rc__stop--right">
                                <div class="rc__stop-dot rc__stop-dot--b"></div>
                                <div>
                                    <div class="rc__stop-label">Devolucao</div>
                                    <div class="rc__stop-place">{{ $reserva->estacaoDevolucao?->nome ?? '-' }}</div>
                                    <div class="rc__stop-date">{{ $dataFim }}@if($horaFim) <span>{{ $horaFim }}</span>@endif</div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer: veiculo + valor --}}
                        <div class="rc__foot">
                            <div class="rc__vehicle">
                                <i class="fas fa-car"></i>
                                <span>{{ $reserva->modelos?->nome ?? 'Modelo nao definido' }}</span>
                                @if(!empty($reserva->grupoFrota?->nome))
                                    <span class="rc__group">{{ $reserva->grupoFrota->nome }}</span>
                                @endif
                            </div>
                            <div class="rc__total">
                                <span>R$</span>
                                <strong>{{ number_format($totalCalculado, 2, ',', '.') }}</strong>
                            </div>
                        </div>

                    </article>
                @endforeach
            </div>

            <div class="reserva-table table-responsive">
                <table class="table table-hover table-bordered table-sm">
    <thead class="thead-light">
        <tr>
            <th class="text-center">#</th>
            <th>Cliente</th>
            <th>Origem</th>
            <th>Veiculo</th>
            <th>Retirada</th>
            <th>Devolucao</th>
            <th class="text-right">Total</th>
            <th>Pagamento</th>
            <th>Status</th>
            {{-- <th class="text-center">Ações</th> --}}
        </tr>
    </thead>
    <tbody>
        @foreach ($reservas as $reserva)
            @php
                $totalCalculado = $reserva->valor_total_final;
                if ($totalCalculado === null) {
                    $totalCalculado = $reserva->valor_total_com_desconto;
                }
                if ($totalCalculado === null) {
                    $totalCalculado = $reserva->valor_total_tarifa_base;
                }
                if ($totalCalculado === null && $reserva->tarifa_base && $reserva->quantidade_dias) {
                    $totalCalculado = $reserva->tarifa_base * $reserva->quantidade_dias;
                }
                $totalCalculado = (float) ($totalCalculado ?? 0);
                $pagamentoEfetuado = ($reserva->pagamento_efetuado ?? 'N') === 'S';
            @endphp
            <tr onclick="window.location='{{ route('reserva.show', $reserva->id) }}';" style="cursor: pointer;">
                <td class="text-center">{{ $reserva->id }}</td>
                <td>
                    <strong>{{ $reserva->usuario?->nome ?? 'Sem cliente' }}</strong><br>
                    <small>{{ $reserva->usuario?->email ?? '-' }}</small>
                </td>
                <td>
                    <span class="badge badge-xs {{ ($reserva->origem_cadastro ?? '') === 'site' ? 'badge-info' : 'badge-primary' }}">
                        {{ $reserva->label_origem_cadastro ?? 'Sem origem' }}
                    </span><br>
                    <small>{{ ($reserva->origem_cadastro ?? '') === 'site' ? 'Portal do cliente' : 'Colaborador interno' }}</small>
                </td>
                <td>
                    <strong>{{ $reserva->grupoFrota?->nome ?? '-' }}</strong><br>
                    <small>{{ $reserva->modelos?->nome ?? 'Modelo nao definido' }}</small>
                </td>
                <td>
                    <strong>{{ $reserva->estacaoRetirada?->nome }}</strong><br>
                    <small>{{ Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') }}</small>
                </td>
                <td>
                    <strong>{{ $reserva->estacaoDevolucao?->nome }}</strong><br>
                    <small>{{ Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') }}</small>
                </td>
                <td class="text-right">
                    <strong>BRL {{ number_format($totalCalculado, 2, ',', '.') }}</strong><br>
                    <small>{{ (int) ($reserva->quantidade_dias ?? 0) }} dias</small>
                </td>
                <td>
                    <span class="badge badge-xs {{ $pagamentoEfetuado ? 'badge-success' : 'badge-warning' }}">
                        {{ $pagamentoEfetuado ? 'Pago' : 'Pendente' }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-xs bg-{{ $reserva->statusReserva?->cor }}" style="cursor: help;" data-toggle="tooltip" title="{{ $reserva->statusReserva?->descricao }}">
                        {{ $reserva->statusReserva?->nome }}
                    </span>
                </td>
                {{-- <td class="text-center">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-cog mr-2"></i> Ações
                        </button>
                        <div class="dropdown-menu" style="font-size:12px !important; overflow:scroll">
                            <a class="dropdown-item" href="{{ route('reserva.show', $reserva->id) }}">
                                <i class="fas fa-eye mr-2"></i> Visualizar
                            </a>
                            @if ($reserva->pagamento_efetuado == 'N')
                                <a class="dropdown-item" href="{{ route('reserva.efetuarPagamento', $reserva->id) }}">
                                    <i class="fas fa-credit-card mr-2"></i> Efetuar Pagamento
                                </a>
                            @endif
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-warning" href="{{ route('reserva.edit', $reserva->id) }}">
                                <i class="fas fa-edit mr-2"></i> Editar
                            </a>
                            <x-delete-button :action="route('reserva.destroy', $reserva->id)" />
                        </div>
                    </div>
                </td> --}}
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
        (function () {
            const body = document.querySelector('.reserva-index-body');
            const buttons = document.querySelectorAll('.reserva-view-toggle [data-view]');
            if (!body || !buttons.length) return;

            function setView(view) {
                body.setAttribute('data-view', view);
                localStorage.setItem('reservaIndexView', view);
                buttons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.view === view);
                });
            }

            const saved = localStorage.getItem('reservaIndexView') || 'cards';
            setView(saved);

            buttons.forEach(btn => {
                btn.addEventListener('click', () => setView(btn.dataset.view));
            });
        })();
    </script>
<script src="{{ asset('js/page-tour-inline.js') }}"></script>
<script>
initInlinePageTour({
    tourId: 'reserva-index-v2',
    steps: @json([
    [
        'element' => '.page-title',
        'popover' => [
            'title' => 'Lista de Reservas',
            'description' => 'Aqui você visualiza todas as reservas cadastradas no sistema. Use os filtros e a busca para encontrar reservas específicas.',
        ],
    ],
    [
        'element' => '.reserva-view-toggle',
        'popover' => [
            'title' => 'Alternar visualização',
            'description' => 'Escolha entre a visualização em <b>Cards</b> (mais visual) ou em <b>Tabela</b> (mais compacta). A preferência é salva automaticamente.',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => '.reserva-cards .rc:first-child',
        'popover' => [
            'title' => 'Card de reserva',
            'description' => 'Cada card mostra: cliente, status, estações de retirada e devolução, duração, veículo e valor total. Clique no card para abrir os detalhes.',
            'side' => 'right',
        ],
    ],
    [
        'element' => '.rc__badges',
        'popover' => [
            'title' => 'Status de pagamento e reserva',
            'description' => 'O primeiro badge indica se o pagamento foi efetuado. O segundo mostra o status atual da reserva (Criada, Confirmada, Cancelada…).',
            'side' => 'left',
        ],
    ],
    [
        'element' => '.rc__route',
        'popover' => [
            'title' => 'Rota da reserva',
            'description' => 'Veja a estação de retirada (ponto verde) e devolução (ponto vermelho), com datas e a duração total em dias.',
            'side' => 'top',
        ],
    ],
    [
        'element' => '.btn-create-resource, a[href*=\"reserva/create\"]',
        'popover' => [
            'title' => 'Nova reserva',
            'description' => 'Clique aqui para criar uma nova reserva. Você será guiado por um formulário passo a passo.',
            'side' => 'bottom',
        ],
    ],
    ]),
});
</script>
@stop

@section('css')
<style>
    /* ── toggle view ── */
    .reserva-view-toggle {
        display: flex;
        gap: 5px;
        margin-left: 12px;
    }

    .reserva-view-toggle .btn.active {
        background: #0f172a;
        color: #fff;
        border-color: #0f172a;
    }

    .reserva-index-body[data-view="table"] .reserva-cards { display: none; }
    .reserva-index-body[data-view="cards"] .reserva-table { display: none; }

    /* ── grid ── */
    .reserva-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 16px;
        padding: 10px 8px 16px;
    }

    /* ══════════════════════════════════════
       CARD BASE
    ══════════════════════════════════════ */
    .rc {
        position: relative;
        background: #fff;
        border: 1px solid #e2e9f4;
        border-radius: 18px;
        overflow: hidden;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 0;
        box-shadow: 0 2px 10px rgba(11,18,32,.06);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .rc:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(11,18,32,.13);
    }

    /* ── accent top bar ── */
    .rc__accent {
        height: 3px;
        background: #e2e9f4;
    }

    .rc-status--green  .rc__accent { background: linear-gradient(90deg, #10b981, #34d399); }
    .rc-status--red    .rc__accent { background: linear-gradient(90deg, #ef4444, #f87171); }
    .rc-status--amber  .rc__accent { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .rc-status--slate  .rc__accent { background: linear-gradient(90deg, #64748b, #94a3b8); }

    /* ── head ── */
    .rc__head {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 14px 16px 12px;
        border-bottom: 1px solid #f1f5f9;
    }

    .rc__avatar {
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

    .rc__head-info {
        flex: 1;
        min-width: 0;
    }

    .rc__id {
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 2px;
    }

    .rc__cliente {
        font-size: .92rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rc__email {
        font-size: .74rem;
        color: #94a3b8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 1px;
    }

    .rc__badges {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
        flex-shrink: 0;
    }

    .rc__badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: .66rem;
        font-weight: 800;
        letter-spacing: .04em;
        white-space: nowrap;
        text-transform: uppercase;
    }

    .rc__badge--green  { background: rgba(16,185,129,.12);  color: #047857; border: 1px solid rgba(16,185,129,.22); }
    .rc__badge--amber  { background: rgba(245,158,11,.14);  color: #b45309; border: 1px solid rgba(245,158,11,.28); }
    .rc__badge--red    { background: rgba(239,68,68,.12);   color: #b91c1c; border: 1px solid rgba(239,68,68,.22); }
    .rc__badge--slate  { background: rgba(100,116,139,.12); color: #475569; border: 1px solid rgba(100,116,139,.2); }

    /* status badge color by name */
    .rc__badge--confirmada  { background: rgba(16,185,129,.12);  color: #047857; border: 1px solid rgba(16,185,129,.22); }
    .rc__badge--cancelada   { background: rgba(239,68,68,.12);   color: #b91c1c; border: 1px solid rgba(239,68,68,.22); }
    .rc__badge--criada,
    .rc__badge--pendente    { background: rgba(245,158,11,.14);  color: #b45309; border: 1px solid rgba(245,158,11,.28); }
    .rc__badge--status      { background: rgba(100,116,139,.12); color: #475569; border: 1px solid rgba(100,116,139,.2); }

    /* ── route ── */
    .rc__route {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 8px;
        align-items: center;
        padding: 14px 16px;
        background: linear-gradient(135deg, #f8faff, #f0f4ff);
        border-bottom: 1px solid #eef1fa;
    }

    .rc__stop {
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .rc__stop--right {
        flex-direction: row-reverse;
        text-align: right;
    }

    .rc__stop-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 4px;
    }

    .rc__stop-dot--a { background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.2); }
    .rc__stop-dot--b { background: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.18); }

    .rc__stop-label {
        font-size: .64rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #94a3b8;
        margin-bottom: 2px;
    }

    .rc__stop-place {
        font-size: .82rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.25;
    }

    .rc__stop-date {
        font-size: .76rem;
        font-weight: 600;
        color: #1e40af;
        margin-top: 2px;
    }

    .rc__stop-date span {
        font-weight: 400;
        color: #64748b;
        margin-left: 4px;
    }

    .rc__route-mid {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }

    .rc__route-line {
        width: 1px;
        height: 14px;
        background: linear-gradient(180deg, #c7d7f4, #93c5fd);
    }

    .rc__route-pill {
        background: #1e40af;
        color: #fff;
        font-size: .64rem;
        font-weight: 800;
        padding: 3px 7px;
        border-radius: 999px;
        white-space: nowrap;
    }

    /* ── footer ── */
    .rc__foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 11px 16px;
    }

    .rc__vehicle {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: .8rem;
        color: #475569;
        font-weight: 600;
        min-width: 0;
        overflow: hidden;
    }

    .rc__vehicle i {
        color: #94a3b8;
        font-size: .78rem;
        flex-shrink: 0;
    }

    .rc__vehicle span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rc__group {
        display: inline-flex;
        align-items: center;
        padding: 2px 7px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #64748b;
        font-size: .66rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .rc__total {
        display: flex;
        align-items: baseline;
        gap: 3px;
        flex-shrink: 0;
    }

    .rc__total span {
        font-size: .72rem;
        font-weight: 700;
        color: #64748b;
    }

    .rc__total strong {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -.01em;
    }

    /* ── responsive ── */
    @media (max-width: 768px) {
        .reserva-view-toggle { display: none; }
        .reserva-cards { grid-template-columns: 1fr; padding: 6px 4px 12px; }
        .reserva-index-body[data-view="table"] .reserva-cards  { display: grid; }
        .reserva-index-body[data-view="table"] .reserva-table  { display: none; }
    }
</style>
@stop
