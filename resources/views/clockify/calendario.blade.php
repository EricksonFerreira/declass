@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <x-page-title :title="$titulo" />

        <!-- Filtros Rápidos do Calendário estilo Clockify -->
        <div class="d-flex align-items-center flex-wrap cal-header-actions">
            <!-- Botão rápido para agendar nova alocação sem precisar de coluna lateral -->
            @can('operacao.calendario.store')
                <button type="button" class="btn btn-sm btn-success font-weight-bold mr-2 mb-2 shadow-sm" onclick="openAddAllocationModalDirect()" style="border-radius: 8px;">
                    <i class="fas fa-calendar-plus mr-1"></i> Agendar Alocação
                </button>
            @endcan

            <select id="calendar-user-filter" class="form-control form-control-sm select2 mb-2" style="min-width: 220px; border-radius: 8px;">
                <option value="all" selected>👥 Colegas de equipe (Todos)</option>
                <option value="current">👤 Apenas meus registros</option>
                @foreach ($usuarios as $u)
                    @if($u->id !== Auth::id())
                        <option value="user-{{ $u->id }}">👤 {{ $u->nome }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Visualização do Calendário 100% de largura -->
        <div class="col-md-12">
            <div class="card cal-card shadow-sm border-0 mb-4">
                <div class="card-body p-3 position-relative">
                    <!-- Calendar Container -->
                    <div id="full-calendar"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE CADASTRO DE ALOCAÇÃO DIRECT (Estilo Popover) -->
    <div class="modal fade" id="modalAddAllocationDirect" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-calendar-plus mr-2 text-success"></i> Agendar Alocação de Tempo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('operacao.calendario.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="modal-user-id" class="text-sm">Colaborador <span class="text-danger">*</span></label>
                            <select name="user_id" id="modal-user-id" class="form-control form-control-sm" required>
                                <option value="">Selecione o colaborador...</option>
                                @foreach ($usuarios as $u)
                                    <option value="{{ $u->id }}" @selected($u->id === Auth::id())>{{ $u->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="modal-projeto-id" class="text-sm">Projeto <span class="text-danger">*</span></label>
                            <select name="clockify_projeto_id" id="modal-projeto-id" class="form-control form-control-sm" required>
                                <option value="">Selecione o projeto...</option>
                                @foreach ($projetos as $p)
                                    <option value="{{ $p->id }}">{{ $p->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 form-group">
                                <label for="modal-data-inicio" class="text-sm">Data de Início <span class="text-danger">*</span></label>
                                <input type="date" name="data_inicio" id="modal-data-inicio" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-6 form-group">
                                <label for="modal-data-fim" class="text-sm">Data de Fim <span class="text-danger">*</span></label>
                                <input type="date" name="data_fim" id="modal-data-fim" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="modal-horas" class="text-sm">Horas por Dia</label>
                            <input type="number" step="0.5" name="horas_por_dia" id="modal-horas" class="form-control form-control-sm" value="8.0" min="0.5" max="24">
                        </div>
                        <div class="form-group mb-0">
                            <label for="modal-obs" class="text-sm">Observação</label>
                            <textarea name="observacao" id="modal-obs" rows="2" class="form-control form-control-sm" placeholder="Algum detalhe opcional..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success btn-sm font-weight-bold"><i class="fas fa-check mr-1"></i> Agendar Alocação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- POPOVER DE DETALHES DO EVENTO - DESIGN PREMIUM -->
    <div id="clockify-event-popover" class="ck-popover d-none" style="position: absolute; z-index: 9999; width: 310px; ">
        <!-- Header colorido por projeto -->
        <div class="ck-pop-header" id="popover-header">
            <div class="ck-pop-header-top">
                <span class="ck-pop-badge" id="popover-event-type-badge">Tempo</span>
                <button type="button" class="ck-pop-close" onclick="closePopover()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="ck-pop-project" id="popover-event-project">PROJETO</div>
            <h6 class="ck-pop-title" id="popover-event-title">Título do Registro</h6>
        </div>
        <!-- Body -->
        <div class="ck-pop-body">
            <div class="ck-pop-info-row">
                <span class="ck-pop-icon"><i class="far fa-user"></i></span>
                <span id="popover-event-user" class="ck-pop-info-text">Colaborador</span>
            </div>
            <div class="ck-pop-info-row">
                <span class="ck-pop-icon"><i class="far fa-clock"></i></span>
                <span id="popover-event-time" class="ck-pop-info-text ck-pop-time">–</span>
            </div>
            <div class="ck-pop-hint">
                <i class="fas fa-arrows-alt mr-1"></i> Arraste o cartão no calendário para mover ou redimensionar.
            </div>
        </div>
        <!-- Footer -->
        <div class="ck-pop-footer">
            <form id="popover-delete-form" method="POST" action="" onsubmit="return confirm('Deseja realmente remover este registro?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="ck-pop-btn-delete">
                    <i class="fas fa-trash-alt"></i> Excluir
                </button>
            </form>
            <button type="button" class="ck-pop-btn-close" onclick="closePopover()">Fechar</button>
        </div>
    </div>
@stop

@section('css')
    <!-- FontAwesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
    <style>
        /* =====================================================
           PADRÃO VISUAL DO SISTEMA (mesmo da tela de projeto)
        ===================================================== */
        body { background-color: #f4f6f9; }

        .cal-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        }

        #full-calendar { min-height: 650px; }

        /* =====================================================
           VARIÁVEIS E RESET
        ===================================================== */
        :root {
            --ck-radius: 10px;
            --ck-shadow: 0 4px 24px rgba(15,23,42,0.10);
            --ck-border: #e8edf5;
            --ck-bg: #f8fafc;
            --ck-text: #1e293b;
            --ck-muted: #64748b;
            --ck-blue: #0284c7;
            --ck-green: #059669;
        }

        /* =====================================================
           FULLCALENDAR BASE
        ===================================================== */
        .fc { font-size: 13px; }
        .fc-theme-standard td, .fc-theme-standard th { border-color: var(--ck-border); }
        .fc-timegrid-slots td { height: 44px !important; }
        .fc-timeGridThreeDay-view .fc-timegrid-slots td { height: 58px !important; }
        .fc-timeGridDay-view .fc-timegrid-slots td { height: 64px !important; }
        .fc-daygrid-day-frame { background: #fff; }

        .fc-theme-standard .fc-scrollgrid {
            border-radius: var(--ck-radius);
            overflow: hidden;
            border-color: var(--ck-border);
        }

        /* Cabeçalho dos dias */
        .fc-col-header-cell {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 10px 0 !important;
            font-weight: 700;
            color: var(--ck-muted);
            border-bottom: 2px solid var(--ck-border);
        }
        .fc-col-header-cell.fc-day-today {
            background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%) !important;
            color: #1d4ed8 !important;
        }
        /* Linha do dia atual */
        .fc-day-today {
            background: rgba(59,130,246,0.03) !important;
        }
        /* Linha do NOW */
        .fc-timegrid-now-indicator-line {
            border-color: #ef4444 !important;
            border-width: 2px !important;
        }
        .fc-timegrid-now-indicator-arrow {
            border-top-color: #ef4444 !important;
            border-bottom-color: #ef4444 !important;
        }

        /* =====================================================
           BOTÕES DO CALENDÁRIO
        ===================================================== */
        .fc-button-primary {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
            border: none !important;
            font-weight: 700 !important;
            font-size: 12px !important;
            text-transform: capitalize !important;
            border-radius: 8px !important;
            padding: 6px 14px !important;
            box-shadow: 0 2px 8px rgba(2,132,199,0.25) !important;
            transition: all 0.2s ease !important;
            letter-spacing: 0.01em !important;
        }
        .fc-button-primary:hover {
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%) !important;
            box-shadow: 0 4px 12px rgba(2,132,199,0.35) !important;
            transform: translateY(-1px);
        }
        .fc-button-primary:active {
            transform: translateY(0) !important;
        }
        .fc-button-primary:disabled {
            background: linear-gradient(135deg, #bae6fd 0%, #7dd3fc 100%) !important;
            box-shadow: none !important;
        }
        .fc-button-primary:not(:disabled).fc-button-active {
            background: linear-gradient(135deg, #075985 0%, #0c4a6e 100%) !important;
        }
        .fc-toolbar-title {
            font-size: 16px !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            letter-spacing: -0.02em;
        }

        /* =====================================================
           EVENTOS - BASE (sem visibilidade - apenas container)
        ===================================================== */
        .fc-event {
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
            margin-bottom: 3px !important;
            cursor: grab !important;
        }
        .fc-event:active { cursor: grabbing !important; }
        .fc-event:focus { outline: none !important; }

        /* =====================================================
           CARD DE EVENTO (renderizado via eventContent)
           Fundo levemente tingido pela cor do projeto p/ legibilidade
        ===================================================== */
        .ck-event-card {
            background: linear-gradient(160deg, var(--ck-proj-bg, #f1f5f9) 0%, #ffffff 75%);
            border-left: 4px solid var(--ck-proj-color, #94a3b8);
            border-radius: 8px;
            padding: 7px 9px;
            height: 100%;
            box-shadow: 0 2px 8px rgba(15,23,42,0.10), 0 0 0 1px rgba(15,23,42,0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: box-shadow 0.15s ease, transform 0.15s ease;
            overflow: hidden;
            position: relative;
        }
        .ck-event-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--ck-proj-color, #94a3b8) 0%, transparent 100%);
            opacity: 0.45;
        }
        .ck-event-card:hover {
            box-shadow: 0 6px 18px rgba(15,23,42,0.16), 0 0 0 1px rgba(15,23,42,0.08);
            transform: translateY(-1px);
        }

        .ck-event-project {
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--ck-proj-color, #64748b);
            letter-spacing: 0.06em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ck-event-title {
            font-weight: 700;
            font-size: 12px;
            line-height: 1.3;
            color: #0f172a;
            margin: 3px 0 5px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .ck-event-footer {
            display: flex;
            align-items: center;
            gap: 6px;
            border-top: 1px solid rgba(15,23,42,0.05);
            padding-top: 5px;
            margin-top: auto;
        }
        .ck-event-avatar {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: 800;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 1px 4px rgba(0,0,0,0.15);
        }
        .ck-event-user {
            font-size: 10px;
            font-weight: 700;
            color: #475569;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
        }
        .ck-event-duration {
            font-size: 9px;
            font-weight: 800;
            color: #94a3b8;
            white-space: nowrap;
        }

        /* =====================================================
           RESPONSIVIDADE E AJUSTES DE ESPAÇAMENTO DOS CARDS
        ===================================================== */
        @media (max-width: 1200px) {
            .fc-event {
                margin-bottom: 2px !important;
            }
            .ck-event-card {
                padding: 4px 6px;
            }
            .ck-event-title {
                font-size: 10.5px;
                margin: 1px 0 3px;
                -webkit-line-clamp: 1; /* Limita a 1 linha em telas menores */
            }
            .ck-event-project {
                font-size: 8px;
            }
            .ck-event-avatar {
                width: 14px;
                height: 14px;
                font-size: 7px;
            }
            .ck-event-user {
                font-size: 9px;
            }
            .ck-event-duration {
                font-size: 8px;
            }
        }

        /* Em telas muito estreitas (celulares ou colunas semanais muito espremidas) */
        @media (max-width: 768px) {
            #full-calendar { min-height: 460px; }

            /* Toolbar do FullCalendar: título em linha própria, botões quebram */
            .fc .fc-header-toolbar {
                flex-wrap: wrap;
                row-gap: 8px;
                margin-bottom: 12px;
            }
            .fc .fc-header-toolbar .fc-toolbar-chunk:nth-child(2) {
                order: -1;
                flex: 1 0 100%;
                text-align: center;
            }
            .fc .fc-header-toolbar .fc-toolbar-chunk:nth-child(3) {
                margin-left: auto;
            }
            .fc .fc-toolbar-title { font-size: 14px !important; }
            .fc .fc-button {
                padding: 5px 9px !important;
                font-size: 11px !important;
            }
            .fc .fc-button-group .fc-button { padding: 5px 8px !important; }

            /* Células do mês mais compactas */
            .fc-daygrid-day-number { font-size: 11px; padding: 2px 4px; }
            .fc-daygrid-more-link { font-size: 9px; font-weight: 700; }

            .ck-event-card {
                padding: 2px 4px;
                border-left-width: 3px;
            }
            .ck-event-title {
                font-size: 9.5px;
                font-weight: 600;
            }
            .ck-event-project,
            .ck-event-footer {
                display: none !important; /* Esconde metadados para priorizar o título legível */
            }
            .fc-timegrid-slots td {
                height: 48px !important; /* Aumenta a altura das linhas no mobile para dar espaço */
            }
            .fc-timegrid-axis-cushion { font-size: 8px !important; }

            /* Popover nunca ultrapassa a viewport */
            .ck-popover { width: min(310px, calc(100vw - 24px)) !important; }

            /* Filtro do cabeçalho ocupa linha própria */
            .cal-header-actions { width: 100%; }
            .cal-header-actions .select2-container,
            .cal-header-actions select { width: 100% !important; min-width: 0 !important; }
        }

        /* Eventos na visão de mês: card compacto em 1 linha */
        .fc-daygrid-event .ck-event-card {
            padding: 3px 6px;
            border-left-width: 3px;
        }
        .fc-daygrid-event .ck-event-title {
            font-size: 10.5px;
            -webkit-line-clamp: 1;
            margin: 0;
        }
        .fc-daygrid-event .ck-event-project,
        .fc-daygrid-event .ck-event-footer { display: none; }

        /* Handles de resize mais fáceis de pegar no touch */
        .fc .fc-event-resizer {
            display: flex !important;
            align-items: center;
            justify-content: center;
        }
        .fc .fc-event-resizer::after {
            content: '';
            width: 22px;
            height: 4px;
            border-radius: 4px;
            background: rgba(15,23,42,0.25);
        }
        .fc-timegrid-event .fc-event-resizer { height: 8px; }
        .fc-daygrid-event .fc-event-resizer { width: 8px; justify-content: flex-end; }
        .fc-daygrid-event .fc-event-resizer::after { width: 4px; height: 14px; }

        /* =====================================================
           ALL-DAY (PLANILHA) - Alocações
           O elemento .fc-h-event é o container transparente; a "pill"
           colorida é o div interno .ck-aloc-pill (vars setadas nele mesmo)
        ===================================================== */
        .fc-h-event { background: transparent !important; border: none !important; }
        .ck-aloc-pill {
            display: block;
            width: 100%;
            background: var(--proj-soft, #e0f2fe);
            border-left: 3px solid var(--proj-color, #0284c7);
            border-radius: 6px;
            font-size: 10px;
            font-weight: 800;
            padding: 4px 8px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            box-shadow: 0 1px 3px rgba(15,23,42,0.08);
        }
        .ck-aloc-pill strong { color: var(--proj-color, #0284c7); }

        .ck-pop-hint {
            margin: 2px 0 4px;
            padding: 7px 10px;
            background: #f8fafc;
            border: 1px dashed #e2e8f0;
            border-radius: 8px;
            font-size: 10.5px;
            font-weight: 600;
            color: #94a3b8;
        }

        /* =====================================================
           POPOVER
        ===================================================== */
        .ck-popover {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 40px rgba(15,23,42,0.18), 0 2px 8px rgba(15,23,42,0.08);
            overflow: hidden;
            border: 1px solid rgba(15,23,42,0.06);
            animation: ckPopIn 0.18s cubic-bezier(0.34,1.56,0.64,1);
        }
        @keyframes ckPopIn {
            from { opacity: 0; transform: scale(0.92) translateY(6px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        .ck-pop-header {
            padding: 16px 18px 12px;
            background: var(--ck-pop-color, #0284c7);
            position: relative;
            cursor: grab;
            user-select: none;
            touch-action: none;
        }
        .ck-popover.ck-dragging .ck-pop-header { cursor: grabbing; }
        .ck-pop-close { touch-action: manipulation; }
        .ck-pop-header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .ck-pop-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(255,255,255,0.22);
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-radius: 20px;
            padding: 2px 10px;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .ck-pop-close {
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 50%;
            color: #fff;
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 11px;
            transition: background 0.15s;
        }
        .ck-pop-close:hover { background: rgba(255,255,255,0.32); }
        .ck-pop-project {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: rgba(255,255,255,0.75);
            margin-bottom: 4px;
        }
        .ck-pop-title {
            font-size: 14px;
            font-weight: 800;
            color: #fff;
            line-height: 1.3;
            margin: 0;
        }

        .ck-pop-body {
            padding: 14px 18px 10px;
        }
        .ck-pop-info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 9px;
        }
        .ck-pop-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 12px;
            flex-shrink: 0;
        }
        .ck-pop-info-text {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }
        .ck-pop-time {
            font-weight: 700;
            color: #0f172a;
        }

        .ck-pop-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 18px 14px;
            border-top: 1px solid #f1f5f9;
        }
        .ck-pop-btn-delete {
            background: none;
            border: none;
            color: #ef4444;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            padding: 0;
            transition: color 0.15s;
        }
        .ck-pop-btn-delete:hover { color: #b91c1c; }
        .ck-pop-btn-close {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 16px;
            cursor: pointer;
            transition: background 0.15s;
        }
        .ck-pop-btn-close:hover { background: #e2e8f0; }

        /* =====================================================
           ALL-DAY ROW LABEL
        ===================================================== */
        .fc-daygrid-event-dot { display: none; }
        .fc-timegrid-allday-label .fc-timegrid-axis-cushion {
            font-size: 8px !important;
            letter-spacing: 0.08em;
        }
        .fc-timegrid-axis-cushion {
            font-size: 9px !important;
            font-weight: 700 !important;
            color: #94a3b8 !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Destaque da área onde o evento será solto (drag) */
        .fc .fc-highlight {
            background: rgba(2,132,199,0.10) !important;
            border-radius: 6px;
        }

        /* =====================================================
           SCROLLBAR sutil
        ===================================================== */
        .fc-scroller::-webkit-scrollbar { width: 5px; }
        .fc-scroller::-webkit-scrollbar-track { background: transparent; }
        .fc-scroller::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
@stop

@section('js')
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/pt-br.js"></script>
    <script>
        let popoverEl = null;

        function openAddAllocationModalDirect() {
            $('#modalAddAllocationDirect').modal('show');
        }

        function closePopover() {
            if (popoverEl) {
                popoverEl.classList.add('d-none');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            popoverEl = document.getElementById('clockify-event-popover');

            // Popover arrastável pelo header (mouse + touch)
            (function initPopoverDrag() {
                var handle = popoverEl.querySelector('.ck-pop-header');
                if (!handle) return;

                var dragOffsetX = 0, dragOffsetY = 0, dragging = false;

                handle.addEventListener('pointerdown', function(e) {
                    if (e.target.closest('.ck-pop-close')) return;
                    dragging = true;
                    popoverEl.classList.add('ck-dragging');
                    var rect = popoverEl.getBoundingClientRect();
                    dragOffsetX = e.clientX - rect.left;
                    dragOffsetY = e.clientY - rect.top;
                    handle.setPointerCapture(e.pointerId);
                    e.preventDefault();
                });

                handle.addEventListener('pointermove', function(e) {
                    if (!dragging) return;
                    var scrollX = window.scrollX || document.documentElement.scrollLeft;
                    var scrollY = window.scrollY || document.documentElement.scrollTop;
                    var popWidth = popoverEl.offsetWidth;
                    var newLeft = Math.max(8, Math.min(e.clientX + scrollX - dragOffsetX, scrollX + document.documentElement.clientWidth - popWidth - 8));
                    popoverEl.style.left = newLeft + 'px';
                    popoverEl.style.top = Math.max(8, e.clientY + scrollY - dragOffsetY) + 'px';
                });

                function endDrag() {
                    dragging = false;
                    popoverEl.classList.remove('ck-dragging');
                }
                handle.addEventListener('pointerup', endDrag);
                handle.addEventListener('pointercancel', endDrag);
            })();

            var calendarEl = document.getElementById('full-calendar');
            var rawEvents = {!! $events !!};
            var currentUserId = {{ Auth::id() }};

            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var moveUrlTemplate = "{{ route('operacao.calendario.mover', ['id' => '__ID__']) }}";
            var deleteCalUrlTemplate = "{{ route('operacao.calendario.destroy', ['id' => '__ID__']) }}";
            var deleteTrackerUrlTemplate = "{{ route('operacao.tracker.destroy', ['id' => '__ID__']) }}";
            var canCalStore = {{ auth()->user()->can('operacao.calendario.store') ? 'true' : 'false' }};
            var canCalMove = {{ auth()->user()->can('operacao.calendario.mover') ? 'true' : 'false' }};
            var canCalDestroy = {{ auth()->user()->can('operacao.calendario.destroy') ? 'true' : 'false' }};
            var canTrackerDestroy = {{ auth()->user()->can('operacao.tracker.destroy') ? 'true' : 'false' }};

            function pad2(n) { return String(n).padStart(2, '0'); }
            function fmtDate(d) { return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate()); }
            function fmtDateTime(d) { return fmtDate(d) + 'T' + pad2(d.getHours()) + ':' + pad2(d.getMinutes()); }
            function addDays(d, n) { var x = new Date(d.getTime()); x.setDate(x.getDate() + n); return x; }
            function parseLocalDate(str) { var p = str.split('-'); return new Date(p[0], p[1] - 1, p[2]); }
            function colorWithAlpha(hex, alpha) {
                if (typeof hex !== 'string') { return hex; }
                if (/^#[0-9a-f]{3}$/i.test(hex)) {
                    hex = '#' + hex[1] + hex[1] + hex[2] + hex[2] + hex[3] + hex[3];
                }
                return /^#[0-9a-f]{6}$/i.test(hex) ? hex + alpha : hex;
            }
            function toast(icon, msg) {
                if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                    Swal.fire({ icon: icon, text: msg, toast: true, position: 'top-end', showConfirmButton: false, timer: 2600, timerProgressBar: true });
                }
            }

            // Persiste mover/redimensionar (drag & drop) via AJAX
            function persistEventMove(info) {
                var ev = info.event;
                var isTracker = ev.extendedProps.type === 'tracker';
                var realId = isTracker ? ev.id.replace('tracker-', '') : ev.id;
                var url = moveUrlTemplate.replace('__ID__', realId);

                var inicio, fim;
                if (ev.allDay) {
                    // Em eventos allDay o "end" do FullCalendar é exclusivo (+1 dia)
                    inicio = fmtDate(ev.start);
                    var endExclusive = ev.end ? ev.end : addDays(ev.start, 1);
                    fim = fmtDate(addDays(endExclusive, -1));
                    if (fim < inicio) { fim = inicio; }
                } else {
                    inicio = fmtDateTime(ev.start);
                    fim = ev.end ? fmtDateTime(ev.end) : inicio;
                }

                fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ type: isTracker ? 'tracker' : 'alocacao', inicio: inicio, fim: fim })
                }).then(function(resp) {
                    if (!resp.ok) { throw new Error('HTTP ' + resp.status); }
                    return resp.json();
                }).then(function() {
                    // Mantém a lista local em sync para o filtro/refetch não perder a nova posição
                    var raw = rawEvents.find(function(e) { return e.id.toString() === ev.id.toString(); });
                    if (raw) {
                        if (ev.allDay) {
                            raw.start = inicio;
                            raw.end = fmtDate(addDays(parseLocalDate(fim), 1));
                        } else {
                            raw.start = fmtDateTime(ev.start);
                            raw.end = ev.end ? fmtDateTime(ev.end) : null;
                        }
                    }
                    toast('success', 'Registro movido com sucesso.');
                }).catch(function() {
                    info.revert();
                    toast('error', 'Não foi possível mover o registro.');
                });
            }

            var isMobileInit = window.innerWidth < 768;
            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'pt-br',
                initialView: isMobileInit ? 'timeGridDay' : 'timeGridWeek',
                slotMinTime: '05:00:00',
                slotMaxTime: '23:00:00',
                height: isMobileInit ? 540 : 860,
                expandRows: true,
                scrollTime: '07:00:00',
                eventMinHeight: 42,
                allDaySlot: true, // Habilitar a linha de Planilha/All-Day no topo
                allDayText: 'PLANILHA', // Alinha com o texto da imagem do Clockify
                nowIndicator: true,
                navLinks: true, // Clique no dia abre a visão diária (bom no mobile)
                stickyHeaderDates: true,
                dayMaxEvents: 3,
                eventDisplay: 'block',
                editable: canCalMove,
                eventStartEditable: canCalMove,
                eventDurationEditable: canCalMove,
                eventResizableFromStart: canCalMove,
                selectable: canCalStore,
                eventLongPressDelay: 400, // segurar ~0.4s p/ arrastar no touch
                selectLongPressDelay: 500,
                views: {
                    timeGridThreeDay: {
                        type: 'timeGrid',
                        duration: { days: 3 },
                        buttonText: '3 dias',
                        height: isMobileInit ? 640 : 1100
                    },
                    timeGridDay: {
                        type: 'timeGrid',
                        height: isMobileInit ? 700 : 1200
                    },
                    dayGridMonth: { fixedWeekCount: false }
                },
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: isMobileInit
                        ? 'timeGridDay,timeGridWeek,dayGridMonth'
                        : 'timeGridWeek,timeGridThreeDay,timeGridDay,dayGridMonth'
                },
                // Alocações (dia inteiro) não podem virar horário; trackers não podem virar dia inteiro
                eventAllow: function(dropInfo, draggedEvent) {
                    var isTracker = draggedEvent.extendedProps.type === 'tracker';
                    return isTracker ? !dropInfo.allDay : dropInfo.allDay;
                },
                eventDrop: function(info) {
                    closePopover();
                    persistEventMove(info);
                },
                eventResize: function(info) {
                    closePopover();
                    persistEventMove(info);
                },
                dateClick: function(info) {
                    if (!canCalStore) { return; }
                    // Clique no número do dia (navLink) deve navegar, não abrir o modal
                    if (info.jsEvent && info.jsEvent.target.closest('.fc-daygrid-day-number')) {
                        return;
                    }
                    const dateStr = info.dateStr.split('T')[0];
                    document.getElementById('modal-data-inicio').value = dateStr;
                    document.getElementById('modal-data-fim').value = dateStr;
                    openAddAllocationModalDirect();
                },
                select: function(info) {
                    if (!canCalStore) { calendar.unselect(); return; }
                    // Arrastar para selecionar um intervalo abre o modal já preenchido
                    var startStr = info.startStr.split('T')[0];
                    var endDate = info.allDay ? addDays(info.end, -1) : info.end;
                    document.getElementById('modal-data-inicio').value = startStr;
                    document.getElementById('modal-data-fim').value = fmtDate(endDate);
                    openAddAllocationModalDirect();
                    calendar.unselect();
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();

                    const rect = info.el.getBoundingClientRect();
                    const scrollX = window.scrollX || document.documentElement.scrollLeft;
                    const scrollY = window.scrollY || document.documentElement.scrollTop;
                    const popWidth = Math.min(310, window.innerWidth - 24);

                    // Mantém o popover sempre dentro da viewport
                    const leftPos = Math.max(8, Math.min(rect.left + scrollX - 30, scrollX + window.innerWidth - popWidth - 8));
                    popoverEl.style.left = leftPos + 'px';
                    popoverEl.style.top = (rect.bottom + scrollY + 5) + 'px';
                    popoverEl.classList.remove('d-none');

                    const ev = info.event;
                    const type = ev.extendedProps.type;
                    const typeBadge = document.getElementById('popover-event-type-badge');
                    
                    if (type === 'tracker') {
                        typeBadge.className = 'ck-pop-badge';
                        typeBadge.textContent = 'Tempo';
                        document.getElementById('popover-delete-form').action = deleteTrackerUrlTemplate.replace('__ID__', ev.id.replace('tracker-', ''));
                    } else {
                        typeBadge.className = 'ck-pop-badge';
                        typeBadge.textContent = 'Alocação';
                        document.getElementById('popover-delete-form').action = deleteCalUrlTemplate.replace('__ID__', ev.id);
                    }
                    document.getElementById('popover-delete-form').style.display =
                        (type === 'tracker' ? canTrackerDestroy : canCalDestroy) ? '' : 'none';

                    const projColor = ev.extendedProps.project_color || '#0284c7';
                    const headerEl = document.getElementById('popover-header');
                    headerEl.style.setProperty('--ck-pop-color', projColor);

                    document.getElementById('popover-event-project').textContent = ev.extendedProps.project_name || 'Sem Projeto';
                    document.getElementById('popover-event-title').textContent = ev.title;
                    document.getElementById('popover-event-user').textContent = ev.extendedProps.user_name;

                    let timeStr = '';
                    if (ev.start && ev.end) {
                        const startH = ev.start.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                        const endH = ev.end.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                        const diffMs = ev.end - ev.start;
                        const diffHrs = (diffMs / (1000 * 60 * 60)).toFixed(1);
                        timeStr = `${diffHrs}h (${startH} - ${endH})`;
                    } else if (ev.start) {
                        const startH = ev.start.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                        timeStr = `Em andamento (Início: ${startH})`;
                    }
                    document.getElementById('popover-event-time').textContent = timeStr;
                },
                events: function(info, successCallback, failureCallback) {
                    var filter = document.getElementById('calendar-user-filter').value;
                    var filtered = rawEvents.filter(function(ev) {
                        if (filter === 'all') return true;
                        var eventUserId = ev.extendedProps.user_id;
                        if (filter === 'current') {
                            return eventUserId == currentUserId;
                        } else if (filter.startsWith('user-')) {
                            var targetId = filter.split('-')[1];
                            return eventUserId == targetId;
                        }
                        return true;
                    });
                    successCallback(filtered);
                },
                dayHeaderContent: function(arg) {
                    const dateObj = arg.date;
                    const year = dateObj.getFullYear();
                    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                    const day = String(dateObj.getDate()).padStart(2, '0');
                    const dateStr = `${year}-${month}-${day}`;

                    let daySeconds = 0;
                    rawEvents.forEach(function(ev) {
                        if (ev.extendedProps.type === 'tracker' && ev.start && ev.end) {
                            const evStartStr = ev.start.slice(0, 10);
                            if (evStartStr === dateStr) {
                                daySeconds += (new Date(ev.end) - new Date(ev.start)) / 1000;
                            }
                        }
                    });

                    const hrs = Math.floor(daySeconds / 3600);
                    const mins = Math.floor((daySeconds % 3600) / 60);
                    const formatted = hrs + ':' + (mins < 10 ? '0' + mins : mins);
                    const weekday = dateObj.toLocaleDateString('pt-BR', { weekday: 'short' }).replace('.', '');
                    const dayNum = dateObj.getDate();

                    let container = document.createElement('div');
                    container.style.textAlign = 'center';
                    container.style.lineHeight = '1.25';
                    container.innerHTML = `
                        <div style="font-size: 11px; text-transform: capitalize; font-weight: 700; color: #475569;">${weekday}, ${dayNum}</div>
                        <div style="font-size: 10px; font-weight: 600; color: #94a3b8; margin-top: 2px;">${formatted}</div>
                    `;
                    return { domNodes: [container] };
                },
                eventContent: function(arg) {
                    let type = arg.event.extendedProps.type;
                    let projColor = arg.event.extendedProps.project_color || '#94a3b8';

                    if (arg.event.allDay) {
                        let div = document.createElement('div');
                        div.className = 'ck-aloc-pill';
                        // Vars ficam no próprio elemento (CSS var não sobe para o .fc-h-event pai)
                        div.style.setProperty('--proj-color', projColor);
                        div.style.setProperty('--proj-soft', colorWithAlpha(projColor, '26'));
                        div.innerHTML = `<strong>${arg.event.extendedProps.project_name || 'Sem Projeto'}</strong> · ${arg.event.extendedProps.hours || 8}h/dia`;
                        return { domNodes: [div] };
                    }

                    const name = arg.event.extendedProps.user_name || 'Usuário';
                    const nameParts = name.split(' ');
                    const initials = nameParts[0].substring(0, 1).toUpperCase() + (nameParts[1] ? nameParts[1].substring(0, 1).toUpperCase() : '');

                    let hash = 0;
                    for (let i = 0; i < name.length; i++) {
                        hash = name.charCodeAt(i) + ((hash << 5) - hash);
                    }
                    const colors = ['#0891b2', '#059669', '#4f46e5', '#d97706', '#ec4899', '#84cc16', '#e11d48'];
                    const avatarColor = colors[Math.abs(hash) % colors.length];

                    let durationStr = '';
                    if (arg.event.start && arg.event.end) {
                        const diffMs = arg.event.end - arg.event.start;
                        const diffHrs = Math.floor(diffMs / 3600000);
                        const diffMins = Math.floor((diffMs % 3600000) / 60000);
                        durationStr = `${diffHrs}h` + (diffMins > 0 ? ` ${diffMins}m` : '');
                    }

                    let card = document.createElement('div');
                    card.className = `ck-event-card type-${type}`;
                    card.style.setProperty('--ck-proj-color', projColor);
                    card.style.setProperty('--ck-proj-bg', colorWithAlpha(projColor, '26'));
                    
                    card.innerHTML = `
                        <div class="ck-event-project">${arg.event.extendedProps.project_name || 'Sem Projeto'}</div>
                        <div class="ck-event-title" title="${arg.event.title}">${arg.event.title}</div>
                        <div class="ck-event-footer">
                            <div class="ck-event-avatar" style="background:${avatarColor};">${initials}</div>
                            <span class="ck-event-user">${name}</span>
                            <span class="ck-event-duration">${durationStr}</span>
                        </div>
                    `;
                    return { domNodes: [card] };
                }
            });

            calendar.render();

            document.getElementById('calendar-user-filter').addEventListener('change', function() {
                calendar.refetchEvents();
                closePopover();
            });

            // Close popover when clicking outside
            document.addEventListener('mousedown', function(e) {
                if (popoverEl && !popoverEl.contains(e.target) && !e.target.closest('.fc-event')) {
                    closePopover();
                }
            });

            // Fecha o popover quando a grade do calendário rola (o evento sai debaixo dele)
            var calScroller = calendarEl.querySelector('.fc-scroller');
            if (calScroller) {
                calScroller.addEventListener('scroll', closePopover);
            }
            document.getElementById('full-calendar').addEventListener('scroll', closePopover, true);
        });
    </script>
@stop
