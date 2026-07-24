@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[['name' => 'Minhas Reservas']]" />
    <h1 class="m-0">{{ $titulo }}</h1>
@stop

@section('content')
    <div class="toast-wrap" id="toastWrap"></div>

    <div class="portal-tabs mb-3">
        <button type="button" class="btn btn-outline-primary active" data-portal-tab="reservas">
            <i class="fas fa-car mr-1"></i> Reservas
        </button>
        <button type="button" class="btn btn-outline-danger" data-portal-tab="multas">
            <i class="fas fa-exclamation-triangle mr-1"></i> Multas
            <span class="badge badge-light ml-1">{{ $multas->count() }}</span>
        </button>
    </div>

    <div class="portal-panel" data-portal-panel="reservas">
        <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">
                    REGISTROS <span class="badge badge-success">{{ $reservas->total() }}</span>
                </h3>
                <div class="align-bottom d-flex flex-row justify-between align-items-end flex-wrap">
                    <form action="{{ route('reserva.setPerPage') }}" method="POST" class="mr-2 mb-1">
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

            @if(session('warning'))
                <div class="alert alert-warning m-2">{{ session('warning') }}</div>
            @endif

            {{-- â”€â”€ Cards view â”€â”€ --}}
            <div class="reserva-cards">
                @forelse ($reservas as $reserva)
                    @php
                        $totalCalculado = $reserva->valor_total_final;
                        if ($totalCalculado === null) { $totalCalculado = $reserva->valor_total_com_desconto; }
                        if ($totalCalculado === null) { $totalCalculado = $reserva->valor_total_tarifa_base; }
                        if ($totalCalculado === null && $reserva->tarifa_base && $reserva->quantidade_dias) {
                            $totalCalculado = $reserva->tarifa_base * $reserva->quantidade_dias;
                        }
                        $totalCalculado    = (float) ($totalCalculado ?? 0);
                        $pagamentoEfetuado = method_exists($reserva, 'isPagamentoEfetuado')
                            ? $reserva->isPagamentoEfetuado()
                            : in_array($reserva->pagamento_efetuado ?? false, [true, 1, '1', 'S', 's', 'sim', 'SIM'], true);
                        $isMotoristaApp    = ($reserva->origem_tipo ?? 'reserva') === 'motorista_app';
                        $pagamentoPendente = $isMotoristaApp
                            ? (bool) ($reserva->pagamento_pendente ?? false)
                            : ! $pagamentoEfetuado;
                        $diasReserva       = (int) ($reserva->quantidade_dias ?? 0);
                        $statusRaw         = strtolower((string) ($reserva->status_nome ?? $reserva->statusReserva?->nome ?? ''));
                        $statusClass       = $reserva->status_card_class ?? match ($statusRaw) {
                            'confirmada', 'ativa'         => 'rc-status--green',
                            'cancelada'                   => 'rc-status--red',
                            'criada', 'pendente',
                            'pendente de pagamento',
                            'pendente pagamento'          => 'rc-status--amber',
                            'pendente de anÃ¡lise',
                            'pendente analise'            => 'rc-status--slate',
                            default              => 'rc-status--slate',
                        };
                        $statusCor = strtolower(trim((string) ($reserva->statusReserva?->cor ?? 'secondary')));
                        if (!preg_match('/^[a-z0-9_-]+$/', $statusCor)) {
                            $statusCor = 'secondary';
                        }
                        $nomeCliente  = auth()->user()->nome ?? auth()->user()->name ?? 'Cliente';
                        $partesNome   = preg_split('/\s+/', trim($nomeCliente)) ?: [];
                        $iniciais     = collect($partesNome)->filter()->take(2)
                                            ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                        $iniciais     = $iniciais ?: 'CL';
                        $dataInicio   = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y') : '-';
                        $horaInicio   = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('H:i') : '';
                        $dataFim      = $reserva->data_fim    ? \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y') : '-';
                        $horaFim      = $reserva->data_fim    ? \Carbon\Carbon::parse($reserva->data_fim)->format('H:i') : '';
                        $nfse         = $reserva->nfseAtual;
                        $urlDetalhes  = $reserva->url_detalhes ?? ($isMotoristaApp
                            ? route('reserva.minhas.motorista-app.show', $reserva->id)
                            : route('reserva.minhas.show', $reserva->id));
                        $origemLabel  = $reserva->tipo_label ?? ($isMotoristaApp ? 'Motorista APP' : 'Reservas');
                        $codigoReserva = $reserva->codigo_exibicao ?? $reserva->codigo_reserva ?? ('#' . $reserva->id);
                        $statusExibicao = $reserva->status_exibicao ?? ($reserva->statusReserva?->nome ?? 'Status');
                    @endphp

                    <article class="rc {{ $statusClass }} {{ $isMotoristaApp ? 'rc--app' : '' }}" onclick="window.location='{{ $urlDetalhes }}';" role="link" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){window.location='{{ $urlDetalhes }}';}">

                        {{-- Accent top bar --}}
                        <div class="rc__accent"></div>

                        {{-- Header: avatar + nome + badges --}}
                        <div class="rc__head">
                            <div class="rc__head-info">
                                <div class="rc__id" style="font-size:1.1rem;font-weight:800;">{{ $codigoReserva }}</div>
                                <div class="rc__cliente">{{ $nomeCliente }}</div>
                                @if(!empty(auth()->user()->email))
                                    <div class="rc__email">{{ auth()->user()->email }}</div>
                                @endif
                            </div>
                            <div class="rc__badges">
                                @if($isMotoristaApp)
                                    <span class="rc__badge rc__badge--app">
                                        <i class="fas fa-redo-alt mr-1"></i> Motorista APP
                                    </span>
                                    @if($pagamentoPendente)
                                    <span class="rc__badge rc__badge--alert" title="Há uma cobrança recorrente pendente">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Falta pagamento
                                        </span>
                                    @endif
                                @else
                                <span class="rc__badge {{ $pagamentoEfetuado ? 'rc__badge--green' : 'rc__badge--amber' }}" title="{{ $pagamentoEfetuado ? 'Pago' : 'Pendente' }}">
                                    <i class="fas fa-redo-alt mr-1"></i>
                                    Reservas
                                </span>
                                @endif
                                <span class="badge badge-xs bg-{{ $statusCor }} rc__badge--status"
                                title="{{ $reserva->statusReserva?->descricao }}">
                                    {{ $statusExibicao }}
                                </span>
                            </div>
                        </div>

                        @if($isMotoristaApp)
                            <div class="rc__route rc__route--app">
                                <div class="rc__stop">
                                    <div class="rc__stop-dot rc__stop-dot--a"></div>
                                    <div>
                                        <div class="rc__stop-label">Retirada</div>
                                        <div class="rc__stop-place">{{ $reserva->estacaoRetirada?->nome ?? 'Estação não informada' }}</div>
                                        <div class="rc__stop-date">{{ $dataInicio }}@if($horaInicio) <span>{{ $horaInicio }}</span>@endif</div>
                                    </div>
                                </div>
                                <div class="rc__route-mid">
                                    <div class="rc__route-line"></div>
                                    <div class="rc__route-pill {{ $pagamentoPendente ? 'bg-danger' : 'bg-success' }}">{{ $pagamentoPendente ? 'Pgto' : 'OK' }}</div>
                                    <div class="rc__route-line"></div>
                                </div>
                                {{-- <div class="rc__stop rc__stop--right">
                                    <div class="rc__stop-dot rc__stop-dot--b"></div>
                                    <div>
                                        <div class="rc__stop-label">Vigência</div>
                                        <div class="rc__stop-place">{{ $reserva->modelos?->nome ?? 'Plano recorrente' }}</div>
                                        <div class="rc__stop-date">{{ $dataFim }}@if($horaFim) <span>{{ $horaFim }}</span>@endif</div>
                                    </div>
                                </div> --}}
                            </div>
                        @else
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
                                        <div class="rc__stop-label">Devolução</div>
                                        <div class="rc__stop-place">{{ $reserva->estacaoDevolucao?->nome ?? '-' }}</div>
                                        <div class="rc__stop-date">{{ $dataFim }}@if($horaFim) <span>{{ $horaFim }}</span>@endif</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Footer: veiculo + valor + NFS-e --}}
                        <div class="rc__foot">
                            @if($isMotoristaApp)
                                <div class="rc__vehicle">
                                      <div class="rc__stop-label">Vigência</div>
                                        <span class="rc__group">{{ $reserva->modelos?->nome ?? 'Plano recorrente' }}</span>
                                    {{-- <i class="fas fa-redo-alt"></i>
                                    <span>{{ $origemLabel }}</span> --}}
                                    {{-- @if(!empty($reserva->grupoFrota?->nome))
                                        <span class="rc__group">{{ $reserva->grupoFrota->nome }}</span>
                                    @endif --}}
                                </div>
                            @else
                                <div class="rc__vehicle">
                                    <i class="fas fa-car"></i>
                                    <span>{{ $reserva->modelos?->nome ?? 'Modelo não definido' }}</span>
                                    @if(!empty($reserva->grupoFrota?->nome))
                                        <span class="rc__group">{{ $reserva->grupoFrota->nome }}</span>
                                    @endif
                                </div>
                            @endif
                            <div class="rc__foot-right">
                                @if($nfse && !empty($nfse->nfse_id))
                                    <a class="btn btn-xs btn-outline-primary" href="{{ route('nfse.download', ['id' => $nfse->id, 'tipo' => 'xml']) }}"
                                       onclick="event.stopPropagation();" title="Baixar NFS-e">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                @endif
                                <div class="rc__total">
                                    <span>R$</span>
                                    <strong>{{ number_format($totalCalculado, 2, ',', '.') }}</strong>
                                </div>
                                <a href="{{ $urlDetalhes }}" class="btn btn-xs btn-outline-primary" onclick="event.stopPropagation();">
                                    Ver detalhes
                                </a>
                            </div>
                        </div>

                    </article>
                @empty
                    <div class="alert alert-info m-3">Você ainda não possui reservas.</div>
                @endforelse
            </div>

            {{-- â”€â”€ Table view â”€â”€ --}}
            <div class="reserva-table table-responsive">
                <table class="table table-hover table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th>VeÃ­culo / Plano</th>
                            <th>Retirada</th>
                            <th>DevoluÃ§ão</th>
                            <th class="text-right">Total</th>
                            <th>Pagamento</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reservas as $reserva)
                            @php
                                $totalCalculado = $reserva->valor_total_final ?? $reserva->valor_total_com_desconto ?? $reserva->valor_total_tarifa_base ?? 0;
                                $totalCalculado = (float) $totalCalculado;
                                $pagamentoEfetuado = method_exists($reserva, 'isPagamentoEfetuado')
                                    ? $reserva->isPagamentoEfetuado()
                                    : in_array($reserva->pagamento_efetuado ?? false, [true, 1, '1', 'S', 's', 'sim', 'SIM'], true);
                                $isMotoristaApp = ($reserva->origem_tipo ?? 'reserva') === 'motorista_app';
                                $pagamentoPendente = $isMotoristaApp
                                    ? (bool) ($reserva->pagamento_pendente ?? false)
                                    : ! $pagamentoEfetuado;
                                $urlDetalhes = $reserva->url_detalhes ?? ($isMotoristaApp
                                    ? route('reserva.minhas.motorista-app.show', $reserva->id)
                                    : route('reserva.minhas.show', $reserva->id));
                                $statusCor = strtolower(trim((string) ($reserva->statusReserva?->cor ?? 'secondary')));
                                if (!preg_match('/^[a-z0-9_-]+$/', $statusCor)) {
                                    $statusCor = 'secondary';
                                }
                                $statusExibicao = $reserva->status_exibicao ?? ($reserva->statusReserva?->nome ?? '-');
                            @endphp
                            <tr onclick="window.location='{{ $urlDetalhes }}';" style="cursor:pointer" role="link" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){window.location='{{ $urlDetalhes }}';}">
                                <td class="text-center">{{ $reserva->codigo_exibicao ?? $reserva->codigo_reserva ?? ('#' . $reserva->id) }}</td>
                                <td>
                                    @if($isMotoristaApp)
                                        <strong>{{ $reserva->modelos?->nome ?? 'Plano recorrente' }}</strong><br>
                                        <small>{{ $reserva->estacaoRetirada?->nome ?? 'Estação não informada' }}</small>
                                    @else
                                        <strong>{{ $reserva->grupoFrota?->nome ?? '-' }}</strong><br>
                                        <small>{{ $reserva->modelos?->nome ?? 'Modelo não definido' }}</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $reserva->estacaoRetirada?->nome ?? '-' }}</strong><br>
                                    <small>{{ \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $reserva->estacaoDevolucao?->nome ?? '-' }}</strong><br>
                                    <small>{{ \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') }}</small>
                                </td>
                                <td class="text-right">
                                    <strong>R$ {{ number_format($totalCalculado, 2, ',', '.') }}</strong><br>
                                    <small>{{ (int) ($reserva->quantidade_dias ?? 0) }} dias</small>
                                </td>
                                <td>
                                    @if($isMotoristaApp)
                                        <span class="badge badge-xs badge-info" title="{{ $pagamentoPendente ? 'Pagamento pendente' : 'Em dia' }}">Motorista APP</span><br>
                                        <small class="d-inline-flex align-items-center {{ $pagamentoPendente ? 'text-warning' : 'text-success' }}">
                                            <i class="fas {{ $pagamentoPendente ? 'fa-exclamation-triangle' : 'fa-check-circle' }} mr-1"></i>
                                            {{ $pagamentoPendente ? 'Pagamento pendente' : 'Em dia' }}
                                        </small>
                                    @else
                                        <span class="badge badge-xs {{ $pagamentoEfetuado ? 'badge-success' : 'badge-warning' }}" title="{{ $pagamentoEfetuado ? 'Pago' : 'Pendente' }}">
                                            Reservas
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-xs bg-{{ $statusCor }}"
                                          data-toggle="tooltip" title="{{ $reserva->statusReserva?->descricao }}">
                                        {{ $statusExibicao }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>{{-- card-body --}}
        </div>{{-- card --}}
    </div>{{-- portal-panel reservas --}}

    <div class="portal-panel d-none" data-portal-panel="multas">
        <div class="card p-2">
            <div class="card-header px-0 pt-0 pb-1">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title text-sm text-bold">
                        MINHAS MULTAS <span class="badge badge-danger">{{ $multas->count() }}</span>
                    </h3>
                </div>
            </div>

            <div class="card-body px-0 pt-0 pb-1">
                @if($multas->isEmpty())
                    <div class="alert alert-success m-3">Nenhuma multa encontrada no momento.</div>
                @else
                    <div class="multa-cards">
                        @foreach($multas as $multa)
                            @php
                                $valorMulta = (float) ($multa->valor_final ?? $multa->valor_original ?? 0);
                                $vencimento = $multa->data_vencimento ? \Carbon\Carbon::parse($multa->data_vencimento) : null;
                                $diasParaVencer = $vencimento ? now()->startOfDay()->diffInDays($vencimento->copy()->startOfDay(), false) : null;
                                $estaPaga = !empty($multa->pago_em);
                                $badgeClass = 'badge-info';
                                $badgeLabel = 'Em aberto';
                                if ($estaPaga) {
                                    $badgeClass = 'badge-success';
                                    $badgeLabel = 'Paga';
                                } elseif ($vencimento && $vencimento->lt(now()->startOfDay())) {
                                    $badgeClass = 'badge-danger';
                                    $badgeLabel = 'Vencida';
                                } elseif ($vencimento && $diasParaVencer !== null && $diasParaVencer <= 3) {
                                    $badgeClass = 'badge-warning';
                                    $badgeLabel = 'Vencimento prÃ³ximo';
                                }
                            @endphp
                            <article class="multa-card" onclick="window.location='{{ route('reserva.minhas.multa.show', $multa->id) }}';" role="link" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){window.location='{{ route('reserva.minhas.multa.show', $multa->id) }}';}">
                                <div class="multa-card__head">
                                    <div>
                                        <div class="multa-card__eyebrow">Multa #{{ $multa->id }}</div>
                                        <div class="multa-card__title">{{ $multa->descricao ?? 'Multa de trÃ¢nsito' }}</div>
                                    </div>
                                    <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                </div>
                                <div class="multa-card__body">
                                    <div class="multa-card__row">
                                        <span>Reserva</span>
                                        <strong>#{{ $multa->reserva?->id ?? '-' }}</strong>
                                    </div>
                                    <div class="multa-card__row">
                                        <span>VeÃ­culo</span>
                                        <strong>{{ $multa->veiculo?->placa ?? $multa->placa_momento ?? '-' }}</strong>
                                    </div>
                                    <div class="multa-card__row">
                                        <span>Vencimento</span>
                                        <strong>{{ $vencimento?->format('d/m/Y') ?? '-' }}</strong>
                                    </div>
                                    <div class="multa-card__row">
                                        <span>Valor</span>
                                        <strong>R$ {{ number_format($valorMulta, 2, ',', '.') }}</strong>
                                    </div>
                                </div>
                                <div class="multa-card__foot">
                                    <div class="multa-card__meta">
                                        @if($estaPaga)
                                            Pagamento concluÃ­do
                                        @elseif($diasParaVencer !== null && $diasParaVencer < 0)
                                            Vencida hÃ¡ {{ abs($diasParaVencer) }} dia(s)
                                        @elseif($diasParaVencer !== null && $diasParaVencer <= 3)
                                            Vence em breve
                                        @else
                                            Aguardando pagamento
                                        @endif
                                    </div>
                                    <a href="{{ route('reserva.minhas.multa.show', $multa->id) }}" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation();">
                                        Ver detalhes
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

@stop

@section('js')
    <script>
        (function () {
            // â”€â”€ View toggle â”€â”€
            const portalTabs = document.querySelectorAll('[data-portal-tab]');
            const portalPanels = document.querySelectorAll('[data-portal-panel]');
            const savedPortalTab = localStorage.getItem('minhasReservasPortalTab') || 'reservas';

            function activatePortal(tab) {
                portalTabs.forEach((button) => {
                    button.classList.toggle('active', button.dataset.portalTab === tab);
                });
                portalPanels.forEach((panel) => {
                    panel.classList.toggle('d-none', panel.dataset.portalPanel !== tab);
                });
                localStorage.setItem('minhasReservasPortalTab', tab);
            }

            if (portalTabs.length && portalPanels.length) {
                activatePortal(savedPortalTab);
                portalTabs.forEach((button) => button.addEventListener('click', () => activatePortal(button.dataset.portalTab)));
            }

            const body    = document.querySelector('.reserva-index-body');
            const buttons = document.querySelectorAll('.reserva-view-toggle [data-view]');
            if (body && buttons.length) {
                function setView(view) {
                    body.setAttribute('data-view', view);
                    localStorage.setItem('minhasReservasView', view);
                    buttons.forEach(btn => btn.classList.toggle('active', btn.dataset.view === view));
                }
                const saved = localStorage.getItem('minhasReservasView') || 'cards';
                setView(saved);
                buttons.forEach(btn => btn.addEventListener('click', () => setView(btn.dataset.view)));
            }

            // â”€â”€ Toasts â”€â”€
            const toastWrap      = document.getElementById('toastWrap');
            const successMessage = @json(session('success'));
            const errorMessages  = @json($errors->all());

            function showToast(message, type) {
                if (!toastWrap || !message) return;
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.textContent = message;
                toastWrap.appendChild(toast);
                setTimeout(() => toast.remove(), 5000);
            }

            if (successMessage) showToast(successMessage, 'ok');
            if (Array.isArray(errorMessages)) errorMessages.forEach(msg => showToast(msg, 'err'));
        })();
    </script>
@stop

@section('css')
<style>
    .toast-wrap { position: fixed; top: 16px; right: 16px; display: grid; gap: 8px; z-index: 60; }
    .toast { min-width: 260px; max-width: 360px; border-radius: 12px; padding: 12px 14px; font-size: 13px; font-weight: 700; box-shadow: 0 18px 30px rgba(15,23,42,.18); border: 1px solid #e2e8f0; background: #fff; }
    .toast.ok  { border-color: #bbf7d0; background: #f0fdf4; color: #166534; }
    .toast.err { border-color: #fecaca; background: #fef2f2; color: #991b1b; }
    .portal-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .portal-tabs .btn.active { color: #fff; }
    .portal-panel.d-none { display: none !important; }

    /* â”€â”€ toggle view â”€â”€ */
    .reserva-view-toggle { display: flex; gap: 5px; margin-left: 12px; }
    .reserva-view-toggle .btn.active { background: #0f172a; color: #fff; border-color: #0f172a; }

    .reserva-index-body[data-view="table"] .reserva-cards { display: none; }
    .reserva-index-body[data-view="cards"] .reserva-table { display: none; }

    /* â”€â”€ grid â”€â”€ */
    .reserva-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 16px;
        padding: 10px 8px 16px;
    }

    /* â•â• CARD BASE â•â• */
    .rc {
        position: relative;
        background: #fff;
        border: 1px solid #e2e9f4;
        border-radius: 18px;
        overflow: hidden;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        box-shadow: 0 2px 10px rgba(11,18,32,.06);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .rc:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(11,18,32,.13); }

    /* accent bar */
    .rc__accent { height: 3px; background: #e2e9f4; }
    .rc-status--green .rc__accent { background: linear-gradient(90deg, #10b981, #34d399); }
    .rc-status--red   .rc__accent { background: linear-gradient(90deg, #ef4444, #f87171); }
    .rc-status--amber .rc__accent { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .rc-status--slate .rc__accent { background: linear-gradient(90deg, #64748b, #94a3b8); }

    /* head */
    .rc__head { display: flex; align-items: flex-start; gap: 11px; padding: 14px 16px 12px; border-bottom: 1px solid #f1f5f9; }
    .rc__head-info { flex: 1; min-width: 0; }
    .rc__id { font-size: .68rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #94a3b8; margin-bottom: 2px; }
    .rc__cliente { font-size: .92rem; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .rc__email { font-size: .74rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px; }
    .rc__badges { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; flex-shrink: 0; }
    .rc__badge {
        display: inline-flex; align-items: center; padding: 3px 9px;
        border-radius: 999px; font-size: .66rem; font-weight: 800;
        letter-spacing: .04em; white-space: nowrap; text-transform: uppercase;
    }
    .rc__badge--green  { background: rgba(16,185,129,.12);  color: #047857; border: 1px solid rgba(16,185,129,.22); }
    .rc__badge--amber  { background: rgba(245,158,11,.14);  color: #b45309; border: 1px solid rgba(245,158,11,.28); }
    .rc__badge--red    { background: rgba(239,68,68,.12);   color: #b91c1c; border: 1px solid rgba(239,68,68,.22); }
    .rc__badge--slate  { background: rgba(100,116,139,.12); color: #475569; border: 1px solid rgba(100,116,139,.2); }
    .rc__badge--confirmada { background: rgba(16,185,129,.12);  color: #047857; border: 1px solid rgba(16,185,129,.22); }
    .rc__badge--cancelada  { background: rgba(239,68,68,.12);   color: #b91c1c; border: 1px solid rgba(239,68,68,.22); }
    .rc__badge--criada,
    .rc__badge--pendente   { background: rgba(245,158,11,.14);  color: #b45309; border: 1px solid rgba(245,158,11,.28); }
    .rc__badge--app        { background: rgba(59,130,246,.12);   color: #1d4ed8; border: 1px solid rgba(59,130,246,.24); }
    .rc__badge--alert      { background: rgba(245,158,11,.18);   color: #b45309; border: 1px solid rgba(245,158,11,.3); }
    .rc__badge--status     { border: 1px solid rgba(100,116,139,.2); }

    /* route */
    .rc__route {
        display: grid; grid-template-columns: 1fr auto 1fr; gap: 8px;
        align-items: center; padding: 14px 16px;
        background: linear-gradient(135deg, #f8faff, #f0f4ff);
        border-bottom: 1px solid #eef1fa;
    }
    .rc__stop { display: flex; align-items: flex-start; gap: 8px; }
    .rc__stop--right { flex-direction: row-reverse; text-align: right; }
    .rc__stop-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
    .rc__stop-dot--a { background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.2); }
    .rc__stop-dot--b { background: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.18); }
    .rc__stop-label { font-size: .64rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #94a3b8; margin-bottom: 2px; }
    .rc__stop-place { font-size: .82rem; font-weight: 700; color: #0f172a; line-height: 1.25; }
    .rc__stop-date { font-size: .76rem; font-weight: 600; color: #1e40af; margin-top: 2px; }
    .rc__stop-date span { font-weight: 400; color: #64748b; margin-left: 4px; }
    .rc__route-mid { display: flex; flex-direction: column; align-items: center; gap: 4px; }
    .rc__route-line { width: 1px; height: 14px; background: linear-gradient(180deg, #c7d7f4, #93c5fd); }
    .rc__route-pill { background: #1e40af; color: #fff; font-size: .64rem; font-weight: 800; padding: 3px 7px; border-radius: 999px; white-space: nowrap; }

    /* footer */
    .rc__foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 11px 16px; }
    .rc__vehicle { display: flex; align-items: center; gap: 6px; font-size: .8rem; color: #475569; font-weight: 600; min-width: 0; overflow: hidden; }
    .rc__vehicle i { color: #94a3b8; font-size: .78rem; flex-shrink: 0; }
    .rc__vehicle span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .rc__group { display: inline-flex; align-items: center; padding: 2px 7px; border-radius: 999px; background: #f1f5f9; color: #64748b; font-size: .66rem; font-weight: 700; flex-shrink: 0; }
    .rc__foot-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
    .rc__total { display: flex; align-items: baseline; gap: 3px; }
    .rc__total span   { font-size: .72rem; font-weight: 700; color: #64748b; }
    .rc__total strong { font-size: 1rem; font-weight: 800; color: #0f172a; letter-spacing: -.01em; }

    .rc--app .rc__accent { background: linear-gradient(90deg, #3b82f6, #8b5cf6); }
    .rc--app .rc__route,
    .rc--app .rc__app {
        background: linear-gradient(135deg, #f8fbff, #eef4ff);
        border-bottom: 1px solid #e6edf9;
    }
    .rc__app { padding: 14px 16px; display: grid; gap: 10px; }
    .rc__app-top { display: grid; gap: 4px; }
    .rc__app-kicker { font-size: .64rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #64748b; }
    .rc__app-plan { font-size: .92rem; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .rc__app-sub { display: flex; flex-wrap: wrap; gap: 10px; font-size: .76rem; font-weight: 700; color: #475569; }
    .rc__app-sub i { color: #94a3b8; margin-right: 4px; }
    .rc__app-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
    .rc__app-field {
        display: grid;
        gap: 3px;
        padding: 9px 10px;
        border: 1px solid #dbe7f7;
        border-radius: 12px;
        background: rgba(255,255,255,.75);
    }
    .rc__app-field span { font-size: .63rem; text-transform: uppercase; letter-spacing: .08em; font-weight: 800; color: #64748b; }
    .rc__app-field strong { font-size: .84rem; font-weight: 800; color: #0f172a; }
    .rc__app-field small { font-size: .72rem; font-weight: 600; color: #64748b; margin-left: 4px; }
    .rc__app-field--wide { grid-column: 1 / -1; }
    .rc__app-field .text-warning { color: #b45309 !important; }
    .rc__app-field .text-success { color: #047857 !important; }

    .multa-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 14px;
        padding: 10px 8px 16px;
    }
    .multa-card {
        background: #fff;
        border: 1px solid #e2e9f4;
        border-radius: 16px;
        padding: 14px;
        box-shadow: 0 2px 10px rgba(11,18,32,.06);
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .multa-card__head { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
    .multa-card__eyebrow { font-size: .68rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #94a3b8; }
    .multa-card__title { font-size: .95rem; font-weight: 800; color: #0f172a; margin-top: 3px; }
    .multa-card__body { display: grid; gap: 8px; }
    .multa-card__row { display: flex; justify-content: space-between; gap: 12px; font-size: .82rem; }
    .multa-card__row span { color: #64748b; font-weight: 700; }
    .multa-card__row strong { color: #0f172a; font-weight: 800; text-align: right; }
    .multa-card__foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding-top: 4px; border-top: 1px dashed #e2e8f0; }
    .multa-card__meta { font-size: .72rem; color: #64748b; font-weight: 700; }

    /* responsive */
    @media (max-width: 768px) {
        .reserva-view-toggle { display: none; }
        .reserva-cards { grid-template-columns: 1fr; padding: 6px 4px 12px; }
        .reserva-index-body[data-view="table"] .reserva-cards { display: grid; }
        .reserva-index-body[data-view="table"] .reserva-table { display: none; }
        .multa-cards { grid-template-columns: 1fr; }
    }
</style>
@stop
