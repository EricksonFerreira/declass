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
                            <span class="veiculo-field__value">{{ $veiculo->modelo?->ano_modelo ?? '—' }}</span>
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
                                @php
                                    $st = optional($veiculo->statusVeiculo)->nome ?? null;
                                    $cor = optional($veiculo->statusVeiculo)->cor ?? 'secondary';
                                @endphp
                                @if($st)
                                    <span class="badge badge-xs bg-{{ $cor }}">{{ $st }}</span>
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

            {{-- ════ TABS OPERACIONAIS ════ --}}
            <div class="vt-shell mb-4">

                {{-- Barra de tabs --}}
                <div class="vt-tabbar">
                    <button class="vt-tab is-active" data-vt-tab="disp">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Disponibilidade</span>
                    </button>
                    <button class="vt-tab" data-vt-tab="avarias">
                        <i class="fas fa-car-crash"></i>
                        <span>Avarias</span>
                        <em class="vt-count">{{ $veiculo->veiculoDanos->count() }}</em>
                    </button>
                    <button class="vt-tab" data-vt-tab="os">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Ordens de Serviço</span>
                        <em class="vt-count">{{ $veiculo->ordensServico->count() }}</em>
                    </button>
                    <button class="vt-tab" data-vt-tab="mov">
                        <i class="fas fa-route"></i>
                        <span>Movimentações</span>
                        <em class="vt-count">{{ $veiculo->movimentacoes->count() }}</em>
                    </button>
                    <button class="vt-tab" data-vt-tab="sinistros">
                        <i class="fas fa-car-crash"></i>
                        <span>Sinistros</span>
                        <em class="vt-count {{ $veiculo->sinistros->whereIn('status',['aberto','em_analise','aguardando_documentos'])->count() ? 'vt-count--alert' : '' }}">{{ $veiculo->sinistros->count() }}</em>
                    </button>
                    <button class="vt-tab" data-vt-tab="multas">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Multas</span>
                        <em class="vt-count {{ $veiculo->multas->whereIn('status_multa',['pendente','em_analise'])->count() ? 'vt-count--alert' : '' }}">{{ $veiculo->multas->count() }}</em>
                    </button>
                </div>

                {{-- ── PAINEL: Disponibilidade ── --}}
                <div class="vt-panel is-active" data-vt-panel="disp">
                    @include('veiculo.components.disponibilidade', [
                        'veiculo'             => $veiculo,
                        'dispMes'             => $dispMes ?? null,
                        'dispAno'             => $dispAno ?? null,
                        'dispEstacaoId'       => $dispEstacaoId ?? 0,
                        'dispEstacoes'        => $dispEstacoes ?? [],
                        'dispDiasNoMes'       => $dispDiasNoMes ?? null,
                        'dispFirstDow'        => $dispFirstDow ?? null,
                        'dispGrade'           => $dispGrade ?? [],
                        'dispStats'           => $dispStats ?? [],
                        'dispReservas'        => $dispReservas ?? collect(),
                        'dispMovimentacoes'   => $dispMovimentacoes ?? collect(),
                        'reservaBloqueioHoras'=> $reservaBloqueioHoras ?? 0,
                    ])
                </div>

                {{-- ── PAINEL: Avarias ── --}}
                <div class="vt-panel" data-vt-panel="avarias">

                    {{-- Filtros avarias --}}
                    <div class="vt-filter-bar" id="filterAvarias">
                        <div class="vt-filter-grid">
                            <div class="vt-filter-field">
                                <label>Status</label>
                                <select id="avf_status" class="vt-select">
                                    <option value="">Todos</option>
                                    <option value="pendente">Pendente</option>
                                    <option value="corrigido">Corrigido</option>
                                </select>
                            </div>
                            <div class="vt-filter-field">
                                <label>Tipo de Dano</label>
                                <input type="text" id="avf_tipo" class="vt-input" placeholder="Ex: amassado, risco…">
                            </div>
                            <div class="vt-filter-field">
                                <label>Local do Dano</label>
                                <input type="text" id="avf_local" class="vt-input" placeholder="Ex: parachoque, porta…">
                            </div>
                            <div class="vt-filter-field">
                                <label>Reserva #</label>
                                <input type="number" id="avf_reserva" class="vt-input" placeholder="ID da reserva">
                            </div>
                            <div class="vt-filter-field vt-filter-field--actions">
                                <button type="button" class="vt-btn-filter" onclick="filtrarAvarias()">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <button type="button" class="vt-btn-clear" onclick="limparFiltroAvarias()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="vt-filter-result" id="avf_result"></div>
                    </div>

                    {{-- Ações --}}
                    <div class="vt-panel-actions">
                        <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'todos']) }}" class="btn-xp btn-xp--sm btn-xp--outline">
                            <i class="fas fa-list mr-1"></i>Ver todas as avarias
                        </a>
                    </div>

                    @if($veiculo->veiculoDanos->isEmpty())
                        <div class="empty-state"><i class="fas fa-shield-alt"></i><p>Nenhuma avaria registrada.</p></div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover xp-table" id="tabelaAvarias">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Local</th>
                                    <th>Tipo</th>
                                    <th>Custo Est.</th>
                                    <th>Observações</th>
                                    <th>Status</th>
                                    <th>Corrigido em</th>
                                    <th>Reserva</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($veiculo->veiculoDanos->sortByDesc('id') as $dano)
                                <tr
                                    data-av-status="{{ $dano->correcao_data ? 'corrigido' : 'pendente' }}"
                                    data-av-tipo="{{ strtolower($dano->tipo_dano ?? '') }}"
                                    data-av-local="{{ strtolower($dano->local_dano ?? '') }}"
                                    data-av-reserva="{{ $dano->reserva_id ?? '' }}"
                                >
                                    <td><span class="xp-id">#{{ $dano->id }}</span></td>
                                    <td>{{ strtoupper($dano->local_dano ?? '—') }}</td>
                                    <td>{{ $dano->tipo_dano ?? '—' }}</td>
                                    <td>{{ $dano->custo_estimado ? 'R$ ' . number_format((float)$dano->custo_estimado, 2, ',', '.') : '—' }}</td>
                                    <td class="obs-cell">{{ \Illuminate\Support\Str::limit($dano->observacoes ?? '—', 70) }}</td>
                                    <td>
                                        @if($dano->correcao_data)
                                            <span class="status-badge status-badge--green"><i class="fas fa-check mr-1"></i>Corrigido</span>
                                        @else
                                            <span class="status-badge status-badge--amber"><i class="fas fa-clock mr-1"></i>Pendente</span>
                                        @endif
                                    </td>
                                    <td>{{ $dano->correcao_data ? \Carbon\Carbon::parse($dano->correcao_data)->format('d/m/Y') : '—' }}</td>
                                    <td>
                                        @if($dano->reserva_id)
                                            <a href="{{ route('reserva.show', $dano->reserva_id) }}" class="xp-link">#{{ $dano->reserva_id }}</a>
                                        @else <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>

                {{-- ── PAINEL: Ordens de Serviço ── --}}
                <div class="vt-panel" data-vt-panel="os">

                    {{-- Filtros OS --}}
                    <div class="vt-filter-bar" id="filterOs">
                        <div class="vt-filter-grid">
                            <div class="vt-filter-field">
                                <label>Status</label>
                                <select id="osf_status" class="vt-select">
                                    <option value="">Todos</option>
                                    <option value="aberta">Aberta</option>
                                    <option value="em_andamento">Em Andamento</option>
                                    <option value="concluida">Concluída</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                            </div>
                            <div class="vt-filter-field">
                                <label>Número / ID</label>
                                <input type="text" id="osf_numero" class="vt-input" placeholder="Ex: OS-001">
                            </div>
                            <div class="vt-filter-field">
                                <label>Abertura de</label>
                                <input type="date" id="osf_data_de" class="vt-input">
                            </div>
                            <div class="vt-filter-field">
                                <label>Abertura até</label>
                                <input type="date" id="osf_data_ate" class="vt-input">
                            </div>
                            <div class="vt-filter-field vt-filter-field--actions">
                                <button type="button" class="vt-btn-filter" onclick="filtrarOs()">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <button type="button" class="vt-btn-clear" onclick="limparFiltroOs()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="vt-filter-result" id="osf_result"></div>
                    </div>

                    {{-- Ações --}}
                    <div class="vt-panel-actions">
                        <a href="{{ route('ordem-servico.create', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm">
                            <i class="fas fa-plus mr-1"></i>Nova OS para este veículo
                        </a>
                    </div>

                    @if($veiculo->ordensServico->isEmpty())
                        <div class="empty-state"><i class="fas fa-tools"></i><p>Nenhuma ordem de serviço registrada.</p></div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover xp-table" id="tabelaOs">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Status</th>
                                    <th>Abertura</th>
                                    <th>Conclusão</th>
                                    <th>Itens</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($veiculo->ordensServico->sortByDesc('id') as $os)
                                @php
                                    $osStatusVal = $os->status ?? '';
                                    $osDataAb    = $os->created_at?->format('Y-m-d') ?? '';
                                    $osNumero    = strtolower($os->numero ?? (string)$os->id);
                                @endphp
                                <tr
                                    data-os-status="{{ $osStatusVal }}"
                                    data-os-numero="{{ $osNumero }}"
                                    data-os-data="{{ $osDataAb }}"
                                >
                                    <td><span class="xp-id">{{ $os->numero ?? '#' . $os->id }}</span></td>
                                    <td>
                                        @if($osStatusVal === \App\Models\OrdemServico::STATUS_ABERTA)
                                            <span class="status-badge status-badge--amber">Aberta</span>
                                        @elseif($osStatusVal === \App\Models\OrdemServico::STATUS_EM_ANDAMENTO)
                                            <span class="status-badge status-badge--blue">Em Andamento</span>
                                        @elseif($osStatusVal === \App\Models\OrdemServico::STATUS_CONCLUIDA)
                                            <span class="status-badge status-badge--green">Concluída</span>
                                        @else
                                            <span class="status-badge status-badge--gray">Cancelada</span>
                                        @endif
                                    </td>
                                    <td>{{ $os->created_at?->format('d/m/Y') ?? '—' }}</td>
                                    <td>{{ $os->data_conclusao?->format('d/m/Y') ?? '—' }}</td>
                                    <td>{{ $os->itens?->count() ?? '—' }}</td>
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
                </div>

                {{-- ── PAINEL: Movimentações ── --}}
                <div class="vt-panel" data-vt-panel="mov">

                    {{-- Filtros Movimentações --}}
                    <div class="vt-filter-bar" id="filterMov">
                        <div class="vt-filter-grid">
                            <div class="vt-filter-field">
                                <label>Tipo</label>
                                <select id="mvf_tipo" class="vt-select">
                                    <option value="">Todos</option>
                                    @foreach($veiculo->movimentacoes->pluck('tipoMovimentacao.nome','tipoMovimentacao.nome')->filter()->unique() as $tn)
                                        <option value="{{ strtolower($tn) }}">{{ $tn }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="vt-filter-field">
                                <label>Status</label>
                                <select id="mvf_status" class="vt-select">
                                    <option value="">Todos</option>
                                    @foreach($veiculo->movimentacoes->pluck('statusMovimentacao.nome','statusMovimentacao.nome')->filter()->unique() as $sn)
                                        <option value="{{ strtolower($sn) }}">{{ $sn }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="vt-filter-field">
                                <label>Origem</label>
                                <input type="text" id="mvf_origem" class="vt-input" placeholder="Nome da estação">
                            </div>
                            <div class="vt-filter-field">
                                <label>Destino</label>
                                <input type="text" id="mvf_destino" class="vt-input" placeholder="Nome da estação">
                            </div>
                            <div class="vt-filter-field">
                                <label>Saída de</label>
                                <input type="date" id="mvf_data_de" class="vt-input">
                            </div>
                            <div class="vt-filter-field">
                                <label>Saída até</label>
                                <input type="date" id="mvf_data_ate" class="vt-input">
                            </div>
                            <div class="vt-filter-field">
                                <label>Reserva #</label>
                                <input type="number" id="mvf_reserva" class="vt-input" placeholder="ID">
                            </div>
                            <div class="vt-filter-field">
                                <label>Usuário</label>
                                <input type="text" id="mvf_usuario" class="vt-input" placeholder="Nome do usuário">
                            </div>
                            <div class="vt-filter-field vt-filter-field--actions">
                                <button type="button" class="vt-btn-filter" onclick="filtrarMov()">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <button type="button" class="vt-btn-clear" onclick="limparFiltroMov()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="vt-filter-result" id="mvf_result"></div>
                    </div>

                    {{-- Ações --}}
                    <div class="vt-panel-actions">
                        <a href="{{ route('movimentacao.create', ['veiculo_id' => $veiculo->id, 'tipo_movimentacao_id' => 10]) }}" class="btn-xp btn-xp--sm">
                            <i class="fas fa-route mr-1"></i>Transferir entre estações
                        </a>
                    </div>

                    @if($veiculo->movimentacoes->isEmpty())
                        <div class="empty-state"><i class="fas fa-route"></i><p>Nenhuma movimentação registrada.</p></div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover xp-table" id="tabelaMov">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Origem</th>
                                    <th>Destino</th>
                                    <th>Saída</th>
                                    <th>KM Saída</th>
                                    <th>Chegada</th>
                                    <th>KM Chegada</th>
                                    <th>Reserva</th>
                                    <th>OS</th>
                                    <th>Usuário</th>
                                    <th>Obs</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($veiculo->movimentacoes as $mov)
                                @php
                                    $mvTipo    = strtolower(optional($mov->tipoMovimentacao)->nome ?? '');
                                    $mvStatus  = strtolower(optional($mov->statusMovimentacao)->nome ?? '');
                                    $mvOrigem  = strtolower(optional($mov->localOrigem)->nome ?? '');
                                    $mvDestino = strtolower(optional($mov->localDestino)->nome ?? '');
                                    $mvUsuario = strtolower(optional($mov->usuario)->nome ?? '');
                                    $mvDataSaida = $mov->data_saida ? \Carbon\Carbon::parse($mov->data_saida)->format('Y-m-d') : '';
                                    $cor = optional($mov->statusMovimentacao)->cor ?? 'secondary';
                                @endphp
                                <tr
                                    data-mv-tipo="{{ $mvTipo }}"
                                    data-mv-status="{{ $mvStatus }}"
                                    data-mv-origem="{{ $mvOrigem }}"
                                    data-mv-destino="{{ $mvDestino }}"
                                    data-mv-reserva="{{ $mov->reserva_id ?? '' }}"
                                    data-mv-usuario="{{ $mvUsuario }}"
                                    data-mv-data="{{ $mvDataSaida }}"
                                >
                                    <td><span class="xp-id">#{{ $mov->id }}</span></td>
                                    <td>{{ optional($mov->tipoMovimentacao)->nome ?? '—' }}</td>
                                    <td><span class="badge badge-{{ $cor }}">{{ optional($mov->statusMovimentacao)->nome ?? '—' }}</span></td>
                                    <td>{{ optional($mov->localOrigem)->nome ?? '—' }}</td>
                                    <td>{{ optional($mov->localDestino)->nome ?? '—' }}</td>
                                    <td>{{ $mov->data_saida ? \Carbon\Carbon::parse($mov->data_saida)->format('d/m/Y H:i') : '—' }}</td>
                                    <td>{{ $mov->km_saida !== null ? number_format((float)$mov->km_saida, 0, ',', '.') : '—' }}</td>
                                    <td>
                                        @if($mov->data_chegada_real)
                                            {{ \Carbon\Carbon::parse($mov->data_chegada_real)->format('d/m/Y H:i') }}
                                        @elseif($mov->data_chegada_prevista)
                                            <span class="text-muted">{{ \Carbon\Carbon::parse($mov->data_chegada_prevista)->format('d/m/Y H:i') }}</span>
                                        @else —
                                        @endif
                                    </td>
                                    <td>{{ $mov->km_chegada !== null ? number_format((float)$mov->km_chegada, 0, ',', '.') : '—' }}</td>
                                    <td>
                                        @if($mov->reserva_id)
                                            <a href="{{ route('reserva.show', $mov->reserva_id) }}" class="xp-link">#{{ $mov->reserva_id }}</a>
                                        @else <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($mov->ordem_servico_id)
                                            <a href="{{ route('ordem-servico.show', $mov->ordem_servico_id) }}" class="xp-link">
                                                {{ optional($mov->ordemServico)->numero ?? ('#'.$mov->ordem_servico_id) }}
                                            </a>
                                        @else <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($mov->usuario)->nome ?? '—' }}</td>
                                    <td class="obs-cell">{{ \Illuminate\Support\Str::limit((string)($mov->observacoes ?? ''), 50) ?: '—' }}</td>
                                    <td>
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
                </div>

                {{-- ── PAINEL: Sinistros ── --}}
                <div class="vt-panel" data-vt-panel="sinistros">

                    <div class="vt-filter-bar">
                        <div class="vt-filter-grid">
                            <div class="vt-filter-field">
                                <label>Status</label>
                                <select id="snf_status" class="vt-select">
                                    <option value="">Todos</option>
                                    @foreach(\App\Models\Sinistro::STATUS as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="vt-filter-field">
                                <label>Tipo</label>
                                <select id="snf_tipo" class="vt-select">
                                    <option value="">Todos</option>
                                    @foreach(\App\Models\Sinistro::TIPOS as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="vt-filter-field">
                                <label>Data ocorrido de</label>
                                <input type="date" id="snf_data_de" class="vt-input">
                            </div>
                            <div class="vt-filter-field">
                                <label>Data ocorrido até</label>
                                <input type="date" id="snf_data_ate" class="vt-input">
                            </div>
                            <div class="vt-filter-field">
                                <label>Seguradora</label>
                                <input type="text" id="snf_seguradora" class="vt-input" placeholder="Nome da seguradora">
                            </div>
                            <div class="vt-filter-field">
                                <label>Nº Sinistro Seguradora</label>
                                <input type="text" id="snf_num_seg" class="vt-input" placeholder="Ex: SIN-2026-001">
                            </div>
                            <div class="vt-filter-field vt-filter-field--actions">
                                <button type="button" class="vt-btn-filter" onclick="filtrarSn()">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <button type="button" class="vt-btn-clear" onclick="limparFiltroSn()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="vt-filter-result" id="snf_result"></div>
                    </div>

                    <div class="vt-panel-actions">
                        <a href="{{ route('sinistro.create', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm">
                            <i class="fas fa-plus mr-1"></i>Registrar sinistro
                        </a>
                        <a href="{{ route('sinistro.index', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm btn-xp--outline">
                            <i class="fas fa-list mr-1"></i>Ver todos
                        </a>
                    </div>

                    @if($veiculo->sinistros->isEmpty())
                        <div class="empty-state"><i class="fas fa-shield-alt"></i><p>Nenhum sinistro registrado.</p></div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover xp-table" id="tabelaSn">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tipo</th>
                                    <th>Data</th>
                                    <th>Local</th>
                                    <th>Seguradora</th>
                                    <th>Nº Seg.</th>
                                    <th>Prejuízo Est.</th>
                                    <th>Resp. Cliente</th>
                                    <th>Status</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($veiculo->sinistros as $sn)
                                @php
                                    $snBadge = match($sn->status) {
                                        'encerrado','indenizado' => 'success',
                                        'aprovado'  => 'primary',
                                        'cancelado' => 'secondary',
                                        'aguardando_documentos' => 'warning',
                                        default     => 'danger',
                                    };
                                @endphp
                                <tr
                                    data-sn-status="{{ $sn->status }}"
                                    data-sn-tipo="{{ $sn->tipo_sinistro }}"
                                    data-sn-seguradora="{{ strtolower($sn->seguradora ?? '') }}"
                                    data-sn-num-seg="{{ strtolower($sn->numero_sinistro_seguradora ?? '') }}"
                                    data-sn-data="{{ $sn->data_hora_ocorrido?->format('Y-m-d') ?? '' }}"
                                >
                                    <td><span class="xp-id">#{{ $sn->id }}</span></td>
                                    <td>{{ $sn->label_tipo }}</td>
                                    <td>{{ $sn->data_hora_ocorrido?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="obs-cell">{{ \Illuminate\Support\Str::limit($sn->local_ocorrido ?? '—', 35) }}</td>
                                    <td>{{ $sn->seguradora ?? '—' }}</td>
                                    <td>{{ $sn->numero_sinistro_seguradora ?? '—' }}</td>
                                    <td>{{ $sn->valor_prejuizo_estimado !== null ? 'R$ '.number_format((float)$sn->valor_prejuizo_estimado,2,',','.') : '—' }}</td>
                                    <td>{{ $sn->valor_responsabilidade_cliente !== null ? 'R$ '.number_format((float)$sn->valor_responsabilidade_cliente,2,',','.') : '—' }}</td>
                                    <td><span class="badge badge-{{ $snBadge }}">{{ $sn->label_status }}</span></td>
                                    <td>
                                        <a href="{{ route('sinistro.show', $sn->id) }}" class="btn-xp btn-xp--sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>

                {{-- ── PAINEL: Multas ── --}}
                <div class="vt-panel" data-vt-panel="multas">

                    <div class="vt-filter-bar">
                        <div class="vt-filter-grid">
                            <div class="vt-filter-field">
                                <label>Status da Multa</label>
                                <select id="mtf_status" class="vt-select">
                                    <option value="">Todos</option>
                                    @foreach(\App\Models\Multa::STATUS_MULTA as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="vt-filter-field">
                                <label>Status Desconto</label>
                                <select id="mtf_desconto" class="vt-select">
                                    <option value="">Todos</option>
                                    @foreach(\App\Models\Multa::STATUS_DESCONTO as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="vt-filter-field">
                                <label>Órgão Autuador</label>
                                <input type="text" id="mtf_orgao" class="vt-input" placeholder="Ex: DETRAN, PRF…">
                            </div>
                            <div class="vt-filter-field">
                                <label>Nº Auto</label>
                                <input type="text" id="mtf_auto" class="vt-input" placeholder="Número do auto">
                            </div>
                            <div class="vt-filter-field">
                                <label>Infração de</label>
                                <input type="date" id="mtf_data_de" class="vt-input">
                            </div>
                            <div class="vt-filter-field">
                                <label>Infração até</label>
                                <input type="date" id="mtf_data_ate" class="vt-input">
                            </div>
                            <div class="vt-filter-field">
                                <label>Valor mín. (R$)</label>
                                <input type="number" id="mtf_valor_min" class="vt-input" placeholder="0,00" step="0.01">
                            </div>
                            <div class="vt-filter-field">
                                <label>Valor máx. (R$)</label>
                                <input type="number" id="mtf_valor_max" class="vt-input" placeholder="9999,99" step="0.01">
                            </div>
                            <div class="vt-filter-field vt-filter-field--actions">
                                <button type="button" class="vt-btn-filter" onclick="filtrarMt()">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <button type="button" class="vt-btn-clear" onclick="limparFiltroMt()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="vt-filter-result" id="mtf_result"></div>
                    </div>

                    <div class="vt-panel-actions">
                        <a href="{{ route('multa.create', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm">
                            <i class="fas fa-plus mr-1"></i>Cadastrar multa
                        </a>
                        <a href="{{ route('multa.index', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm btn-xp--outline">
                            <i class="fas fa-list mr-1"></i>Ver todas
                        </a>
                    </div>

                    @if($veiculo->multas->isEmpty())
                        <div class="empty-state"><i class="fas fa-receipt"></i><p>Nenhuma multa registrada.</p></div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover xp-table" id="tabelaMt">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Data Infração</th>
                                    <th>Órgão</th>
                                    <th>Nº Auto</th>
                                    <th>Local</th>
                                    <th>Vencimento</th>
                                    <th>Pontos</th>
                                    <th class="text-right">Valor Orig.</th>
                                    <th class="text-right">Desconto</th>
                                    <th class="text-right">Valor Final</th>
                                    <th>Status Multa</th>
                                    <th>Status Desc.</th>
                                    <th>Reserva</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($veiculo->multas as $mt)
                                @php
                                    $mtBadge = match($mt->status_multa) {
                                        'paga','deferida' => 'success',
                                        'cancelada'       => 'secondary',
                                        'recorrida'       => 'info',
                                        'indeferida'      => 'danger',
                                        default           => 'warning',
                                    };
                                @endphp
                                <tr
                                    data-mt-status="{{ $mt->status_multa }}"
                                    data-mt-desconto="{{ $mt->status_desconto }}"
                                    data-mt-orgao="{{ strtolower($mt->orgao_autuador ?? '') }}"
                                    data-mt-auto="{{ strtolower($mt->numero_auto ?? '') }}"
                                    data-mt-data="{{ $mt->data_hora_infracao?->format('Y-m-d') ?? '' }}"
                                    data-mt-valor="{{ (float)($mt->valor_final ?? 0) }}"
                                >
                                    <td><span class="xp-id">#{{ $mt->id }}</span></td>
                                    <td>{{ $mt->data_hora_infracao?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td>{{ $mt->orgao_autuador ?? '—' }}</td>
                                    <td>{{ $mt->numero_auto ?? '—' }}</td>
                                    <td class="obs-cell">{{ \Illuminate\Support\Str::limit($mt->local_infracao ?? '—', 30) }}</td>
                                    <td>{{ $mt->data_vencimento?->format('d/m/Y') ?? '—' }}</td>
                                    <td>{{ $mt->pontos ?? '—' }}</td>
                                    <td class="text-right">R$ {{ number_format((float)$mt->valor_original,2,',','.') }}</td>
                                    <td class="text-right">{{ $mt->valor_desconto !== null ? 'R$ '.number_format((float)$mt->valor_desconto,2,',','.') : '—' }}</td>
                                    <td class="text-right font-weight-bold">R$ {{ number_format((float)$mt->valor_final,2,',','.') }}</td>
                                    <td><span class="badge badge-{{ $mtBadge }}">{{ $mt->label_status_multa }}</span></td>
                                    <td><span class="badge badge-light text-dark">{{ $mt->label_status_desconto }}</span></td>
                                    <td>
                                        @if($mt->reserva_id)
                                            <a href="{{ route('reserva.show', $mt->reserva_id) }}" class="xp-link">#{{ $mt->reserva_id }}</a>
                                        @else <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('multa.show', $mt->id) }}" class="btn-xp btn-xp--sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>

            </div>{{-- /vt-shell --}}

        </div>

        {{-- ── Coluna direita (1/3) ── --}}
        <div class="col-lg-4">

            {{-- Ações rápidas (PRIMEIRO — mais acessado) --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-bolt"></i>
                        <div>
                            <strong>Ações rápidas</strong>
                        </div>
                    </div>
                    <div class="veiculo-acoes-grid">
                        <a href="{{ route('veiculo.edit', $veiculo->id) }}" class="veiculo-acao veiculo-acao--primary">
                            <i class="fas fa-pen"></i>
                            <span>Editar veículo</span>
                        </a>
                        <a href="{{ route('movimentacao.create', ['veiculo_id' => $veiculo->id, 'tipo_movimentacao_id' => 10]) }}" class="veiculo-acao">
                            <i class="fas fa-route"></i>
                            <span>Transferir estação</span>
                        </a>
                        <a href="{{ route('sinistro.create', ['veiculo_id' => $veiculo->id]) }}" class="veiculo-acao veiculo-acao--danger">
                            <i class="fas fa-car-crash"></i>
                            <span>Registrar sinistro</span>
                        </a>
                        <a href="{{ route('multa.create', ['veiculo_id' => $veiculo->id]) }}" class="veiculo-acao">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Cadastrar multa</span>
                        </a>
                        <a href="{{ route('ordem-servico.create', ['veiculo_id' => $veiculo->id]) }}" class="veiculo-acao">
                            <i class="fas fa-tools"></i>
                            <span>Abrir OS</span>
                        </a>
                        <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'pendentes']) }}" class="veiculo-acao">
                            <i class="fas fa-shield-alt"></i>
                            <span>Ver avarias</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Resumo --}}
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
                            <span>Sinistros abertos</span>
                            <strong class="text-danger">{{ $veiculo->sinistros->whereIn('status', ['aberto','em_analise','aguardando_documentos'])->count() }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Multas pendentes</span>
                            <strong class="text-warning">{{ $veiculo->multas->whereIn('status_multa', ['pendente','em_analise'])->count() }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Reservas totais</span>
                            <strong>{{ $veiculo->reservas->count() }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sinistros --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-car-crash text-danger"></i>
                        <div>
                            <strong>Sinistros</strong>
                            <span>{{ $veiculo->sinistros->count() }} sinistro(s) registrado(s)</span>
                        </div>
                    </div>

                    <div class="d-flex mb-2" style="gap:8px; flex-wrap: wrap">
                        <a href="{{ route('sinistro.create', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm">
                            <i class="fas fa-plus mr-1"></i>Registrar sinistro
                        </a>
                        <a href="{{ route('sinistro.index', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm btn-xp--outline">
                            <i class="fas fa-list mr-1"></i>Ver todos
                        </a>
                    </div>

                    @if($veiculo->sinistros->isEmpty())
                        <div class="empty-state mt-2">
                            <i class="fas fa-shield-alt"></i>
                            <p>Nenhum sinistro registrado para este veículo.</p>
                        </div>
                    @else
                        @foreach($veiculo->sinistros->take(5) as $sinistro)
                            @php
                                $snBadge = match($sinistro->status) {
                                    'encerrado', 'indenizado' => 'success',
                                    'aprovado'   => 'primary',
                                    'cancelado'  => 'secondary',
                                    'aguardando_documentos' => 'warning',
                                    default => 'danger',
                                };
                            @endphp
                            <div class="veiculo-sinistro-item">
                                <div class="veiculo-sinistro-item__head">
                                    <span class="xp-id">#{{ $sinistro->id }}</span>
                                    <span class="badge badge-{{ $snBadge }} ml-1">{{ $sinistro->label_status }}</span>
                                </div>
                                <div class="veiculo-sinistro-item__body">
                                    <span class="veiculo-sinistro-item__tipo">{{ $sinistro->label_tipo }}</span>
                                    <small class="text-muted">{{ $sinistro->data_hora_ocorrido?->format('d/m/Y') ?? '—' }}</small>
                                </div>
                                <a href="{{ route('sinistro.show', $sinistro->id) }}" class="btn-xp btn-xp--sm mt-1">
                                    <i class="fas fa-eye mr-1"></i>Ver
                                </a>
                            </div>
                        @endforeach
                        @if($veiculo->sinistros->count() > 5)
                            <p class="text-muted small mb-0 mt-1">Mostrando os 5 mais recentes.</p>
                        @endif
                    @endif
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

                    <div class="d-flex mb-2" style="gap:8px; flex-wrap: wrap">
                        <a href="{{ route('multa.create', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm">
                            <i class="fas fa-plus mr-1"></i>Cadastrar multa
                        </a>
                        <a href="{{ route('multa.index', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm btn-xp--outline">
                            <i class="fas fa-list mr-1"></i>Ver todas
                        </a>
                    </div>

                    @if($veiculo->multas->isEmpty())
                        <div class="empty-state mt-2">
                            <i class="fas fa-receipt"></i>
                            <p>Nenhuma multa registrada para este veículo.</p>
                        </div>
                    @else
                        <div class="table-responsive mt-2">
                            <table class="table table-hover xp-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Data</th>
                                        <th>Status</th>
                                        <th class="text-right">Valor</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($veiculo->multas->take(5) as $multa)
                                        @php
                                            $badgeMulta = match($multa->status_multa) {
                                                'paga', 'deferida' => 'success',
                                                'cancelada'  => 'secondary',
                                                'recorrida'  => 'info',
                                                'indeferida' => 'danger',
                                                default      => 'warning',
                                            };
                                        @endphp
                                        <tr>
                                            <td><span class="xp-id">#{{ $multa->id }}</span></td>
                                            <td>{{ $multa->data_hora_infracao?->format('d/m/Y') ?? '—' }}</td>
                                            <td><span class="badge badge-{{ $badgeMulta }}">{{ $multa->label_status_multa }}</span></td>
                                            <td class="text-right">R$ {{ number_format((float) $multa->valor_final, 2, ',', '.') }}</td>
                                            <td>
                                                <a href="{{ route('multa.show', $multa->id) }}" class="btn-xp btn-xp--sm">
                                                    <i class="fas fa-eye"></i>
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

    /* ── Ações rápidas grid ── */
    .veiculo-acoes-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 4px;
    }
    .veiculo-acao {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 12px 8px;
        border-radius: 10px;
        border: 1px solid #e2e9f4;
        background: #f8fafd;
        color: #1a2848;
        font-size: .75rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        transition: background .15s, box-shadow .15s, transform .12s;
        cursor: pointer;
    }
    .veiculo-acao i { font-size: 1.1rem; color: var(--c-indigo); }
    .veiculo-acao:hover {
        background: #eef2fb;
        box-shadow: 0 2px 8px rgba(67,56,202,.10);
        transform: translateY(-1px);
        color: #1a2848;
        text-decoration: none;
    }
    .veiculo-acao--primary {
        background: linear-gradient(135deg, #1a2848, #2e4080);
        border-color: transparent;
        color: #fff;
    }
    .veiculo-acao--primary i { color: #93c5fd; }
    .veiculo-acao--primary:hover { color: #fff; background: linear-gradient(135deg, #0c1428, #1a2848); }
    .veiculo-acao--danger { border-color: #fecaca; background: #fff5f5; }
    .veiculo-acao--danger i { color: #ef4444; }
    .veiculo-acao--danger:hover { background: #fee2e2; }

    /* ── Sinistro items ── */
    .veiculo-sinistro-item {
        padding: .5rem 0;
        border-bottom: 1px dashed #e5eaf3;
    }
    .veiculo-sinistro-item:last-child { border-bottom: none; }
    .veiculo-sinistro-item__head { display: flex; align-items: center; margin-bottom: 2px; }
    .veiculo-sinistro-item__body { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
    .veiculo-sinistro-item__tipo { font-size: .8rem; font-weight: 600; color: #333; }

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

    /* —— Disponibilidade (show veiculo) —— */
    .vd__filters { margin-top: 8px; }
    .vd__filters-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: end;
        margin-bottom: 10px;
    }
    .vd__filters-item { min-width: 160px; }
    .vd__filters-actions { display: flex; gap: 8px; }
    .vd__label {
        display: block;
        font-size: .65rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 4px;
    }
    .vd__summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 10px;
        margin: 10px 0 10px;
    }
    .vd__kpi {
        background: #f8fafc;
        border: 1px solid #e9f0fa;
        border-radius: 12px;
        padding: 10px 12px;
    }
    .vd__kpi-label {
        font-size: .64rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 4px;
    }
    .vd__kpi-value {
        font-size: .9rem;
        font-weight: 800;
        color: #0c1428;
    }
    .vd__note {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        border-radius: 12px;
        padding: 10px 12px;
        font-size: .85rem;
        font-weight: 600;
        margin: 10px 0 10px;
    }
    .vd__legend {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin: 10px 0 10px;
    }
    .vd__legend-btn {
        border: 1px solid #dbe3ee;
        background: #f8fbff;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: .78rem;
        font-weight: 800;
        color: #1f334d;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }
    .vd__legend-btn.is-active {
        border-color: var(--c-indigo);
        box-shadow: 0 6px 16px rgba(67,56,202,.12);
    }
    .vd__weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
        margin: 8px 0 8px;
        font-size: .7rem;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
    }
    .vd__weekdays > div { text-align: center; }

    .vd__calendar {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 8px;
    }
    .vd__day {
        background: #ffffff;
        border: 1px solid #e9f0fa;
        border-radius: 12px;
        min-height: 68px;
        padding: 8px 10px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-decoration: none;
        color: inherit;
        position: relative;
        transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
    }
    .vd__day:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(12,20,40,.08);
        border-color: #c7d6f1;
        text-decoration: none;
        color: inherit;
    }
    .vd__day.is-empty { background: transparent; border: 0; box-shadow: none; }
    .vd__day.is-hidden { display: none; }
    .vd__day.is-today { outline: 2px solid rgba(14,165,233,.35); }
    .vd__num { font-weight: 900; color: #0c1428; }

    .vd__dot {
        width: 22px;
        height: 22px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #fff;
        align-self: flex-start;
    }
    .vd__dot.is-ok { background: #16a34a; }
    .vd__dot.is-off { background: #dc2626; }
    .vd__dot.is-reserved { background: #d97706; }
    .vd__dot.is-pending { background: #64748b; }

    .vd__lists { margin-top: 12px; }
    .vd__details {
        border: 1px solid #e9f0fa;
        border-radius: 14px;
        padding: 10px 12px;
        background: #fff;
        margin-bottom: 10px;
    }
    .vd__details > summary {
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        user-select: none;
    }
</style>
@stop

@section('js')
<script>
    (function () {
        var buttons = Array.from(document.querySelectorAll('[data-vd-filter]'));
        var calendar = document.getElementById('vdCalendar');
        if (!buttons.length || !calendar) return;

        function setFilter(key) {
            buttons.forEach(function (btn) {
                btn.classList.toggle('is-active', btn.dataset.vdFilter === key);
            });

            calendar.querySelectorAll('[data-vd-type]').forEach(function (day) {
                if (key === 'all') {
                    day.classList.remove('is-hidden');
                    return;
                }
                day.classList.toggle('is-hidden', day.dataset.vdType !== key);
            });
        }

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                setFilter(this.dataset.vdFilter);
            });
        });

        setFilter('all');
    }());
</script>
@stop
