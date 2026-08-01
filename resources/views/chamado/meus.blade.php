@extends('adminlte::page')
@section('title', 'Meus chamados')
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop
@section('content')
    <style>
        .support-hero {
            border-radius: 24px;
            padding: 24px 24px 26px;
            background: linear-gradient(135deg, #071633 0%, #0b2e78 52%, #0a57d6 100%);
            color: #fff;
            box-shadow: 0 18px 40px rgba(8, 20, 58, .16);
        }
        .support-hero h1 {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -.03em;
            margin-bottom: .5rem;
        }
        .support-hero p {
            color: rgba(255,255,255,.85);
            margin: 0;
            font-size: 1.02rem;
        }
        .support-path {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            color: rgba(255,255,255,.88);
            font-weight: 600;
            font-size: .95rem;
            margin-bottom: 18px;
        }
        .support-path i {
            opacity: .95;
        }
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
        }
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
        .metric-label {
            color: #64748b;
            font-size: .8rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .metric-value {
            font-size: 1.9rem;
            line-height: 1;
            font-weight: 800;
            letter-spacing: -.04em;
            margin-top: 2px;
        }
        .filter-shell {
            margin-top: 16px;
            background: #fff;
            border: 1px solid #e7edf6;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
            padding: 18px;
        }
        .filter-shell label {
            font-weight: 700;
            color: #334155;
            margin-bottom: .45rem;
        }
        .filter-shell .form-control {
            border-radius: 10px;
            border-color: #d9e2ef;
            height: 46px;
        }
        .btn-filter-main {
            height: 46px;
            border-radius: 12px;
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
            border: 0;
            box-shadow: 0 12px 22px rgba(37, 99, 235, .25);
        }
        .btn-filter-clear {
            height: 46px;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #d9e2ef;
            color: #334155;
        }
        .ticket-row {
            border: 1px solid #e7edf6;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .05);
            overflow: hidden;
            margin-bottom: 12px;
        }
        .ticket-row .ticket-head {
            padding: 18px 18px;
        }
        .ticket-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 132px;
            padding: .45rem .85rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: .82rem;
        }
        .ticket-title {
            font-size: 1.08rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }
        .ticket-sub {
            color: #64748b;
            font-size: .88rem;
            margin-top: .2rem;
        }
        .ticket-meta {
            color: #475569;
            font-size: .9rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            white-space: nowrap;
        }
        .ticket-toggle {
            border-radius: 12px;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            background: #fff;
            padding: .55rem 1rem;
            min-width: 156px;
            text-align: center;
            font-weight: 700;
        }
        .ticket-details {
            border-top: 1px solid #edf2f7;
            background: #fafcff;
            padding: 18px;
        }
        .info-card {
            background: #fff;
            border: 1px solid #e7edf6;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .04);
            height: 100%;
        }
        .info-card h5 {
            font-size: .96rem;
            font-weight: 800;
            margin-bottom: 12px;
            color: #0f172a;
        }
        .timeline.timeline-inverse:before { left: 20px; }
        .timeline > div > .timeline-item {
            margin-left: 52px;
            border-radius: 14px;
            border: 1px solid #eef2f7;
            box-shadow: 0 6px 14px rgba(15, 23, 42, .04);
            background: #fff;
        }
        .timeline > div > .fas,
        .timeline > div > .far {
            left: 10px;
            width: 22px;
            height: 22px;
            line-height: 22px;
            font-size: 11px;
        }
        .timeline-header {
            font-weight: 700;
        }
    </style>

    @php
        $totalChamados = $chamados->total();
        $filtrados = $chamados->count();
    @endphp

    <div class="support-hero">
        <div class="row align-items-start">
            <div class="col-lg-8">
                <div class="support-path">
                    <i class="fas fa-headphones-alt"></i>
                    <span>Suporte</span>
                    <i class="fas fa-chevron-right" style="font-size:.72rem;"></i>
                    <span>Central de chamados</span>
                </div>
                <h1>Meus chamados</h1>
                <p>Acompanhe seus chamados em um painel mais limpo e organizado.</p>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0">
                <div class="row">
                    <div class="col-6">
                        <div class="metric-box">
                            <div class="metric-icon"><i class="far fa-calendar-check"></i></div>
                            <div>
                                <div class="metric-label">Total</div>
                                <div class="metric-value">{{ $totalChamados }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric-box">
                            <div class="metric-icon"><i class="fas fa-filter"></i></div>
                            <div>
                                <div class="metric-label">Filtrados</div>
                                <div class="metric-value">{{ $filtrados }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-shell">
        <form method="GET" action="{{ route('chamado.meus') }}">
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="aguardando_analise" @selected(($filtros['status'] ?? '') === 'aguardando_analise')>Aguardando análise</option>
                        <option value="em_atendimento" @selected(($filtros['status'] ?? '') === 'em_atendimento')>Em andamento</option>
                        <option value="concluido" @selected(($filtros['status'] ?? '') === 'concluido')>Concluído</option>
                        <option value="cancelado" @selected(($filtros['status'] ?? '') === 'cancelado')>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Categoria</label>
                    <select name="categoria_id" class="form-control">
                        <option value="">Todas</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected((int)($filtros['categoria_id'] ?? 0) === (int) $categoria->id)>{{ $categoria->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group">
                    <label>Data inicial</label>
                    <input type="date" name="data_inicio" class="form-control" value="{{ $filtros['data_inicio'] ?? '' }}">
                </div>
                <div class="col-md-2 form-group">
                    <label>Data final</label>
                    <input type="date" name="data_fim" class="form-control" value="{{ $filtros['data_fim'] ?? '' }}">
                </div>
                <div class="col-md-12 form-group">
                    <label>Pesquisar</label>
                    <input type="text" name="termo" class="form-control" value="{{ $filtros['termo'] ?? '' }}" placeholder="Busque por descrição, categoria ou subcategoria">
                </div>
                <div class="col-md-9 form-group mb-0 d-flex align-items-end">
                    <button class="btn btn-primary btn-filter-main px-4 mr-2">
                        <i class="fas fa-filter mr-2"></i> Filtrar
                    </button>
                    <a href="{{ route('chamado.meus') }}" class="btn btn-filter-clear px-4">
                        <i class="fas fa-undo mr-2"></i> Limpar filtros
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="mt-3" id="accordionChamados">
        @forelse($chamados as $chamado)
            @php
                $collapseId = 'ticket-' . $chamado->id;
                $badgeClass = match ($chamado->status?->slug) {
                    'aguardando_analise' => 'warning',
                    'em_atendimento' => 'success',
                    'concluido' => 'secondary',
                    'cancelado' => 'danger',
                    default => 'secondary',
                };
                $openByDefault = false;
            @endphp
            <div class="ticket-row">
                <div class="ticket-head">
                    <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between" style="gap:1rem;">
                        <div class="d-flex flex-wrap align-items-center" style="gap:1rem;">
                            <span class="ticket-status badge badge-{{ $badgeClass }}">{{ $chamado->status?->nome ?? 'Sem status' }}</span>
                            <strong class="text-dark">#{{ $chamado->id }}</strong>
                            <span class="ticket-title">{{ $chamado->subcategoria?->nome ?? $chamado->categoria?->nome ?? 'Chamado' }}</span>
                            <span class="ticket-sub">{{ $chamado->categoria?->nome ?? '-' }}</span>
                            <span class="ticket-meta"><i class="far fa-calendar-alt"></i> Criado em {{ $chamado->created_at?->format('d/m/Y H:i') }}</span>
                            <span class="ticket-meta"><i class="far fa-clock"></i> Prazo {{ $chamado->prazo_atendimento_em ? $chamado->prazo_atendimento_em->format('d/m/Y H:i') : '-' }}</span>
                            <span class="ticket-meta"><i class="fas fa-user-tie"></i> Responsável: {{ $chamado->responsavel?->nome ?? 'Não atribuído' }}</span>
                        </div>
                        <button class="ticket-toggle btn" type="button" data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="{{ $openByDefault ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                            Ver detalhes <i class="fas fa-chevron-{{ $openByDefault ? 'up' : 'down' }} ml-2"></i>
                        </button>
                    </div>
                </div>

                <div id="{{ $collapseId }}" class="collapse" data-parent="#accordionChamados">
                    <div class="ticket-details">
                        <div class="row">
                            <div class="col-lg-8 mb-3">
                                <div class="info-card">
                                    <h5>Descrição</h5>
                                    <div style="white-space: pre-line;">{{ $chamado->descricao }}</div>
                                </div>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <div class="info-card">
                                    <h5>Informações</h5>
                                    <div class="d-flex flex-column" style="gap:.85rem;">
                                        <div class="d-flex justify-content-between" style="gap:1rem;">
                                            <span class="text-muted">Solicitante</span>
                                            <strong class="text-right">{{ $chamado->solicitante?->nome ?? '-' }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between" style="gap:1rem;">
                                            <span class="text-muted">Responsável</span>
                                            <strong class="text-right">{{ $chamado->responsavel?->nome ?? 'Não atribuído' }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between" style="gap:1rem;">
                                            <span class="text-muted">Última atualização</span>
                                            <strong class="text-right">{{ $chamado->updated_at?->format('d/m/Y H:i') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8 mb-3">
                                <div class="info-card">
                                    <h5>Timeline</h5>
                                    <div class="timeline timeline-inverse mb-0">
                                        @forelse($chamado->logs->sortBy('created_at') as $log)
                                            <div>
                                                <i class="fas fa-circle bg-{{ $log->nivel ?? 'primary' }}"></i>
                                                <div class="timeline-item">
                                                    <span class="time"><i class="far fa-clock"></i> {{ $log->created_at?->format('d/m/Y H:i') }}</span>
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
                                                        @php
                                                            $anexosDoLog = $chamado->anexos->where('chamado_log_id', $log->id);
                                                        @endphp
                                                        @if($anexosDoLog->isNotEmpty())
                                                            <div class="mt-2">
                                                                <div class="text-muted small mb-1">Anexos desta mensagem</div>
                                                                <div class="d-flex flex-column" style="gap:.5rem;">
                                                                    @foreach($anexosDoLog as $anexo)
                                                                        <div class="d-flex align-items-center justify-content-between border rounded-lg p-2">
                                                                            <div class="text-truncate mr-2">
                                                                                <i class="fas fa-paperclip text-primary mr-1"></i>{{ $anexo->original_name }}
                                                                            </div>
                                                                            @if($anexo->url)
                                                                                <a href="{{ $anexo->url }}" target="_blank" class="btn btn-xs btn-outline-primary">Abrir</a>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-muted">Sem eventos registrados.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            @php
                                $statusSlug = $chamado->status?->slug;
                                $permiteMensagem = ! in_array($statusSlug, ['cancelado', 'concluido'], true);
                            @endphp
                            @if($permiteMensagem)
                                <div class="col-lg-4 mb-3">
                                    <form method="POST" action="{{ route('chamado.meus.mensagem', $chamado) }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="info-card">
                                            <h5>Enviar mensagem</h5>
                                            <div class="form-group">
                                                <label class="text-muted text-sm">Mensagem</label>
                                                <textarea name="mensagem" class="form-control" rows="4" required placeholder="Escreva sua mensagem para o suporte...">{{ old('mensagem') }}</textarea>
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
                            @endif
                            <div class="col-lg-4 mb-3">
                                <div class="info-card">
                                    <h5>Anexos ({{ $chamado->anexos->count() }})</h5>
                                    @forelse($chamado->anexos as $anexo)
                                        <div class="d-flex align-items-center justify-content-between border rounded-lg p-2 mb-2">
                                            <div class="text-truncate mr-2">
                                                <i class="fas fa-paperclip text-primary mr-1"></i>{{ $anexo->original_name }}
                                            </div>
                                            @if($anexo->url)
                                                <a href="{{ $anexo->url }}" target="_blank" class="btn btn-xs btn-outline-primary">Abrir</a>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="text-muted">Nenhum arquivo anexado.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">Nenhum chamado encontrado.</div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $chamados->withQueryString()->links() }}
    </div>
@stop
