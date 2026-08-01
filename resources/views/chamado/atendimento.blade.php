@extends('adminlte::page')
@section('title', 'Atendimento chamado')
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop
@section('content')
    <style>
        .support-hero {
            border-radius: 24px;
            padding: 24px;
            background: linear-gradient(135deg, #071633 0%, #0b2e78 52%, #0a57d6 100%);
            color: #fff;
            box-shadow: 0 18px 40px rgba(8, 20, 58, .16);
        }
        .support-hero h1 { font-size: 2rem; font-weight: 800; letter-spacing: -.03em; margin-bottom: .5rem; }
        .support-hero p { color: rgba(255,255,255,.85); margin: 0; font-size: 1.02rem; }
        .metric-box {
            background: rgba(255,255,255,.96);
            color: #0f172a;
            border-radius: 18px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            min-height: 90px;
            box-shadow: 0 10px 25px rgba(3, 8, 20, .12);
            text-decoration: none;
        }
        .metric-box:hover { text-decoration: none; transform: translateY(-1px); }
        .metric-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #2f6ef7 0%, #3a87ff 100%);
            color: #fff;
            font-size: 1.2rem;
            flex: 0 0 auto;
        }
        .metric-label { color: #64748b; font-size: .8rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .metric-value { font-size: 1.9rem; line-height: 1; font-weight: 800; letter-spacing: -.04em; margin-top: 2px; }
        .metric-sub { color: #2563eb; font-size: .92rem; margin-top: .25rem; }
        .section-card {
            border: 1px solid #e7edf6;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
            background: #fff;
        }
        .section-card .card-body { padding: 18px; }
        .ticket-table thead th {
            border-top: none;
            color: #334155;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            background: #f8fbff;
        }
        .ticket-table td, .ticket-table th { vertical-align: middle; }
        .ticket-row-card {
            border: 1px solid #e7edf6;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .05);
            overflow: hidden;
            margin-bottom: 12px;
        }
        .ticket-row-card .ticket-head { padding: 18px; }
        .ticket-status { display: inline-flex; align-items: center; justify-content: center; min-width: 132px; padding: .45rem .85rem; border-radius: 999px; font-weight: 700; font-size: .82rem; }
        .ticket-title { font-size: 1.08rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
        .ticket-sub { color: #64748b; font-size: .88rem; margin-top: .2rem; }
        .ticket-meta { color: #475569; font-size: .9rem; display: inline-flex; align-items: center; gap: .4rem; white-space: nowrap; }
        .ticket-toggle { border-radius: 12px; border: 1px solid #bfdbfe; color: #1d4ed8; background: #fff; padding: .55rem 1rem; min-width: 156px; text-align: center; font-weight: 700; }
        .ticket-details { border-top: 1px solid #edf2f7; background: #fafcff; padding: 18px; }
        .info-card { background: #fff; border: 1px solid #e7edf6; border-radius: 16px; padding: 16px; box-shadow: 0 8px 18px rgba(15, 23, 42, .04); height: 100%; }
        .info-card h5 { font-size: .96rem; font-weight: 800; margin-bottom: 12px; color: #0f172a; }
        .timeline.timeline-inverse:before { left: 20px; }
        .timeline > div > .timeline-item { margin-left: 52px; border-radius: 14px; border: 1px solid #eef2f7; box-shadow: 0 6px 14px rgba(15, 23, 42, .04); background: #fff; }
        .timeline > div > .fas, .timeline > div > .far { left: 10px; width: 22px; height: 22px; line-height: 22px; font-size: 11px; }
        .timeline-header { font-weight: 700; }
        .overview-list .list-group-item { border: 1px solid #e7edf6; border-radius: 14px !important; margin-bottom: 10px; }
        .overview-list .list-group-item.active { background: #eff6ff; border-color: #93c5fd; color: #0f172a; }
        .ticket-scroll { max-height: 76vh; overflow-y: auto; padding-right: 6px; }
    </style>

    @php
        $isVisaoGeral = $modo === 'visao-geral';
        $isFilaTotal = $modo === 'fila-total';
        $isAguardando = $modo === 'aguardando-analise';
        $isEmAtendimento = $modo === 'em-atendimento';
        $isConcluidos = $modo === 'concluidos';
        $badge = match ($modo) {
            'aguardando-analise' => ['Aguardando análise', 'warning', 'fas fa-hourglass-half'],
            'em-atendimento' => ['Em atendimento', 'info', 'fas fa-tools'],
            'concluidos' => ['Concluídos', 'success', 'fas fa-check-circle'],
            'fila-total' => ['Fila total', 'primary', 'fas fa-headset'],
            default => ['Atendimento chamado', 'primary', 'fas fa-headset'],
        };
        $tituloTela = match ($modo) {
            'aguardando-analise' => 'Chamados aguardando análise',
            'em-atendimento' => 'Chamados em atendimento',
            'concluidos' => 'Chamados concluídos',
            'fila-total' => 'Fila total de chamados',
            default => 'Atendimento chamado',
        };
        $descricaoTela = match ($modo) {
            'aguardando-analise' => 'Fila de chamados que aguardam análise.',
            'em-atendimento' => 'Visualize e gerencie os chamados que estão atualmente em atendimento pela equipe.',
            'concluidos' => 'Centralize o histórico dos atendimentos finalizados.',
            'fila-total' => 'Visualize e gerencie todos os chamados registrados no sistema.',
            default => 'Acesse as filas de atendimento conforme o status para gerenciar seus chamados de forma eficiente.',
        };
        $cardTotal = $total;
        $cardAguardando = $aguardandoAnalise;
        $cardEmAtendimento = $emAtendimento;
        $cardConcluidos = $concluidos;
    @endphp

    <div class="support-hero">
        <div class="row align-items-start">
            <div class="col-lg-6">
                <div class="text-primary mb-3" style="font-weight:700;">
                    Home <i class="fas fa-chevron-right mx-2" style="font-size:.72rem;color:#dbeafe"></i>
                    Atendimento chamado
                    @if($isAguardando) <i class="fas fa-chevron-right mx-2" style="font-size:.72rem;color:#dbeafe"></i> Aguardando análise @endif
                    @if($isEmAtendimento) <i class="fas fa-chevron-right mx-2" style="font-size:.72rem;color:#dbeafe"></i> Em atendimento @endif
                    @if($isConcluidos) <i class="fas fa-chevron-right mx-2" style="font-size:.72rem;color:#dbeafe"></i> Concluídos @endif
                    @if($isFilaTotal) <i class="fas fa-chevron-right mx-2" style="font-size:.72rem;color:#dbeafe"></i> Fila total @endif
                </div>
                <h1>{{ $tituloTela }}</h1>
                <p>{{ $descricaoTela }}</p>
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0">
                @if($isVisaoGeral)
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('chamado.atendimento.total') }}" class="metric-box">
                                <div class="metric-icon"><i class="fas fa-headset"></i></div>
                                <div>
                                    <div class="metric-label">Total</div>
                                    <div class="metric-value">{{ $cardTotal }}</div>
                                    <div class="metric-sub">Clique para abrir</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('chamado.atendimento.aguardando') }}" class="metric-box">
                                <div class="metric-icon" style="background: linear-gradient(135deg,#f59e0b 0%,#fbbf24 100%);"><i class="fas fa-hourglass-half"></i></div>
                                <div>
                                    <div class="metric-label">Aguardando análise</div>
                                    <div class="metric-value">{{ $cardAguardando }}</div>
                                    <div class="metric-sub">Clique para abrir</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('chamado.atendimento.em') }}" class="metric-box">
                                <div class="metric-icon" style="background: linear-gradient(135deg,#0ea5b7 0%,#22d3ee 100%);"><i class="fas fa-tools"></i></div>
                                <div>
                                    <div class="metric-label">Em atendimento</div>
                                    <div class="metric-value">{{ $cardEmAtendimento }}</div>
                                    <div class="metric-sub">Clique para abrir</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('chamado.atendimento.concluidos') }}" class="metric-box">
                                <div class="metric-icon" style="background: linear-gradient(135deg,#22c55e 0%,#4ade80 100%);"><i class="fas fa-check-circle"></i></div>
                                <div>
                                    <div class="metric-label">Concluídos</div>
                                    <div class="metric-value">{{ $cardConcluidos }}</div>
                                    <div class="metric-sub">Clique para abrir</div>
                                </div>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="metric-box" style="min-height:120px;">
                        <div class="metric-icon" style="background: linear-gradient(135deg, {{ $badge[1] === 'warning' ? '#f59e0b' : ($badge[1] === 'info' ? '#0ea5b7' : ($badge[1] === 'success' ? '#22c55e' : '#2f6ef7')) }} 0%, {{ $badge[1] === 'warning' ? '#fbbf24' : ($badge[1] === 'info' ? '#22d3ee' : ($badge[1] === 'success' ? '#4ade80' : '#3a87ff')) }} 100%);">
                            <i class="{{ $badge[2] }}"></i>
                        </div>
                        <div>
                            <div class="metric-label">{{ $badge[0] }}</div>
                            <div class="metric-value">{{ $isFilaTotal ? $cardTotal : ($isAguardando ? $cardAguardando : ($isEmAtendimento ? $cardEmAtendimento : $cardConcluidos)) }}</div>
                            <div class="metric-sub">{{ $isFilaTotal ? 'chamados registrados' : 'chamados nessa fila' }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($isVisaoGeral)
        <div class="section-card mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1 font-weight-bold">Como funciona?</h5>
                        <div class="text-muted">Clique em qualquer card para abrir a fila correspondente.</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="info-card">
                            <h5>Visão geral das filas</h5>
                            <table class="table ticket-table mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Categoria</th>
                                        <th>Subcategoria</th>
                                        <th>Solicitante</th>
                                        <th>Prazo</th>
                                        <th>Criado em</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($chamadosAguardandoAnalise->take(3) as $chamado)
                                        <tr>
                                            <td>{{ $chamado->id }}</td>
                                            <td>{{ $chamado->categoria?->nome ?? '-' }}</td>
                                            <td>{{ $chamado->subcategoria?->nome ?? '-' }}</td>
                                            <td>{{ $chamado->solicitante?->nome ?? '-' }}</td>
                                            <td>{{ $chamado->prazo_atendimento_em?->format('d/m/Y H:i') ?? '-' }}</td>
                                            <td>{{ $chamado->created_at?->format('d/m/Y H:i') }}</td>
                                            <td><span class="badge badge-warning">{{ $chamado->status?->nome ?? 'Aguardando análise' }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center text-muted py-4">Nenhum chamado disponível.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="info-card h-100">
                            <h5>Atalhos</h5>
                            <div class="list-group overview-list">
                                <a href="{{ route('chamado.atendimento.total') }}" class="list-group-item list-group-item-action">Fila total</a>
                                <a href="{{ route('chamado.atendimento.aguardando') }}" class="list-group-item list-group-item-action">Aguardando análise</a>
                                <a href="{{ route('chamado.atendimento.em') }}" class="list-group-item list-group-item-action">Em atendimento</a>
                                <a href="{{ route('chamado.atendimento.concluidos') }}" class="list-group-item list-group-item-action">Concluídos</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="section-card mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1 font-weight-bold">{{ $tituloTela }}</h5>
                        <div class="text-muted">Use os filtros e selecione um chamado para ver os detalhes.</div>
                    </div>
                    <div class="d-flex" style="gap:.5rem;">
                        <a href="{{ route('chamado.atendimento') }}" class="btn btn-outline-primary">Visão geral</a>
                        <a href="{{ route('chamado.atendimento.total') }}" class="btn btn-outline-primary">Fila total</a>
                    </div>
                </div>

                @if($isFilaTotal)
                    <div class="table-responsive">
                        <table class="table ticket-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Categoria</th>
                                    <th>Subcategoria</th>
                                    <th>Solicitante</th>
                                    <th>Prazo</th>
                                    <th>Criado em</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($chamadosTotal as $chamado)
                                    <tr>
                                        <td>{{ $chamado->id }}</td>
                                        <td>{{ $chamado->categoria?->nome ?? '-' }}</td>
                                        <td>{{ $chamado->subcategoria?->nome ?? '-' }}</td>
                                        <td>{{ $chamado->solicitante?->nome ?? '-' }}</td>
                                        <td>{{ $chamado->prazo_atendimento_em?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td>{{ $chamado->created_at?->format('d/m/Y H:i') }}</td>
                                        <td><span class="badge badge-{{ $chamado->status?->cor ?? 'secondary' }}">{{ $chamado->status?->nome ?? 'Sem status' }}</span></td>
                                        <td><a href="{{ route('chamado.atendimento.aguardando') }}" class="btn btn-xs btn-outline-primary">Abrir atendimento</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted py-4">Nenhum chamado encontrado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $chamadosTotal->links() }}</div>
                @elseif($isAguardando)
                    <div class="row">
                        <div class="col-lg-5">
                            <div class="info-card ticket-scroll">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">Chamados na fila</h5>
                                    <div>
                                        <a href="{{ route('chamado.atendimento') }}" class="btn btn-xs btn-outline-primary">Voltar</a>
                                    </div>
                                </div>
                                <div class="list-group overview-list">
                                    @forelse($chamadosAguardandoAnalise as $chamado)
                                        <a href="{{ request()->fullUrlWithQuery(['ticket' => $chamado->id]) }}#ticket-{{ $chamado->id }}" class="list-group-item list-group-item-action {{ (int) request()->query('ticket') === (int) $chamado->id || (! request()->filled('ticket') && $loop->first) ? 'active' : '' }}">
                                            <div class="d-flex justify-content-between">
                                                <strong>#{{ $chamado->id }} - {{ $chamado->subcategoria?->nome ?? $chamado->categoria?->nome }}</strong>
                                                <span class="badge badge-warning">{{ $chamado->status?->nome ?? 'Aguardando análise' }}</span>
                                            </div>
                                            <div class="text-muted small">{{ $chamado->solicitante?->nome ?? '-' }}</div>
                                            <div class="text-muted small">Atualizado em {{ $chamado->updated_at?->format('d/m/Y H:i') }}</div>
                                        </a>
                                    @empty
                                        <div class="text-muted">Nenhum chamado na fila.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            @php $selected = $chamadosAguardandoAnalise->firstWhere('id', (int) request()->query('ticket')) ?? $chamadosAguardandoAnalise->first(); @endphp
                            @if($selected)
                                <div class="info-card mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <div class="text-primary font-weight-bold">#{{ $selected->id }}</div>
                                            <h5 class="mb-0">{{ $selected->subcategoria?->nome ?? $selected->categoria?->nome }}</h5>
                                        </div>
                                        <span class="badge badge-warning">{{ $selected->status?->nome }}</span>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-2"><strong>Solicitante:</strong> {{ $selected->solicitante?->nome ?? '-' }}</div>
                                        <div class="col-md-6 mb-2"><strong>Prazo:</strong> {{ $selected->prazo_atendimento_em?->format('d/m/Y H:i') ?? '-' }}</div>
                                        <div class="col-md-6 mb-2"><strong>Categoria:</strong> {{ $selected->categoria?->nome ?? '-' }}</div>
                                        <div class="col-md-6 mb-2"><strong>Subcategoria:</strong> {{ $selected->subcategoria?->nome ?? '-' }}</div>
                                    </div>
                                    <div class="mt-3">
                                        <h5 class="mb-2">Descrição</h5>
                                        <div style="white-space: pre-line;">{{ $selected->descricao }}</div>
                                    </div>
                                    <div class="mt-4">
                                        <h5 class="mb-2">Timeline</h5>
                                        <div class="timeline timeline-inverse mb-0">
                                            @forelse($selected->logs->sortBy('created_at') as $log)
                                                <div>
                                                    <i class="fas fa-circle bg-{{ $log->nivel ?? 'primary' }}"></i>
                                                    <div class="timeline-item">
                                                        <span class="time">{{ $log->created_at?->format('d/m/Y H:i') }}</span>
                                                        <h3 class="timeline-header">{{ $log->acao }}</h3>
                                                        <div class="timeline-body">
                                                            <div><strong>Usuário:</strong> {{ $log->usuario?->nome ?? 'Sistema' }}</div>
                                                            @if($log->detalhes)
                                                                @php $detalhes = json_decode($log->detalhes, true); @endphp
                                                                @if(is_array($detalhes) && count($detalhes))
                                                                    <ul class="mb-0 mt-2 pl-3">
                                                                        @foreach($detalhes as $chave => $valor)
                                                                            <li><strong>{{ ucfirst(str_replace('_', ' ', $chave)) }}:</strong> {{ is_array($valor) ? json_encode($valor, JSON_UNESCAPED_UNICODE) : $valor }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-muted">Sem eventos registrados.</div>
                                            @endforelse
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <form method="POST" action="{{ route('chamado.atendimento.assumir', $selected) }}">
                                                    @csrf
                                                    <div class="info-card">
                                                        <h5>Atribuir e assumir atendimento</h5>
                                                        <div class="form-group">
                                                            <label class="text-muted text-sm">Superadmin responsável</label>
                                                            <select name="responsavel_user_id" class="form-control" required>
                                                                <option value="">Selecione</option>
                                                                @foreach($superadmins ?? [] as $superadmin)
                                                                    <option value="{{ $superadmin->id }}">{{ $superadmin->nome }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <button class="btn btn-primary btn-block">
                                                            <i class="fas fa-user-check mr-2"></i> Assumir atendimento
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <form method="POST" action="{{ route('chamado.atendimento.concluir', $selected) }}" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="info-card">
                                                        <h5>Concluir chamado</h5>
                                                        <div class="form-group">
                                                            <label class="text-muted text-sm">Justificativa / solução</label>
                                                            <textarea name="justificativa" class="form-control" rows="4" required placeholder="Descreva o que foi feito e o desfecho..."></textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="text-muted text-sm">Imagens</label>
                                                            <input type="file" name="anexos[]" class="form-control" multiple accept="image/*">
                                                        </div>
                                                        <button class="btn btn-success btn-block">
                                                            <i class="fas fa-check mr-2"></i> Concluir
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <form method="POST" action="{{ route('chamado.atendimento.transferir', $selected) }}">
                                                    @csrf
                                                    <div class="info-card">
                                                        <h5>Transferir atendimento</h5>
                                                        <div class="form-group">
                                                            <label class="text-muted text-sm">Novo superadmin</label>
                                                            <select name="responsavel_user_id" class="form-control" required>
                                                                <option value="">Selecione</option>
                                                                @foreach($superadmins ?? [] as $superadmin)
                                                                    <option value="{{ $superadmin->id }}">{{ $superadmin->nome }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="text-muted text-sm">Descrição</label>
                                                            <textarea name="descricao" class="form-control" rows="3" required placeholder="Explique o motivo da transferência..."></textarea>
                                                        </div>
                                                        <button class="btn btn-primary btn-block">
                                                            <i class="fas fa-exchange-alt mr-2"></i> Transferir
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <form method="POST" action="{{ route('chamado.atendimento.cancelar', $selected) }}">
                                                    @csrf
                                                    <div class="info-card">
                                                        <h5>Cancelar chamado</h5>
                                                        <div class="form-group">
                                                            <label class="text-muted text-sm">Justificativa</label>
                                                            <textarea name="cancelamento_justificativa" class="form-control" rows="4" required placeholder="Explique o motivo do cancelamento..."></textarea>
                                                        </div>
                                                        <button class="btn btn-outline-danger btn-block">
                                                            <i class="fas fa-ban mr-2"></i> Cancelar
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif($isEmAtendimento)
                    <div class="row">
                        <div class="col-lg-5">
                            <div class="info-card">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h5 class="mb-0">Chamados em atendimento</h5>
                                        <div class="text-muted small">Selecione um chamado para gerenciar a fila.</div>
                                    </div>
                                </div>
                                <div class="list-group overview-list">
                                    @forelse($chamadosEmAtendimento as $chamado)
                                        <a href="{{ request()->fullUrlWithQuery(['ticket' => $chamado->id]) }}#ticket-em-{{ $chamado->id }}" class="list-group-item list-group-item-action {{ (int) request()->query('ticket') === (int) $chamado->id || (! request()->filled('ticket') && $loop->first) ? 'active' : '' }}">
                                            <div class="d-flex justify-content-between">
                                                <strong>#{{ $chamado->id }} - {{ $chamado->subcategoria?->nome ?? $chamado->categoria?->nome }}</strong>
                                                <span class="badge badge-info">{{ $chamado->status?->nome ?? 'Em atendimento' }}</span>
                                            </div>
                                            <div class="text-muted small">{{ $chamado->solicitante?->nome ?? '-' }}</div>
                                            <div class="text-muted small">Responsável: {{ $chamado->responsavel?->nome ?? '-' }}</div>
                                        </a>
                                    @empty
                                        <div class="text-muted">Nenhum chamado em atendimento.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            @php $selectedEmAtendimento = $chamadosEmAtendimento->firstWhere('id', (int) request()->query('ticket')) ?? $chamadosEmAtendimento->first(); @endphp
                            @if($selectedEmAtendimento)
                                <div class="info-card mb-3" id="ticket-em-{{ $selectedEmAtendimento->id }}">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <div class="text-primary font-weight-bold">#{{ $selectedEmAtendimento->id }}</div>
                                            <h5 class="mb-0">{{ $selectedEmAtendimento->subcategoria?->nome ?? $selectedEmAtendimento->categoria?->nome }}</h5>
                                        </div>
                                        <span class="badge badge-info">{{ $selectedEmAtendimento->status?->nome }}</span>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-2"><strong>Solicitante:</strong> {{ $selectedEmAtendimento->solicitante?->nome ?? '-' }}</div>
                                        <div class="col-md-6 mb-2"><strong>Responsável:</strong> {{ $selectedEmAtendimento->responsavel?->nome ?? '-' }}</div>
                                        <div class="col-md-6 mb-2"><strong>Prazo:</strong> {{ $selectedEmAtendimento->prazo_atendimento_em?->format('d/m/Y H:i') ?? '-' }}</div>
                                        <div class="col-md-6 mb-2"><strong>Criado em:</strong> {{ $selectedEmAtendimento->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                                    </div>
                                    <div class="mt-3">
                                        <h5 class="mb-2">Descrição</h5>
                                        <div style="white-space: pre-line;">{{ $selectedEmAtendimento->descricao }}</div>
                                    </div>
                                    <div class="mt-4">
                                        <h5 class="mb-2">Timeline</h5>
                                        <div class="timeline timeline-inverse mb-0">
                                            @forelse($selectedEmAtendimento->logs->sortBy('created_at') as $log)
                                                <div>
                                                    <i class="fas fa-circle bg-{{ $log->nivel ?? 'primary' }}"></i>
                                                    <div class="timeline-item">
                                                        <span class="time">{{ $log->created_at?->format('d/m/Y H:i') }}</span>
                                                        <h3 class="timeline-header">{{ $log->acao }}</h3>
                                                        <div class="timeline-body">
                                                            <div><strong>Usuário:</strong> {{ $log->usuario?->nome ?? 'Sistema' }}</div>
                                                            @if($log->detalhes)
                                                                @php $detalhes = json_decode($log->detalhes, true); @endphp
                                                                @if(is_array($detalhes) && count($detalhes))
                                                                    <ul class="mb-0 mt-2 pl-3">
                                                                        @foreach($detalhes as $chave => $valor)
                                                                            <li><strong>{{ ucfirst(str_replace('_', ' ', $chave)) }}:</strong> {{ is_array($valor) ? json_encode($valor, JSON_UNESCAPED_UNICODE) : $valor }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-muted">Sem eventos registrados.</div>
                                            @endforelse
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <h5 class="p-2 bg-gray text-white text-center">Ações disponíveis para este chamado:</h5>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <form method="POST" action="{{ route('chamado.atendimento.mensagem', $selectedEmAtendimento) }}" enctype="multipart/form-data">
                                                @csrf
                                                <div class="info-card h-100">
                                                    <h5>Enviar mensagem</h5>
                                                    <div class="form-group">
                                                        <label class="text-muted text-sm">Mensagem</label>
                                                        <textarea name="mensagem" class="form-control" rows="4" required placeholder="Escreva a mensagem para o solicitante...">{{ old('mensagem') }}</textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="text-muted text-sm">Anexos</label>
                                                        <input type="file" name="anexos[]" class="form-control" multiple accept="image/*">
                                                    </div>
                                                    <button class="btn btn-primary btn-block">
                                                        <i class="fas fa-paper-plane mr-2"></i> Enviar mensagem
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <form method="POST" action="{{ route('chamado.atendimento.concluir', $selectedEmAtendimento) }}" enctype="multipart/form-data">
                                                @csrf
                                                <div class="info-card h-100">
                                                    <h5>Concluir chamado</h5>
                                                    <div class="form-group">
                                                        <label class="text-muted text-sm">Justificativa / solução</label>
                                                        <textarea name="justificativa" class="form-control" rows="4" required placeholder="Descreva o que foi feito e o desfecho..."></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="text-muted text-sm">Imagens</label>
                                                        <input type="file" name="anexos[]" class="form-control" multiple accept="image/*">
                                                    </div>
                                                    <button class="btn btn-success btn-block">
                                                        <i class="fas fa-check mr-2"></i> Concluir
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <form method="POST" action="{{ route('chamado.atendimento.transferir', $selectedEmAtendimento) }}">
                                                @csrf
                                                <div class="info-card h-100">
                                                    <h5>Transferir atendimento</h5>
                                                    <div class="form-group">
                                                        <label class="text-muted text-sm">Novo superadmin</label>
                                                        <select name="responsavel_user_id" class="form-control" required>
                                                            <option value="">Selecione</option>
                                                            @foreach($superadmins ?? [] as $superadmin)
                                                                <option value="{{ $superadmin->id }}">{{ $superadmin->nome }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="text-muted text-sm">Descrição</label>
                                                        <textarea name="descricao" class="form-control" rows="3" required placeholder="Explique o motivo da transferência..."></textarea>
                                                    </div>
                                                    <button class="btn btn-primary btn-block">
                                                        <i class="fas fa-exchange-alt mr-2"></i> Transferir
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <form method="POST" action="{{ route('chamado.atendimento.cancelar', $selectedEmAtendimento) }}">
                                                @csrf
                                                <div class="info-card h-100">
                                                    <h5>Cancelar chamado</h5>
                                                    <div class="form-group">
                                                        <label class="text-muted text-sm">Justificativa</label>
                                                        <textarea name="cancelamento_justificativa" class="form-control" rows="3" required placeholder="Explique o motivo do cancelamento..."></textarea>
                                                    </div>
                                                    <button class="btn btn-outline-danger btn-block">
                                                        <i class="fas fa-ban mr-2"></i> Cancelar
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            @endif
                        </div>
                    </div>
                    <div class="mt-3">{{ $chamadosEmAtendimento->links() }}</div>
                @elseif($isConcluidos)
                    <div class="table-responsive">
                        <table class="table ticket-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Categoria</th>
                                    <th>Subcategoria</th>
                                    <th>Solicitante</th>
                                    <th>Responsável</th>
                                    <th>Concluído em</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($chamadosConcluidos as $chamado)
                                    <tr>
                                        <td>{{ $chamado->id }}</td>
                                        <td>{{ $chamado->categoria?->nome ?? '-' }}</td>
                                        <td>{{ $chamado->subcategoria?->nome ?? '-' }}</td>
                                        <td>{{ $chamado->solicitante?->nome ?? '-' }}</td>
                                        <td>{{ $chamado->responsavel?->nome ?? '-' }}</td>
                                        <td>{{ $chamado->concluido_em?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td><span class="badge badge-success">{{ $chamado->status?->nome ?? 'Concluído' }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-4">Nenhum chamado concluído.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $chamadosConcluidos->links() }}</div>
                @endif
            </div>
        </div>
    @endif
@stop
