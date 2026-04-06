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
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div>
                    <div class="module-header-eyebrow">Ordens de Servico</div>
                    <h1 class="module-header-title">Nova Ordem de Servico</h1>
                    <p class="module-header-sub">Abra uma OS para registrar e acompanhar manutencoes e reparos</p>
                </div>
            </div>
            <div class="module-header-right">
                <a href="{{ route('ordem-servico.index') }}" class="btn-xp btn-xp--outline-light">
                    <i class="fas fa-arrow-left mr-1"></i>Voltar
                </a>
            </div>
        </div>
        <div class="module-about">
            <div class="module-about-icon"><i class="fas fa-info-circle"></i></div>
            <div class="module-about-content">
                <strong>Ordens de Servico</strong>
                <p>Gerencie todas as manutencoes e reparos dos veiculos. Uma OS pode ser criada manualmente ou a partir de avarias registradas no check-in. Acompanhe o andamento de cada servico, registre resultados por item e conclua a OS para marcar automaticamente as avarias como corrigidas.</p>
            </div>
        </div>
    </div>

    {{-- ERROS --}}
    @if ($errors->any())
        <div class="alert xp-alert-danger mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORMULARIO --}}
    <div class="config-card">
        <form method="post" action="{{ route('ordem-servico.store') }}">
            @csrf

            {{-- Veiculo --}}
            <div class="config-block">
                <div class="config-block-label">
                    <i class="fas fa-car"></i>
                    <div>
                        <strong>Veiculo</strong>
                        <span>Selecione o veiculo para abrir a OS</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <select name="veiculo_id" id="veiculo_id" class="form-control xp-select @error('veiculo_id') is-invalid @enderror" required>
                            <option value="">— Selecione um veiculo —</option>
                            @foreach($veiculos as $v)
                                <option value="{{ $v->id }}" @selected((int) old('veiculo_id', $veiculoId ?? null) === (int) $v->id)>
                                    {{ $v->nome }} ({{ $v->placa ?? $v->id }})
                                </option>
                            @endforeach
                        </select>
                        @error('veiculo_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Avarias pendentes --}}
            <div class="config-block">
                <div class="config-block-label">
                    <i class="fas fa-car-crash"></i>
                    <div>
                        <strong>Avarias pendentes</strong>
                        <span>Avarias sem correcao e sem OS aberta para este veiculo</span>
                    </div>
                </div>
                <div id="danos_container" class="danos-container">
                    <p class="text-muted small mb-0"><i class="fas fa-arrow-up mr-1"></i>Selecione um veiculo para listar as avarias.</p>
                </div>
            </div>

            {{-- Reserva --}}
            <div class="config-block">
                <div class="config-block-label">
                    <i class="fas fa-calendar-alt"></i>
                    <div>
                        <strong>Reserva (opcional)</strong>
                        <span>Informe o ID da reserva se esta OS tiver vinculo com uma reserva</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <input type="number" name="reserva_id" id="reserva_id"
                               class="form-control xp-input @error('reserva_id') is-invalid @enderror"
                               value="{{ old('reserva_id') }}" min="1" placeholder="ID da reserva">
                        @error('reserva_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Observacoes --}}
            <div class="config-block">
                <div class="config-block-label">
                    <i class="fas fa-sticky-note"></i>
                    <div>
                        <strong>Observacoes da OS</strong>
                        <span>Informacoes gerais sobre esta ordem de servico</span>
                    </div>
                </div>
                <textarea name="observacoes" id="observacoes"
                          class="form-control xp-textarea @error('observacoes') is-invalid @enderror"
                          rows="3" placeholder="Descreva o contexto ou observacoes gerais desta OS...">{{ old('observacoes') }}</textarea>
                @error('observacoes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Servicos livres --}}
            <div class="config-block">
                <div class="config-block-label">
                    <i class="fas fa-wrench"></i>
                    <div>
                        <strong>Servicos adicionais</strong>
                        <span>Um servico por linha, sem vinculo com avaria registrada</span>
                    </div>
                </div>
                <textarea name="servicos_livres" id="servicos_livres"
                          class="form-control xp-textarea @error('servicos_livres') is-invalid @enderror"
                          rows="4" placeholder="Ex: Troca de oleo&#10;Revisao geral">{{ old('servicos_livres') }}</textarea>
                @error('servicos_livres')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Acoes --}}
            <div class="form-actions">
                <a href="{{ route('ordem-servico.index') }}" class="btn-xp btn-xp--outline">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </a>
                <button type="submit" class="btn-xp">
                    <i class="fas fa-save mr-1"></i>Abrir ordem de servico
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --c-ink:    #0c1428;
        --c-navy:   #1a2848;
        --c-indigo: #4338ca;
        --c-sky:    #0ea5e9;
        --c-green:  #10b981;
        --c-amber:  #f59e0b;
        --c-red:    #ef4444;
        --c-slate:  #64748b;
        --c-border: #e2e9f4;
        --c-bg:     #f2f5fb;
        --c-card:   #ffffff;
        --shadow-sm: 0 2px 8px rgba(12,20,40,0.07);
        --shadow-lg: 0 16px 48px rgba(12,20,40,0.13);
    }
    .content-wrapper { background: var(--c-bg); }

    .module-card { border-radius: 20px; overflow: hidden; background: var(--c-card); box-shadow: var(--shadow-lg); border: 1px solid var(--c-border); }
    .module-card-header {
        position: relative;
        background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%);
        padding: 1.75rem 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; overflow: hidden;
    }
    .module-card-header::before { content: ''; position: absolute; inset: 0; background-image: radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px); background-size: 20px 20px; pointer-events: none; }
    .module-card-header::after { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #4338ca, #0ea5e9, #10b981); }
    .module-header-left { position: relative; display: flex; align-items: center; gap: 16px; z-index: 1; }
    .module-header-right { position: relative; z-index: 1; }
    .module-header-icon { width: 56px; height: 56px; border-radius: 14px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: rgba(255,255,255,0.85); flex-shrink: 0; backdrop-filter: blur(8px); }
    .module-header-eyebrow { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: rgba(148,163,184,0.8); margin-bottom: 3px; }
    .module-header-title { font-size: 1.2rem; font-weight: 800; color: #fff; margin: 0 0 3px; letter-spacing: -0.02em; }
    .module-header-sub { font-size: 0.78rem; color: rgba(148,163,184,0.75); margin: 0; }

    .module-about { display: flex; gap: 14px; padding: 1.25rem 2rem; background: #f8faff; border-top: 1px solid var(--c-border); align-items: flex-start; }
    .module-about-icon { width: 36px; height: 36px; border-radius: 10px; background: rgba(67,56,202,0.1); display: flex; align-items: center; justify-content: center; color: var(--c-indigo); font-size: 1rem; flex-shrink: 0; margin-top: 2px; }
    .module-about-content strong { display: block; font-size: 0.9rem; color: #1e293b; margin-bottom: 4px; }
    .module-about-content p { font-size: 0.82rem; color: var(--c-slate); margin: 0; line-height: 1.6; }

    .config-card { border-radius: 18px; overflow: hidden; background: var(--c-card); box-shadow: var(--shadow-sm); border: 1px solid var(--c-border); }
    .config-block { padding: 1.5rem; border-left: 3px solid var(--c-indigo); margin: 1.25rem; background: #f8faff; border-radius: 0 12px 12px 0; }
    .config-block-label { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 1rem; }
    .config-block-label > i { margin-top: 3px; color: var(--c-indigo); font-size: 1rem; }
    .config-block-label strong { display: block; font-size: 0.9rem; color: #1e293b; }
    .config-block-label span { font-size: 0.78rem; color: var(--c-slate); }

    .xp-select, .xp-input { border-radius: 10px; border: 1px solid var(--c-border); font-size: 0.85rem; transition: border-color 0.2s; }
    .xp-select:focus, .xp-input:focus { border-color: var(--c-indigo); box-shadow: 0 0 0 3px rgba(67,56,202,0.12); outline: none; }

    .xp-textarea { width: 100%; border-radius: 10px; border: 1px solid var(--c-border); font-size: 0.85rem; resize: vertical; padding: 10px 12px; transition: border-color 0.2s; }
    .xp-textarea:focus { border-color: var(--c-indigo); box-shadow: 0 0 0 3px rgba(67,56,202,0.12); outline: none; }

    .danos-container { border-radius: 10px; border: 1px solid var(--c-border); padding: 1rem; background: #fff; max-height: 220px; overflow-y: auto; font-size: 0.85rem; }

    .btn-xp { display: inline-flex; align-items: center; padding: 8px 18px; border-radius: 8px; background: linear-gradient(135deg, #4338ca 0%, #0ea5e9 100%); color: #fff; font-size: 0.82rem; font-weight: 700; border: none; cursor: pointer; transition: opacity 0.2s, transform 0.2s; box-shadow: 0 4px 12px rgba(67,56,202,0.3); text-decoration: none; }
    .btn-xp:hover { opacity: 0.9; transform: translateY(-1px); color: #fff; text-decoration: none; }
    .btn-xp--outline { background: transparent; color: var(--c-indigo); border: 2px solid var(--c-indigo); box-shadow: none; }
    .btn-xp--outline:hover { background: rgba(67,56,202,0.06); color: var(--c-indigo); }
    .btn-xp--outline-light { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.85); border: 1px solid rgba(255,255,255,0.25); box-shadow: none; }
    .btn-xp--outline-light:hover { background: rgba(255,255,255,0.2); color: #fff; }

    .form-actions { display: flex; justify-content: flex-end; gap: 10px; padding: 1.25rem 1.5rem; border-top: 1px solid var(--c-border); background: #f8faff; }

    .xp-alert-danger { background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 1rem 1.25rem; color: #991b1b; font-size: 0.85rem; display: flex; align-items: flex-start; }
</style>
@stop

@section('js')
<script>
(function () {
    const veiculoEl = document.getElementById('veiculo_id');
    const container = document.getElementById('danos_container');
    const preselected = @json(array_values(array_map('intval', $danoIds ?? [])));
    const danosListUrl = @json(route('ordem-servico.danos-pendentes'));

    function renderDanos(list) {
        container.innerHTML = '';
        if (!list.length) {
            container.innerHTML = '<p class="text-muted small mb-0">Nenhuma avaria pendente disponivel para OS.</p>';
            return;
        }
        list.forEach(function (d) {
            const id = 'dano_cb_' + d.id;
            const wrap = document.createElement('div');
            wrap.className = 'custom-control custom-checkbox mb-1';
            const input = document.createElement('input');
            input.type = 'checkbox';
            input.className = 'custom-control-input';
            input.name = 'veiculo_dano_ids[]';
            input.value = d.id;
            input.id = id;
            if (preselected.indexOf(d.id) !== -1) input.checked = true;
            const label = document.createElement('label');
            label.className = 'custom-control-label';
            label.setAttribute('for', id);
            label.textContent = d.label;
            wrap.appendChild(input);
            wrap.appendChild(label);
            container.appendChild(wrap);
        });
    }

    function loadDanos() {
        const vid = veiculoEl.value;
        if (!vid) {
            container.innerHTML = '<p class="text-muted small mb-0">Selecione um veiculo para listar as avarias.</p>';
            return;
        }
        container.innerHTML = '<p class="text-muted small mb-0">Carregando...</p>';
        const sep = danosListUrl.indexOf('?') >= 0 ? '&' : '?';
        const url = danosListUrl + sep + 'veiculo_id=' + encodeURIComponent(vid);
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(renderDanos)
            .catch(function () {
                container.innerHTML = '<p class="text-danger small mb-0">Erro ao carregar avarias.</p>';
            });
    }

    veiculoEl.addEventListener('change', loadDanos);
    if (veiculoEl.value) {
        loadDanos();
    }
})();
</script>
@stop
