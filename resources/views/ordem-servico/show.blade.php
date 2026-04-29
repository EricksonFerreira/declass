@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
@php
    $statusSlug = strtolower((string) ($ordem_servico->status ?? 'aberta'));
    $statusClass = match ($statusSlug) {
        \App\Models\OrdemServico::STATUS_CONCLUIDA => 'is-success',
        \App\Models\OrdemServico::STATUS_CANCELADA => 'is-danger',
        \App\Models\OrdemServico::STATUS_EM_ANDAMENTO => 'is-primary',
        default => 'is-warning',
    };
    $statusIcon = match ($statusSlug) {
        \App\Models\OrdemServico::STATUS_CONCLUIDA => 'fas fa-check-circle',
        \App\Models\OrdemServico::STATUS_CANCELADA => 'fas fa-ban',
        \App\Models\OrdemServico::STATUS_EM_ANDAMENTO => 'fas fa-spinner',
        default => 'fas fa-folder-open',
    };
@endphp

<div class="rs-page os-show-page">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rs-hero rs-hero--{{ $statusSlug }}">
        <div class="rs-hero__accent"></div>
        <div class="rs-hero__body">
            <div class="rs-hero__left">
                <div class="rs-hero__eyebrow">
                    <i class="fas fa-tools"></i>
                    Ordem de servico #{{ $ordem_servico->numero ?? $ordem_servico->id }}
                </div>
                <h1 class="rs-hero__title">{{ $titulo }}</h1>
                <div class="rs-hero__meta">
                    <span><i class="fas fa-car"></i>{{ optional($ordem_servico->veiculo)->nome ?? ('Veiculo #' . $ordem_servico->veiculo_id) }}</span>
                    <span><i class="fas fa-user"></i>{{ optional($ordem_servico->usuarioAbertura)->name ?? 'Usuario nao informado' }}</span>
                </div>
            </div>
            <div class="rs-hero__right">
                <span class="rs-status-badge {{ $statusClass }}">
                    <i class="{{ $statusIcon }}"></i>
                    {{ strtoupper(str_replace('_', ' ', $statusSlug)) }}
                </span>
                <div class="rs-hero__kpis">
                    <div class="rs-kpi">
                        <span>Itens</span>
                        <strong>{{ $ordem_servico->itens->count() }}</strong>
                    </div>
                    <div class="rs-kpi-divider"></div>
                    <div class="rs-kpi">
                        <span>Reserva</span>
                        <strong>{{ $ordem_servico->reserva_id ? '#' . $ordem_servico->reserva_id : '-' }}</strong>
                    </div>
                </div>
                <div class="rs-hero__actions">
                    <a class="rs-btn rs-btn--outline" href="{{ route('ordem-servico.pdf', $ordem_servico) }}" target="_blank" rel="noopener noreferrer">
                        <i class="fas fa-file-pdf"></i>Exportar PDF
                    </a>
                    <a class="rs-btn rs-btn--outline" href="{{ route('ordem-servico.index') }}">
                        <i class="fas fa-arrow-left"></i>Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="rs-grid">
        <div class="rs-main">
            <div class="rs-card">
                <div class="rs-card__header">
                    <i class="fas fa-info-circle rs-card__icon"></i>
                    <h2>Dados da OS</h2>
                </div>
                <div class="os-detail-grid">
                    <div class="os-detail">
                        <span>Numero</span>
                        <strong>{{ $ordem_servico->numero ?? '#' . $ordem_servico->id }}</strong>
                    </div>
                    <div class="os-detail">
                        <span>Status</span>
                        <strong>{{ ucfirst(str_replace('_', ' ', $statusSlug)) }}</strong>
                    </div>
                    <div class="os-detail">
                        <span>Veiculo</span>
                        <strong>{{ optional($ordem_servico->veiculo)->nome ?? '-' }}</strong>
                        <small>{{ optional($ordem_servico->veiculo)->placa ?? '-' }}</small>
                    </div>
                    <div class="os-detail">
                        <span>Reserva</span>
                        <strong>{{ $ordem_servico->reserva_id ? '#' . $ordem_servico->reserva_id : '-' }}</strong>
                    </div>
                    <div class="os-detail">
                        <span>Aberta por</span>
                        <strong>{{ optional($ordem_servico->usuarioAbertura)->name ?? '-' }}</strong>
                    </div>
                    @if($ordem_servico->data_conclusao)
                        <div class="os-detail">
                            <span>Concluida em</span>
                            <strong>{{ $ordem_servico->data_conclusao->format('d/m/Y H:i') }}</strong>
                            <small>{{ optional($ordem_servico->usuarioEncerramento)->name ?? '-' }}</small>
                        </div>
                    @endif
                </div>

                @if($ordem_servico->observacoes)
                    <div class="os-note-box">
                        <h3>Observacoes</h3>
                        <p>{{ $ordem_servico->observacoes }}</p>
                    </div>
                @endif

                @if($ordem_servico->relatorio_conclusao)
                    <div class="os-note-box">
                        <h3>Relatorio geral</h3>
                        <p>{{ $ordem_servico->relatorio_conclusao }}</p>
                    </div>
                @endif

                @if($ordem_servico->anexos->isNotEmpty())
                    <div class="os-note-box">
                        <h3>Anexos</h3>
                        <ul class="os-attachments mb-0">
                            @foreach($ordem_servico->anexos as $anexo)
                                @php
                                    $nome = $anexo->original_name ?: basename((string) $anexo->path);
                                @endphp
                                <li class="os-attachment">
                                    <a href="{{ asset('storage/' . $anexo->path) }}" target="_blank" rel="noopener noreferrer">
                                        <i class="fas fa-paperclip mr-1"></i>{{ $nome }}
                                    </a>
                                    <small class="text-muted d-block">
                                        {{ $anexo->created_at?->format('d/m/Y H:i') ?? '' }}
                                        @if($anexo->usuario)
                                            &middot; {{ $anexo->usuario->name }}
                                        @endif
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="rs-card">
                <div class="rs-card__header rs-card__header--between">
                    <div class="rs-card__header-left">
                        <i class="fas fa-list-ul rs-card__icon"></i>
                        <h2>Itens do servico</h2>
                    </div>
                    <span class="rs-card__badge">{{ $ordem_servico->itens->count() }} item(s)</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">#</th>
                                <th>Descricao</th>
                                <th>Vinculo com avaria</th>
                                <th>Resultado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ordem_servico->itens as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->descricao_servico }}</td>
                                    <td>
                                        @if($item->veiculo_dano_id)
                                            <span class="badge badge-warning">Avaria #{{ $item->veiculo_dano_id }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->resultado ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Nenhum item registrado nesta OS.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <aside class="rs-sidebar">
            @if($ordem_servico->podeAlterarStatus())
                <div class="rs-card">
                    <div class="rs-card__header">
                        <i class="fas fa-exchange-alt rs-card__icon"></i>
                        <h2>Alterar status</h2>
                    </div>

                    @if($ordem_servico->status === \App\Models\OrdemServico::STATUS_ABERTA)
                        <form method="post" action="{{ route('ordem-servico.status', $ordem_servico) }}" class="mb-2">
                            @csrf
                            <input type="hidden" name="status" value="{{ \App\Models\OrdemServico::STATUS_EM_ANDAMENTO }}">
                            <button type="submit" class="rs-btn rs-btn--primary rs-btn--full">
                                <i class="fas fa-play"></i>Marcar em andamento
                            </button>
                        </form>
                    @endif

                    <form method="post" action="{{ route('ordem-servico.status', $ordem_servico) }}"
                        onsubmit="return confirm('Confirma o cancelamento desta OS?');">
                        @csrf
                        <input type="hidden" name="status" value="{{ \App\Models\OrdemServico::STATUS_CANCELADA }}">
                        <button type="submit" class="rs-btn rs-btn--outline rs-btn--full">
                            <i class="fas fa-ban"></i>Cancelar OS
                        </button>
                    </form>
                </div>
            @endif

            @if($ordem_servico->podeConcluir())
                <div class="rs-card">
                    <div class="rs-card__header">
                        <i class="fas fa-check-double rs-card__icon"></i>
                        <h2>Concluir OS</h2>
                    </div>

                    <form method="post" action="{{ route('ordem-servico.concluir', $ordem_servico) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="relatorio_geral">Relatorio geral</label>
                            <textarea name="relatorio_geral" id="relatorio_geral"
                                class="form-control form-control-sm @error('relatorio_geral') is-invalid @enderror"
                                rows="4" required minlength="3"
                                placeholder="Resumo do que foi executado">{{ old('relatorio_geral') }}</textarea>
                            @error('relatorio_geral')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($ordem_servico->itens->isNotEmpty())
                            <div class="os-item-results">
                                <label class="font-weight-bold d-block mb-2">Resultado por item</label>
                                @foreach($ordem_servico->itens as $item)
                                    <div class="form-group">
                                        <label for="res_{{ $item->id }}" class="small text-muted mb-1">
                                            Item {{ $loop->iteration }}
                                            @if($item->veiculo_dano_id)
                                                - avaria #{{ $item->veiculo_dano_id }}
                                            @endif
                                        </label>
                                        <textarea name="resultados[{{ $item->id }}]" id="res_{{ $item->id }}"
                                            class="form-control form-control-sm" rows="2"
                                            placeholder="Deixe em branco para usar o relatorio geral">{{ old('resultados.' . $item->id) }}</textarea>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="anexos">Anexos (opcional)</label>
                            <input type="file"
                                name="anexos[]"
                                id="anexos"
                                class="form-control form-control-sm @error('anexos.*') is-invalid @enderror"
                                multiple
                                accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx,.txt">
                            @error('anexos.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">Voce pode anexar varios arquivos (max 10MB cada).</small>
                        </div>

                        <button type="submit" class="rs-btn rs-btn--success rs-btn--full">
                            <i class="fas fa-check"></i>Concluir e registrar correcoes
                        </button>
                    </form>
                </div>
            @endif
        </aside>
    </div>
</div>
@stop

@section('css')
<style>
    .os-show-page {
        max-width: 1400px;
        margin: 0 auto;
    }

    .rs-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .rs-hero {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
        color: #fff;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.16);
    }

    .rs-hero__accent {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--empresa-cor-primaria, #2563eb), var(--empresa-cor-terciaria, #38bdf8));
    }

    .rs-hero__body {
        padding: 24px 28px;
        display: flex;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }

    .rs-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: rgba(255, 255, 255, 0.72);
        margin-bottom: 8px;
    }

    .rs-hero__title {
        margin: 0 0 8px;
        font-size: 30px;
        font-weight: 700;
    }

    .rs-hero__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
    }

    .rs-hero__meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .rs-hero__right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 14px;
    }

    .rs-hero__actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 10px;
    }

    .rs-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .rs-status-badge.is-success { background: rgba(16, 185, 129, .18); color: #bbf7d0; }
    .rs-status-badge.is-danger { background: rgba(239, 68, 68, .18); color: #fecaca; }
    .rs-status-badge.is-warning { background: rgba(245, 158, 11, .18); color: #fde68a; }
    .rs-status-badge.is-primary { background: rgba(59, 130, 246, .18); color: #bfdbfe; }

    .rs-hero__kpis {
        display: flex;
        align-items: stretch;
        gap: 14px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 12px 16px;
    }

    .rs-kpi {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 92px;
    }

    .rs-kpi span {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.7);
    }

    .rs-kpi strong {
        font-size: 17px;
        color: #fff;
    }

    .rs-kpi-divider {
        width: 1px;
        background: rgba(255, 255, 255, 0.14);
    }

    .rs-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 18px;
    }

    .rs-main,
    .rs-sidebar {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .rs-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .rs-card__header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid #eef2f7;
    }

    .rs-card__header--between {
        justify-content: space-between;
    }

    .rs-card__header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .rs-card__icon {
        color: var(--empresa-cor-primaria, #2563eb);
    }

    .rs-card__header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }

    .rs-card__badge {
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 700;
    }

    .os-detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        padding: 20px;
    }

    .os-detail {
        padding: 14px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .os-detail span {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
    }

    .os-detail strong {
        color: #0f172a;
        font-size: 15px;
    }

    .os-detail small {
        color: #64748b;
    }

    .os-note-box {
        margin: 0 20px 20px;
        padding: 16px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
    }

    .os-note-box h3 {
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 8px;
        color: #0f172a;
    }

    .os-note-box p {
        margin: 0;
        color: #475569;
        white-space: pre-wrap;
    }

    .os-attachments {
        list-style: none;
        padding-left: 0;
    }

    .os-attachment + .os-attachment {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #e5e7eb;
    }

    .rs-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 12px;
        font-weight: 600;
        border: 1px solid transparent;
        text-decoration: none;
        transition: .2s ease;
    }

    .rs-btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }

    .rs-btn--primary {
        background: linear-gradient(135deg, var(--empresa-cor-primaria, #2563eb), var(--empresa-cor-terciaria, #38bdf8));
        color: #fff;
    }

    .rs-btn--success {
        background: linear-gradient(135deg, #10b981, #34d399);
        color: #fff;
    }

    .rs-btn--outline {
        background: #fff;
        color: #334155;
        border-color: #cbd5e1;
    }

    .rs-btn--full {
        width: 100%;
    }

    .os-item-results {
        margin-bottom: 14px;
        padding-top: 4px;
    }

    .rs-sidebar .rs-card {
        padding: 0 0 18px;
    }

    .rs-sidebar .form-group,
    .rs-sidebar .rs-btn {
        margin-left: 20px;
        margin-right: 20px;
    }

    @media (max-width: 991.98px) {
        .rs-grid {
            grid-template-columns: 1fr;
        }

        .rs-hero__right {
            align-items: flex-start;
        }

        .rs-hero__actions {
            justify-content: flex-start;
        }
    }
</style>
@stop

@push('js')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'ordem-servico-show';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.rs-hero', popover: { title: 'Cabeçalho da OS', description: 'Exibe o número da OS, status atual, veículo vinculado, usuário que abriu e data de criação. Use os botões à direita para exportar em PDF ou voltar à lista.', side: 'bottom' } },
        { element: '.rs-status-badge', popover: { title: 'Status da OS', description: '<b>Aberta</b> (amarelo) — aguardando início. <b>Em andamento</b> (azul) — manutenção em curso. <b>Concluída</b> (verde) — finalizada com relatório. <b>Cancelada</b> (vermelho) — encerrada sem execução.', side: 'left' } },
        { element: '.rs-hero__kpis', popover: { title: 'Indicadores da OS', description: 'Mostra a quantidade de itens (danos + serviços) vinculados à OS e o número da reserva associada ao veículo, quando houver.', side: 'bottom' } },
        { element: '.rs-main .rs-card:nth-child(1)', popover: { title: 'Dados da OS', description: 'Detalhes completos: veículo, placa, data de abertura, data de encerramento (quando concluída), usuário responsável e observações gerais informadas na criação.', side: 'right' } },
        { element: '.rs-main .rs-card:nth-child(2)', popover: { title: 'Itens da OS', description: 'Lista todos os danos e serviços vinculados. Cada item mostra a descrição, o resultado registrado após a conclusão e os anexos de evidência.', side: 'right' } },
        { element: '.rs-sidebar', popover: { title: 'Painel de ações', description: 'Aqui você avança o fluxo da OS: <b>Marcar em andamento</b> → <b>Concluir</b> (com relatório geral e resultados por item) → ou <b>Cancelar</b>. O formulário de conclusão exige relatório e permite anexar arquivos.', side: 'left' } },
        { element: '#relatorio_geral', popover: { title: 'Relatório de conclusão', description: 'Descreva tudo o que foi feito: peças substituídas, procedimentos realizados e condições do veículo após o serviço. Campo obrigatório para concluir a OS.', side: 'top' } },
        { element: '#anexos', popover: { title: 'Anexos da conclusão', description: 'Envie fotos, laudos e documentos que comprovem a execução do serviço. Formatos aceitos: PDF, imagens, Word e Excel. Máximo 10 MB por arquivo.', side: 'top' } },
    ];

    function marcarFeito() {
        localStorage.setItem(CACHE_KEY, '1');
        fetch(CONCLUIR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } }).catch(() => {});
    }

    function getAvailableSteps() {
        return steps.filter(function (step) {
            if (!step || !step.element) return false;

            return String(step.element)
                .split(',')
                .map(function (selector) { return selector.trim(); })
                .filter(Boolean)
                .some(function (selector) {
                    try {
                        return !!document.querySelector(selector);
                    } catch (error) {
                        return false;
                    }
                });
        });
    }

    function buildTour() {
        if (typeof window.driver === 'undefined' || !window.driver.js) return null;

        var availableSteps = getAvailableSteps();
        if (!availableSteps.length) return null;

        var _pulou = false;
        return window.driver.js.driver({
            animate: true, smoothScroll: true, showProgress: true,
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
            steps: availableSteps,
        });
    }

    function startTour() {
        var tour = buildTour();
        if (!tour) return;
        setTimeout(function () { tour.drive(); }, 350);
    }

    window.restartPageTour = function () {
        localStorage.removeItem(CACHE_KEY);
        fetch(RESETAR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
            .catch(() => {})
            .then(() => startTour());
    };

    if (localStorage.getItem(CACHE_KEY) && !forceTour) return;
    fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.feito && !forceTour) { localStorage.setItem(CACHE_KEY, '1'); return; }
            startTour();
        })
        .catch(() => {
            if (!localStorage.getItem(CACHE_KEY) || forceTour) startTour();
        });
})();
</script>
@endpush
