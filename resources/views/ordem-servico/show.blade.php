@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
<div class="container-fluid px-0">

    {{-- HERO HEADER --}}
    <div class="module-card mb-4">
        <div class="module-card-header">
            <div class="module-header-left">
                <div class="module-header-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <div class="module-header-eyebrow">Ordem de Servico</div>
                    <h1 class="module-header-title">{{ $ordem_servico->numero ?? 'OS #' . $ordem_servico->id }}</h1>
                    <p class="module-header-sub">
                        Veiculo:
                        <a href="{{ route('veiculo.show', $ordem_servico->veiculo_id) }}" class="module-header-link">
                            {{ optional($ordem_servico->veiculo)->placa ?? ('#' . $ordem_servico->veiculo_id) }}
                        </a>
                    </p>
                </div>
            </div>
            <div class="module-header-right">
                <a href="{{ route('ordem-servico.index') }}" class="btn-xp btn-xp--outline-light">
                    <i class="fas fa-arrow-left mr-1"></i>Voltar
                </a>
            </div>
        </div>
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="xp-alert xp-alert--success mb-4">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="xp-alert-close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if ($errors->any())
        <div class="xp-alert xp-alert--danger mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        {{-- COLUNA PRINCIPAL --}}
        <div class="col-lg-8">

            {{-- Dados da OS --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Dados da OS</strong>
                            <span>Informacoes gerais desta ordem de servico</span>
                        </div>
                    </div>

                    <div class="os-details-grid">
                        <div class="os-detail-item">
                            <span class="os-detail-label">Numero</span>
                            <span class="os-detail-value font-weight-bold">{{ $ordem_servico->numero ?? '#' . $ordem_servico->id }}</span>
                        </div>
                        <div class="os-detail-item">
                            <span class="os-detail-label">Status</span>
                            <span class="os-detail-value">
                                @if($ordem_servico->status === \App\Models\OrdemServico::STATUS_ABERTA)
                                    <span class="status-badge status-badge--amber"><i class="fas fa-folder-open mr-1"></i>Aberta</span>
                                @elseif($ordem_servico->status === \App\Models\OrdemServico::STATUS_EM_ANDAMENTO)
                                    <span class="status-badge status-badge--blue"><i class="fas fa-spinner mr-1"></i>Em Andamento</span>
                                @elseif($ordem_servico->status === \App\Models\OrdemServico::STATUS_CONCLUIDA)
                                    <span class="status-badge status-badge--green"><i class="fas fa-check-circle mr-1"></i>Concluida</span>
                                @else
                                    <span class="status-badge status-badge--gray"><i class="fas fa-ban mr-1"></i>Cancelada</span>
                                @endif
                            </span>
                        </div>
                        <div class="os-detail-item">
                            <span class="os-detail-label">Veiculo</span>
                            <span class="os-detail-value">
                                <a href="{{ route('veiculo.show', $ordem_servico->veiculo_id) }}" class="xp-link">
                                    <i class="fas fa-car mr-1"></i>{{ optional($ordem_servico->veiculo)->placa ?? ('#' . $ordem_servico->veiculo_id) }}
                                </a>
                            </span>
                        </div>
                        <div class="os-detail-item">
                            <span class="os-detail-label">Reserva</span>
                            <span class="os-detail-value">
                                @if($ordem_servico->reserva_id)
                                    <a href="{{ route('reserva.show', $ordem_servico->reserva_id) }}" class="xp-link">
                                        <i class="fas fa-calendar-alt mr-1"></i>#{{ $ordem_servico->reserva_id }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </span>
                        </div>
                        <div class="os-detail-item">
                            <span class="os-detail-label">Aberta por</span>
                            <span class="os-detail-value">{{ optional($ordem_servico->usuarioAbertura)->name ?? '—' }}</span>
                        </div>
                        @if($ordem_servico->data_conclusao)
                            <div class="os-detail-item">
                                <span class="os-detail-label">Encerramento</span>
                                <span class="os-detail-value">
                                    {{ optional($ordem_servico->usuarioEncerramento)->name ?? '—' }}
                                    <br><small class="text-muted">{{ $ordem_servico->data_conclusao->format('d/m/Y H:i') }}</small>
                                </span>
                            </div>
                        @endif
                        @if($ordem_servico->observacoes)
                            <div class="os-detail-item os-detail-item--full">
                                <span class="os-detail-label">Observacoes</span>
                                <span class="os-detail-value" style="white-space:pre-wrap;">{{ $ordem_servico->observacoes }}</span>
                            </div>
                        @endif
                        @if($ordem_servico->relatorio_conclusao)
                            <div class="os-detail-item os-detail-item--full">
                                <span class="os-detail-label">Relatorio geral</span>
                                <span class="os-detail-value" style="white-space:pre-wrap;">{{ $ordem_servico->relatorio_conclusao }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Itens do servico --}}
            <div class="config-card">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-list-ul"></i>
                        <div>
                            <strong>Itens do servico</strong>
                            <span>{{ $ordem_servico->itens->count() }} item(s) nesta OS</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table xp-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Descricao</th>
                                    <th>Vinculo avaria</th>
                                    <th>Resultado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ordem_servico->itens as $idx => $item)
                                    <tr>
                                        <td><span class="xp-id">{{ $idx + 1 }}</span></td>
                                        <td style="max-width:280px;white-space:normal;">{{ $item->descricao_servico }}</td>
                                        <td>
                                            @if($item->veiculo_dano_id)
                                                <span class="status-badge status-badge--amber"><i class="fas fa-link mr-1"></i>#{{ $item->veiculo_dano_id }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td style="max-width:320px;white-space:normal;">
                                            @if($item->resultado)
                                                {{ $item->resultado }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUNA LATERAL --}}
        <div class="col-lg-4">

            {{-- Alterar status --}}
            @if($ordem_servico->podeAlterarStatus())
                <div class="config-card mb-4">
                    <div class="config-block config-block--blue">
                        <div class="config-block-label">
                            <i class="fas fa-exchange-alt" style="color:var(--c-sky);"></i>
                            <div>
                                <strong>Alterar Status</strong>
                                <span>Avance ou cancele esta OS</span>
                            </div>
                        </div>

                        @if($ordem_servico->status === \App\Models\OrdemServico::STATUS_ABERTA)
                            <form method="post" action="{{ route('ordem-servico.status', $ordem_servico) }}" class="mb-2">
                                @csrf
                                <input type="hidden" name="status" value="{{ \App\Models\OrdemServico::STATUS_EM_ANDAMENTO }}">
                                <button type="submit" class="btn-xp btn-xp--info w-100">
                                    <i class="fas fa-play mr-1"></i>Marcar em andamento
                                </button>
                            </form>
                        @endif

                        <form method="post" action="{{ route('ordem-servico.status', $ordem_servico) }}"
                              onsubmit="return confirm('Confirma o cancelamento desta OS?');">
                            @csrf
                            <input type="hidden" name="status" value="{{ \App\Models\OrdemServico::STATUS_CANCELADA }}">
                            <button type="submit" class="btn-xp btn-xp--danger w-100">
                                <i class="fas fa-ban mr-1"></i>Cancelar OS
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Concluir OS --}}
            @if($ordem_servico->podeConcluir())
                <div class="config-card">
                    <div class="config-block config-block--green">
                        <div class="config-block-label">
                            <i class="fas fa-check-double" style="color:var(--c-green);"></i>
                            <div>
                                <strong>Concluir OS</strong>
                                <span>Finalize e registre as correcoes</span>
                            </div>
                        </div>

                        <p class="xp-hint-text mb-3">Ao concluir, o relatorio geral e obrigatorio. Para itens vinculados a avarias, a correcao sera preenchida com o resultado do item (ou com o relatorio geral se o campo estiver vazio).</p>

                        <form method="post" action="{{ route('ordem-servico.concluir', $ordem_servico) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="xp-form-label" for="relatorio_geral">Relatorio geral <span class="text-danger">*</span></label>
                                <textarea name="relatorio_geral" id="relatorio_geral"
                                          class="form-control xp-textarea @error('relatorio_geral') is-invalid @enderror"
                                          rows="4" required minlength="3"
                                          placeholder="Resumo do que foi executado">{{ old('relatorio_geral') }}</textarea>
                                @error('relatorio_geral')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if($ordem_servico->itens->isNotEmpty())
                                <p class="xp-form-label mb-2">Resultado por item (opcional)</p>
                                @foreach($ordem_servico->itens as $item)
                                    <div class="mb-2">
                                        <label class="xp-label-sm" for="res_{{ $item->id }}">
                                            Item {{ $loop->iteration }}
                                            @if($item->veiculo_dano_id)
                                                <span class="status-badge status-badge--amber ml-1" style="font-size:0.68rem;">avaria #{{ $item->veiculo_dano_id }}</span>
                                            @endif
                                        </label>
                                        <textarea name="resultados[{{ $item->id }}]" id="res_{{ $item->id }}"
                                                  class="form-control xp-textarea" rows="2"
                                                  placeholder="Deixe em branco para usar o relatorio geral">{{ old('resultados.'.$item->id) }}</textarea>
                                    </div>
                                @endforeach
                            @endif

                            <button type="submit" class="btn-xp btn-xp--success w-100 mt-2">
                                <i class="fas fa-check mr-1"></i>Concluir e registrar correcoes
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --c-ink:    color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 78%, #08111f 22%);
        --c-navy:   color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 72%, var(--empresa-cor-secundaria, #334155) 28%);
        --c-indigo: var(--empresa-cor-primaria, #1F2937);
        --c-sky:    var(--empresa-cor-terciaria, #64748B);
        --c-green:  color-mix(in srgb, var(--empresa-cor-secundaria, #334155) 45%, #10b981 55%);
        --c-amber:  #f59e0b;
        --c-red:    #ef4444;
        --c-slate:  color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 82%, #475569 18%);
        --c-border: color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 18%, #d7e0ec 82%);
        --c-bg:     color-mix(in srgb, var(--empresa-cor-secundaria, #334155) 10%, #f5f7fb 90%);
        --c-card:   #ffffff;
        --shadow-sm: 0 2px 8px color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 10%, transparent 90%);
        --shadow-lg: 0 16px 48px color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 16%, transparent 84%);
    }
    .content-wrapper { background: var(--c-bg); }

    .module-card { border-radius: 20px; overflow: hidden; background: var(--c-card); box-shadow: var(--shadow-lg); border: 1px solid var(--c-border); }
    .module-card-header {
        position: relative;
        background: linear-gradient(135deg, var(--c-ink) 0%, var(--c-navy) 58%, color-mix(in srgb, var(--c-sky) 42%, var(--c-navy) 58%) 100%);
        padding: 1.75rem 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; overflow: hidden;
    }
    .module-card-header::before { content: ''; position: absolute; inset: 0; background-image: radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px); background-size: 20px 20px; pointer-events: none; }
    .module-card-header::after { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--c-indigo), var(--c-sky), var(--c-green)); }
    .module-header-left { position: relative; display: flex; align-items: center; gap: 16px; z-index: 1; }
    .module-header-right { position: relative; z-index: 1; }
    .module-header-icon { width: 56px; height: 56px; border-radius: 14px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: rgba(255,255,255,0.85); flex-shrink: 0; backdrop-filter: blur(8px); }
    .module-header-eyebrow { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: rgba(148,163,184,0.8); margin-bottom: 3px; }
    .module-header-title { font-size: 1.2rem; font-weight: 800; color: #fff; margin: 0 0 3px; letter-spacing: -0.02em; }
    .module-header-sub { font-size: 0.78rem; color: rgba(148,163,184,0.75); margin: 0; }
    .module-header-link { color: color-mix(in srgb, var(--c-sky) 68%, #ffffff 32%); text-decoration: none; font-weight: 700; }
    .module-header-link:hover { color: #ffffff; text-decoration: underline; }

    .config-card { border-radius: 18px; overflow: hidden; background: var(--c-card); box-shadow: var(--shadow-sm); border: 1px solid var(--c-border); }
    .config-block { padding: 1.5rem; border-left: 3px solid var(--c-indigo); margin: 1.25rem; background: color-mix(in srgb, var(--c-sky) 4%, #ffffff 96%); border-radius: 0 12px 12px 0; }
    .config-block--blue  { border-left-color: var(--c-sky); background: color-mix(in srgb, var(--c-sky) 8%, #ffffff 92%); }
    .config-block--green { border-left-color: var(--c-green); background: color-mix(in srgb, var(--c-green) 7%, #ffffff 93%); }
    .config-block-label { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 1rem; }
    .config-block-label > i { margin-top: 3px; color: var(--c-indigo); font-size: 1rem; }
    .config-block-label strong { display: block; font-size: 0.9rem; color: #1e293b; }
    .config-block-label span { font-size: 0.78rem; color: var(--c-slate); }

    /* OS detail grid */
    .os-details-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
    .os-detail-item { display: flex; flex-direction: column; gap: 4px; }
    .os-detail-item--full { grid-column: 1 / -1; }
    .os-detail-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--c-slate); }
    .os-detail-value { font-size: 0.88rem; color: #1e293b; }

    /* TABLE */
    .xp-table { margin: 0; font-size: 0.83rem; }
    .xp-table thead th { background: color-mix(in srgb, var(--c-sky) 10%, #f8fafc 90%); color: var(--c-ink); font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--c-border); padding: 10px 12px; }
    .xp-table tbody td { padding: 10px 12px; vertical-align: middle; border-color: #eef2f9; }
    .xp-table tbody tr:hover { background: #f8faff; }
    .xp-id { font-weight: 700; color: var(--c-slate); font-size: 0.78rem; }
    .xp-link { color: var(--c-indigo); font-weight: 600; text-decoration: none; font-size: 0.82rem; }
    .xp-link:hover { color: #3730a3; text-decoration: underline; }

    /* BADGES */
    .status-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 0.73rem; font-weight: 700; }
    .status-badge--green { background: #d1fae5; color: #065f46; }
    .status-badge--amber { background: #fef3c7; color: #92400e; }
    .status-badge--blue  { background: #dbeafe; color: #1e40af; }
    .status-badge--gray  { background: #f1f5f9; color: #475569; }

    /* BUTTONS */
    .btn-xp { display: inline-flex; align-items: center; justify-content: center; padding: 8px 18px; border-radius: 8px; background: linear-gradient(135deg, var(--c-indigo) 0%, var(--c-sky) 100%); color: #fff; font-size: 0.82rem; font-weight: 700; border: none; cursor: pointer; transition: opacity 0.2s, transform 0.2s; box-shadow: 0 4px 12px color-mix(in srgb, var(--c-indigo) 30%, transparent 70%); text-decoration: none; }
    .btn-xp:hover { opacity: 0.9; transform: translateY(-1px); color: #fff; text-decoration: none; }
    .btn-xp--outline-light { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.85); border: 1px solid rgba(255,255,255,0.25); box-shadow: none; }
    .btn-xp--outline-light:hover { background: rgba(255,255,255,0.2); color: #fff; }
    .btn-xp--info    { background: linear-gradient(135deg, var(--c-indigo) 0%, var(--c-sky) 100%); box-shadow: 0 4px 12px color-mix(in srgb, var(--c-indigo) 28%, transparent 72%); }
    .btn-xp--success { background: linear-gradient(135deg, var(--c-green) 0%, var(--c-sky) 100%); box-shadow: 0 4px 12px color-mix(in srgb, var(--c-green) 28%, transparent 72%); }
    .btn-xp--danger  { background: transparent; color: #991b1b; border: 2px solid #fca5a5; box-shadow: none; }
    .btn-xp--danger:hover { background: #fef2f2; color: #991b1b; }
    .w-100 { width: 100%; }

    /* FORM ELEMENTS */
    .xp-textarea { width: 100%; border-radius: 10px; border: 1px solid var(--c-border); font-size: 0.85rem; resize: vertical; padding: 10px 12px; transition: border-color 0.2s; }
    .xp-textarea:focus { border-color: var(--c-indigo); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c-indigo) 18%, transparent 82%); outline: none; }
    .xp-form-label { font-size: 0.82rem; font-weight: 700; color: #1e293b; display: block; }
    .xp-label-sm { font-size: 0.76rem; color: var(--c-slate); font-weight: 600; display: block; margin-bottom: 4px; }
    .xp-hint-text { font-size: 0.78rem; color: var(--c-slate); line-height: 1.6; }

    /* ALERTS */
    .xp-alert { border-radius: 12px; padding: 0.9rem 1.25rem; font-size: 0.85rem; display: flex; align-items: flex-start; position: relative; }
    .xp-alert--success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #065f46; }
    .xp-alert--danger  { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .xp-alert-close { position: absolute; top: 10px; right: 12px; background: none; border: none; font-size: 1.1rem; cursor: pointer; opacity: 0.5; }
    .xp-alert-close:hover { opacity: 1; }
</style>
@stop

@section('js')
@stop
