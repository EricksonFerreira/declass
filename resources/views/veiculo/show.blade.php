@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
<div class="container-fluid px-0">

    {{-- ════ HERO ════ --}}
    <div class="module-card mb-4">
        <div class="module-card-header">
            <div class="module-header-left">
                <div class="module-header-icon">
                    <i class="fas fa-car"></i>
                </div>
                <div>
                    <div class="module-header-eyebrow">Frota &bull; Detalhes do Veículo</div>
                    <h1 class="module-header-title">{{ $veiculo->nome }}</h1>
                    <p class="module-header-sub">
                        Placa: <strong style="color:#93c5fd">{{ $veiculo->placa_licenca ?? '—' }}</strong>
                        &bull; {{ optional($veiculo->marca)->nome ?? '—' }} / {{ optional($veiculo->modelo)->nome ?? '—' }}
                    </p>
                </div>
            </div>
            <div class="module-header-right">
                <a href="{{ route('veiculo.edit', $veiculo->id) }}" class="btn-xp btn-xp--sm mr-2">
                    <i class="fas fa-pen mr-1"></i>Editar
                </a>
                <a href="{{ route('veiculo.index') }}" class="btn-xp btn-xp--outline-light btn-xp--sm">
                    <i class="fas fa-arrow-left mr-1"></i>Voltar
                </a>
            </div>
        </div>
    </div>

    {{-- ════ GRID PRINCIPAL ════ --}}
    <div class="row">

        {{-- ── Coluna esquerda (2/3) ── --}}
        <div class="col-lg-8">

            {{-- Identificação --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-id-card"></i>
                        <div>
                            <strong>Identificação</strong>
                            <span>Dados cadastrais e documentais do veículo</span>
                        </div>
                    </div>
                    <div class="veiculo-grid">
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Nome</span>
                            <span class="veiculo-field__value">{{ $veiculo->nome ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Placa / Licença</span>
                            <span class="veiculo-field__value">{{ $veiculo->placa_licenca ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Marca</span>
                            <span class="veiculo-field__value">{{ optional($veiculo->marca)->nome ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Modelo</span>
                            <span class="veiculo-field__value">{{ optional($veiculo->modelo)->nome ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Tipo de Frota</span>
                            <span class="veiculo-field__value">{{ optional($veiculo->tipoFrota)->nome ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Grupo de Frota</span>
                            <span class="veiculo-field__value">{{ optional($veiculo->grupoFrota)->nome ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Ano Fabricação</span>
                            <span class="veiculo-field__value">{{ $veiculo->ano_fabricacao ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Ano Modelo</span>
                            <span class="veiculo-field__value">{{ $veiculo->ano_modelo ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Cor</span>
                            <span class="veiculo-field__value">{{ $veiculo->cor ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Chassi</span>
                            <span class="veiculo-field__value">{{ $veiculo->chassi ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">RENAVAM</span>
                            <span class="veiculo-field__value">{{ $veiculo->renavam ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Nº Motor</span>
                            <span class="veiculo-field__value">{{ $veiculo->numero_motor ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Situação operacional --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-tachometer-alt"></i>
                        <div>
                            <strong>Situação Operacional</strong>
                            <span>Status, quilometragem e datas de manutenção</span>
                        </div>
                    </div>
                    <div class="veiculo-grid">
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Status</span>
                            <span class="veiculo-field__value">
                                @php $st = optional($veiculo->statusVeiculo)->nome ?? null; @endphp
                                @if($st)
                                    <span class="status-badge status-badge--blue">{{ $st }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Disponível</span>
                            <span class="veiculo-field__value">
                                @if($veiculo->disponivel)
                                    <span class="status-badge status-badge--green"><i class="fas fa-check mr-1"></i>Sim</span>
                                @else
                                    <span class="status-badge status-badge--amber"><i class="fas fa-times mr-1"></i>Não</span>
                                @endif
                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Quilometragem Atual</span>
                            <span class="veiculo-field__value">{{ number_format($veiculo->kms ?? 0, 0, ',', '.') }} km</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Limite de KMs</span>
                            <span class="veiculo-field__value">{{ $veiculo->limite_kms ? number_format($veiculo->limite_kms, 0, ',', '.') . ' km' : '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Próxima Revisão (KM)</span>
                            <span class="veiculo-field__value">{{ $veiculo->proxima_revisao_kms ? number_format($veiculo->proxima_revisao_kms, 0, ',', '.') . ' km' : '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Próxima Manutenção</span>
                            <span class="veiculo-field__value">{{ $veiculo->data_proxima_manutencao ? \Carbon\Carbon::parse($veiculo->data_proxima_manutencao)->format('d/m/Y') : '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Próxima Inspeção</span>
                            <span class="veiculo-field__value">{{ $veiculo->data_proxima_inspecao ? \Carbon\Carbon::parse($veiculo->data_proxima_inspecao)->format('d/m/Y') : '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Local de Disponibilidade</span>
                            <span class="veiculo-field__value">{{ optional($veiculo->localDisponibilidade)->nome ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Disponível a partir de</span>
                            <span class="veiculo-field__value">{{ $veiculo->data_hora_disponibilidade ? \Carbon\Carbon::parse($veiculo->data_hora_disponibilidade)->format('d/m/Y H:i') : '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Data de Compra</span>
                            <span class="veiculo-field__value">{{ $veiculo->data_compra ? \Carbon\Carbon::parse($veiculo->data_compra)->format('d/m/Y') : '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Validade Placa</span>
                            <span class="veiculo-field__value">{{ $veiculo->data_placa_licenca ? \Carbon\Carbon::parse($veiculo->data_placa_licenca)->format('d/m/Y') : '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Vendido</span>
                            <span class="veiculo-field__value">
                                @if($veiculo->vendido)
                                    <span class="status-badge status-badge--gray">Vendido em {{ $veiculo->data_vendido ? \Carbon\Carbon::parse($veiculo->data_vendido)->format('d/m/Y') : '—' }}</span>
                                @else
                                    <span class="status-badge status-badge--green">Em frota</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Avarias --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-car-crash"></i>
                        <div>
                            <strong>Avarias Registradas</strong>
                            <span>{{ $veiculo->veiculoDanos->count() }} avaria(s) no histórico deste veículo</span>
                        </div>
                    </div>

                    @if($veiculo->veiculoDanos->isEmpty())
                        <div class="empty-state">
                            <i class="fas fa-shield-alt"></i>
                            <p>Nenhuma avaria registrada para este veículo.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover xp-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Local</th>
                                        <th>Tipo</th>
                                        <th>Observações</th>
                                        <th>Status</th>
                                        <th>Reserva</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($veiculo->veiculoDanos->sortByDesc('id') as $dano)
                                        <tr>
                                            <td><span class="xp-id">#{{ $dano->id }}</span></td>
                                            <td>{{ strtoupper($dano->local_dano ?? '—') }}</td>
                                            <td>{{ $dano->tipo_dano ?? '—' }}</td>
                                            <td class="obs-cell">{{ \Illuminate\Support\Str::limit($dano->observacoes, 80) ?? '—' }}</td>
                                            <td>
                                                @if($dano->correcao_data)
                                                    <span class="status-badge status-badge--green"><i class="fas fa-check mr-1"></i>Corrigido</span>
                                                @else
                                                    <span class="status-badge status-badge--amber"><i class="fas fa-clock mr-1"></i>Pendente</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($dano->reserva_id)
                                                    <a href="{{ route('reserva.show', $dano->reserva_id) }}" class="xp-link">#{{ $dano->reserva_id }}</a>
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

                    <div class="mt-3">
                        <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'todos']) }}" class="btn-xp btn-xp--sm btn-xp--outline">
                            <i class="fas fa-list mr-1"></i>Ver todas as avarias
                        </a>
                    </div>
                </div>
            </div>

            {{-- Ordens de Serviço --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-clipboard-check"></i>
                        <div>
                            <strong>Ordens de Serviço</strong>
                            <span>{{ $veiculo->ordensServico->count() }} OS(s) no histórico</span>
                        </div>
                    </div>

                    @if($veiculo->ordensServico->isEmpty())
                        <div class="empty-state">
                            <i class="fas fa-tools"></i>
                            <p>Nenhuma ordem de serviço registrada para este veículo.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover xp-table">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Status</th>
                                        <th>Abertura</th>
                                        <th>Conclusão</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($veiculo->ordensServico->sortByDesc('id') as $os)
                                        <tr>
                                            <td><span class="xp-id">{{ $os->numero ?? '#' . $os->id }}</span></td>
                                            <td>
                                                @if($os->status === \App\Models\OrdemServico::STATUS_ABERTA)
                                                    <span class="status-badge status-badge--amber">Aberta</span>
                                                @elseif($os->status === \App\Models\OrdemServico::STATUS_EM_ANDAMENTO)
                                                    <span class="status-badge status-badge--blue">Em Andamento</span>
                                                @elseif($os->status === \App\Models\OrdemServico::STATUS_CONCLUIDA)
                                                    <span class="status-badge status-badge--green">Concluída</span>
                                                @else
                                                    <span class="status-badge status-badge--gray">Cancelada</span>
                                                @endif
                                            </td>
                                            <td>{{ $os->created_at?->format('d/m/Y') ?? '—' }}</td>
                                            <td>{{ $os->data_conclusao?->format('d/m/Y') ?? '—' }}</td>
                                            <td>
                                                <a href="{{ route('ordem-servico.show', $os) }}" class="btn-xp btn-xp--sm">
                                                    <i class="fas fa-eye mr-1"></i>Ver
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('ordem-servico.create', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm">
                            <i class="fas fa-plus mr-1"></i>Nova OS para este veículo
                        </a>
                    </div>
                </div>
            </div>

            {{-- MovimentaÃ§Ãµes --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-exchange-alt"></i>
                        <div>
                            <strong>MovimentaÃ§Ãµes</strong>
                            <span>{{ $veiculo->movimentacoes->count() }} registro(s) de movimentaÃ§Ã£o</span>
                        </div>
                    </div>

                    @if($veiculo->movimentacoes->isEmpty())
                        <div class="empty-state">
                            <i class="fas fa-route"></i>
                            <p>Nenhuma movimentaÃ§Ã£o registrada para este veÃ­culo.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover xp-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tipo</th>
                                        <th>Status</th>
                                        <th>Origem</th>
                                        <th>Destino</th>
                                        <th>SaÃ­da</th>
                                        <th>Chegada</th>
                                        <th>Reserva</th>
                                        <th>OS</th>
                                        <th>UsuÃ¡rio</th>
                                        <th>Obs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($veiculo->movimentacoes as $mov)
                                        <tr>
                                            <td><span class="xp-id">#{{ $mov->id }}</span></td>
                                            <td>{{ optional($mov->tipoMovimentacao)->nome ?? 'â€”' }}</td>
                                            <td>{{ optional($mov->statusMovimentacao)->nome ?? 'â€”' }}</td>
                                            <td>{{ optional($mov->localOrigem)->nome ?? ($mov->local_origem_id ?? 'â€”') }}</td>
                                            <td>{{ optional($mov->localDestino)->nome ?? ($mov->local_destino_id ?? 'â€”') }}</td>
                                            <td>{{ $mov->data_saida ? \Carbon\Carbon::parse($mov->data_saida)->format('d/m/Y H:i') : 'â€”' }}</td>
                                            <td>
                                                @if($mov->data_chegada_real)
                                                    {{ \Carbon\Carbon::parse($mov->data_chegada_real)->format('d/m/Y H:i') }}
                                                @elseif($mov->data_chegada_prevista)
                                                    <span class="text-muted">{{ \Carbon\Carbon::parse($mov->data_chegada_prevista)->format('d/m/Y H:i') }}</span>
                                                @else
                                                    â€”
                                                @endif
                                            </td>
                                            <td>
                                                @if($mov->reserva_id)
                                                    <a href="{{ route('reserva.show', $mov->reserva_id) }}" class="xp-link">#{{ $mov->reserva_id }}</a>
                                                @else
                                                    <span class="text-muted">â€”</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($mov->ordem_servico_id)
                                                    <a href="{{ route('ordem-servico.show', $mov->ordem_servico_id) }}" class="xp-link">
                                                        {{ optional($mov->ordemServico)->numero ?? ('#' . $mov->ordem_servico_id) }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">â€”</span>
                                                @endif
                                            </td>
                                            <td>{{ optional($mov->usuario)->name ?? 'â€”' }}</td>
                                            <td class="obs-cell">{{ \Illuminate\Support\Str::limit((string) ($mov->observacoes ?? ''), 60) ?: 'â€”' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- ── Coluna direita (1/3) ── --}}
        <div class="col-lg-4">

            {{-- Status card --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Resumo</strong>
                            <span>Visão rápida do veículo</span>
                        </div>
                    </div>
                    <div class="veiculo-summary">
                        <div class="veiculo-summary__row">
                            <span>Avarias pendentes</span>
                            <strong class="text-warning">{{ $veiculo->veiculoDanos->whereNull('correcao_data')->count() }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Avarias corrigidas</span>
                            <strong class="text-success">{{ $veiculo->veiculoDanos->whereNotNull('correcao_data')->count() }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>OS abertas</span>
                            <strong class="text-info">{{ $veiculo->ordensServico->where('status', \App\Models\OrdemServico::STATUS_ABERTA)->count() }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Reservas totais</span>
                            <strong>{{ $veiculo->reservas->count() }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Multas --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong>Multas</strong>
                            <span>{{ $veiculo->multas->count() }} multa(s) registrada(s)</span>
                        </div>
                    </div>

                    <div class="d-flex" style="gap:8px; flex-wrap: wrap">
                        <a href="{{ route('multa.create', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm">
                            <i class="fas fa-plus mr-1"></i>Cadastrar multa
                        </a>
                        <a href="{{ route('multa.index', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm btn-xp--outline">
                            <i class="fas fa-list mr-1"></i>Ver todas
                        </a>
                    </div>

                    @if($veiculo->multas->isEmpty())
                        <div class="empty-state mt-3">
                            <i class="fas fa-receipt"></i>
                            <p>Nenhuma multa registrada para este veículo.</p>
                        </div>
                    @else
                        <div class="table-responsive mt-3">
                            <table class="table table-hover xp-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Data</th>
                                        <th>Status</th>
                                        <th class="text-right">Valor final</th>
                                        <th>Reserva</th>
                                        <th>Responsável</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($veiculo->multas->take(5) as $multa)
                                        @php
                                            $badgeMulta = match($multa->status_multa) {
                                                'paga' => 'success',
                                                'cancelada' => 'secondary',
                                                'recorrida' => 'info',
                                                'indeferida' => 'danger',
                                                'deferida' => 'success',
                                                'em_analise' => 'warning',
                                                default => 'warning',
                                            };
                                        @endphp
                                        <tr>
                                            <td><span class="xp-id">#{{ $multa->id }}</span></td>
                                            <td>{{ $multa->data_hora_infracao?->format('d/m/Y H:i') ?? '—' }}</td>
                                            <td><span class="badge badge-{{ $badgeMulta }}">{{ $multa->label_status_multa }}</span></td>
                                            <td class="text-right">R$ {{ number_format((float) $multa->valor_final, 2, ',', '.') }}</td>
                                            <td>
                                                @if($multa->reserva_id)
                                                    <a href="{{ route('reserva.show', $multa->reserva_id) }}" class="xp-link">#{{ $multa->reserva_id }}</a>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $multa->responsavelLancamento->nome ?? '—' }}</td>
                                            <td>
                                                <a href="{{ route('multa.show', $multa->id) }}" class="btn-xp btn-xp--sm">
                                                    <i class="fas fa-eye mr-1"></i>Ver
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($veiculo->multas->count() > 5)
                            <p class="text-muted small mb-0">Mostrando as 5 mais recentes.</p>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Últimas reservas --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            <strong>Últimas Reservas</strong>
                            <span>5 reservas mais recentes</span>
                        </div>
                    </div>
                    @forelse($veiculo->reservas->sortByDesc('id')->take(5) as $reserva)
                        <div class="veiculo-reserva-item">
                            <div class="veiculo-reserva-item__id">
                                <a href="{{ route('reserva.show', $reserva->id) }}" class="xp-link">#{{ $reserva->id }}</a>
                            </div>
                            <div class="veiculo-reserva-item__info">
                                <span>{{ optional($reserva->statusReserva)->nome ?? '—' }}</span>
                                <small>{{ $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y') : '—' }} → {{ $reserva->data_fim ? \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y') : '—' }}</small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Nenhuma reserva registrada.</p>
                    @endforelse
                </div>
            </div>

            {{-- Ações rápidas --}}
            <div class="config-card">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-bolt"></i>
                        <div>
                            <strong>Ações rápidas</strong>
                        </div>
                    </div>
                    <div class="d-flex flex-column" style="gap:8px">
                        <a href="{{ route('veiculo.edit', $veiculo->id) }}" class="btn-xp btn-xp--sm w-100 justify-content-center">
                            <i class="fas fa-pen mr-1"></i>Editar veículo
                        </a>
                        <a href="{{ route('multa.create', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm btn-xp--outline w-100 justify-content-center">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Cadastrar multa
                        </a>
                        <a href="{{ route('ordem-servico.create', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm btn-xp--outline w-100 justify-content-center">
                            <i class="fas fa-plus mr-1"></i>Abrir OS
                        </a>
                        <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'pendentes']) }}" class="btn-xp btn-xp--sm btn-xp--outline w-100 justify-content-center">
                            <i class="fas fa-car-crash mr-1"></i>Ver avarias
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --c-ink:    #0c1428;
        --c-navy:   #1a2848;
        --c-indigo: #4338ca;
        --c-green:  #10b981;
        --c-amber:  #f59e0b;
        --c-blue:   #0ea5e9;
        --c-red:    #ef4444;
        --c-slate:  #64748b;
        --c-border: #e2e9f4;
        --c-bg:     #f2f5fb;
    }

    /* ── Hero ── */
    .module-card {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(12,20,40,.10);
    }
    .module-card-header {
        background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%);
        padding: 24px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        position: relative;
        overflow: hidden;
    }
    .module-card-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px);
        background-size: 20px 20px;
        pointer-events: none;
    }
    .module-card-header::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4338ca, #0ea5e9, #10b981);
    }
    .module-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        z-index: 1;
    }
    .module-header-icon {
        width: 52px; height: 52px;
        border-radius: 14px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.18);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; color: #60a5fa;
        flex-shrink: 0; backdrop-filter: blur(8px);
    }
    .module-header-eyebrow {
        font-size: .62rem; font-weight: 700;
        letter-spacing: .18em; text-transform: uppercase;
        color: rgba(148,163,184,.8); display: block; margin-bottom: 3px;
    }
    .module-header-title {
        font-size: 1.45rem; font-weight: 800;
        color: #fff; margin: 0 0 3px; letter-spacing: -.02em;
    }
    .module-header-sub {
        margin: 0; font-size: .82rem; color: rgba(148,163,184,.9);
    }
    .module-header-right { position: relative; z-index: 1; display: flex; gap: 8px; }

    /* ── Config cards & blocks ── */
    .config-card {
        background: #fff;
        border: 1px solid var(--c-border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(12,20,40,.06);
    }
    .config-block {
        padding: 18px 20px;
        border-left: 3px solid var(--c-indigo);
        border-bottom: 1px solid #f0f4fc;
    }
    .config-block:last-child { border-bottom: none; }
    .config-block-label {
        display: flex; align-items: flex-start;
        gap: 10px; margin-bottom: 14px;
        padding-bottom: 12px; border-bottom: 1px dashed #e5eaf3;
    }
    .config-block-label > i {
        width: 30px; height: 30px; border-radius: 9px;
        background: #eef2ff; color: var(--c-indigo);
        display: flex; align-items: center; justify-content: center;
        font-size: .78rem; flex-shrink: 0;
    }
    .config-block-label strong { display: block; color: #0c1428; font-size: .86rem; font-weight: 700; }
    .config-block-label span   { color: #64748b; font-size: .73rem; display: block; margin-top: 1px; }

    /* ── Veiculo grid ── */
    .veiculo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 12px;
    }
    .veiculo-field {
        background: #f8fafc;
        border: 1px solid #e9f0fa;
        border-radius: 10px;
        padding: 10px 12px;
    }
    .veiculo-field__label {
        display: block;
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 4px;
    }
    .veiculo-field__value {
        display: block;
        font-size: .88rem;
        font-weight: 600;
        color: #0c1428;
    }

    /* ── Summary ── */
    .veiculo-summary { display: flex; flex-direction: column; gap: 8px; }
    .veiculo-summary__row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background: #f8fafc;
        border-radius: 9px;
        font-size: .84rem;
    }
    .veiculo-summary__row span { color: #475569; }
    .veiculo-summary__row strong { font-size: 1rem; }

    /* ── Reserva items ── */
    .veiculo-reserva-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px dashed #e5eaf3;
    }
    .veiculo-reserva-item:last-child { border-bottom: none; }
    .veiculo-reserva-item__id { flex-shrink: 0; }
    .veiculo-reserva-item__info { flex: 1; }
    .veiculo-reserva-item__info span { display: block; font-size: .82rem; font-weight: 600; color: #0c1428; }
    .veiculo-reserva-item__info small { color: #64748b; font-size: .72rem; }

    /* ── Buttons ── */
    .btn-xp {
        display: inline-flex; align-items: center; gap: 6px;
        background: linear-gradient(135deg, var(--c-indigo), #6366f1);
        color: #fff; border: none; border-radius: 10px;
        padding: 9px 16px; font-size: .82rem; font-weight: 700;
        cursor: pointer; text-decoration: none;
        transition: all .18s; letter-spacing: -.01em;
        box-shadow: 0 4px 12px rgba(67,56,202,.25);
    }
    .btn-xp:hover { opacity: .88; transform: translateY(-1px); color: #fff; text-decoration: none; }
    .btn-xp--sm { padding: 6px 12px; font-size: .78rem; border-radius: 8px; }
    .btn-xp--outline {
        background: transparent;
        color: var(--c-indigo);
        border: 1.5px solid var(--c-indigo);
        box-shadow: none;
    }
    .btn-xp--outline:hover { background: #eef2ff; color: var(--c-indigo); }
    .btn-xp--outline-light {
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.25);
        color: rgba(255,255,255,.9);
        box-shadow: none;
    }
    .btn-xp--outline-light:hover { background: rgba(255,255,255,.2); color: #fff; }
    .w-100 { width: 100%; justify-content: center; }

    /* ── Status badges ── */
    .status-badge {
        display: inline-flex; align-items: center;
        padding: 3px 10px; border-radius: 999px;
        font-size: .73rem; font-weight: 700; letter-spacing: .02em;
    }
    .status-badge--green  { background: #dcfce7; color: #15803d; }
    .status-badge--amber  { background: #fef3c7; color: #92400e; }
    .status-badge--blue   { background: #dbeafe; color: #1e40af; }
    .status-badge--gray   { background: #f1f5f9; color: #475569; }
    .status-badge--red    { background: #fee2e2; color: #991b1b; }

    /* ── Table ── */
    .xp-table { font-size: .82rem; }
    .xp-table thead th {
        background: #f0f4ff;
        color: #3d4f63;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        border-bottom: 2px solid #dde8ff;
        padding: 10px 12px;
    }
    .xp-table tbody td { padding: 9px 12px; vertical-align: middle; border-color: #f0f4fc; }
    .xp-id { font-weight: 700; color: var(--c-indigo); font-size: .8rem; }
    .xp-link { color: var(--c-indigo); font-weight: 600; text-decoration: none; }
    .xp-link:hover { color: #3730a3; text-decoration: underline; }
    .obs-cell { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* ── Empty state ── */
    .empty-state {
        text-align: center; padding: 28px 16px;
        color: #94a3b8;
    }
    .empty-state i { font-size: 2rem; display: block; margin-bottom: 8px; }
    .empty-state p { margin: 0; font-size: .85rem; }
</style>
@stop
