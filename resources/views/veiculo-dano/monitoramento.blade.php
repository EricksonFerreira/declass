@extends('adminlte::page')

@section('title', 'Monitoramento de Avarias')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb ?? [['label' => 'Avarias'], ['label' => 'Monitoramento']]" />
@stop

@section('content')
<div class="container-fluid px-0">

    {{-- HERO HEADER --}}
    <div class="module-card mb-4">
        <div class="module-card-header">
            <div class="module-header-left">
                <div class="module-header-icon">
                    <i class="fas fa-car-crash"></i>
                </div>
                <div>
                    <div class="module-header-eyebrow">Gestao de Frota</div>
                    <h1 class="module-header-title">Monitoramento de Avarias</h1>
                    <p class="module-header-sub">Acompanhe e gerencie todas as avarias registradas nos veiculos</p>
                </div>
            </div>
        </div>
        <div class="module-about">
            <div class="module-about-icon"><i class="fas fa-info-circle"></i></div>
            <div class="module-about-content">
                <strong>Avarias e Correcoes</strong>
                <p>Centraliza todas as avarias registradas nos veiculos durante o processo de check-in. Aqui voce pode acompanhar o status de cada avaria (pendente ou corrigida), registrar correcoes realizadas e abrir Ordens de Servico vinculadas para controle de manutencao.</p>
            </div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="config-card mb-4">
        <div class="config-block">
            <div class="filter-tabs">
                <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'pendentes']) }}"
                   class="filter-tab {{ ($filtro ?? 'pendentes') === 'pendentes' ? 'active' : '' }}">
                    <i class="fas fa-exclamation-circle mr-1"></i>Pendentes
                </a>
                <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'corrigidos']) }}"
                   class="filter-tab {{ ($filtro ?? '') === 'corrigidos' ? 'active' : '' }}">
                    <i class="fas fa-check-circle mr-1"></i>Corrigidos
                </a>
                <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'todos']) }}"
                   class="filter-tab {{ ($filtro ?? '') === 'todos' ? 'active' : '' }}">
                    <i class="fas fa-list mr-1"></i>Todos
                </a>
            </div>
        </div>
    </div>

    {{-- TABELA --}}
    <div class="config-card">
        <div class="config-block">
            <div class="config-block-label">
                <i class="fas fa-table"></i>
                <div>
                    <strong>Lista de Avarias</strong>
                    <span>{{ $danos->total() }} registro(s) encontrado(s)</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover xp-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Veiculo</th>
                            <th>Reserva</th>
                            <th>Local</th>
                            <th>Tipo</th>
                            <th>Observacoes</th>
                            <th>Correcao</th>
                            <th>Acao</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($danos as $dano)
                            <tr>
                                <td><span class="xp-id">#{{ $dano->id }}</span></td>
                                <td>
                                    <a href="{{ route('veiculo.show', $dano->veiculo_id) }}" class="xp-link">
                                        <i class="fas fa-car mr-1"></i>{{ optional($dano->veiculo)->placa ?? $dano->veiculo_id }}
                                    </a>
                                </td>
                                <td>
                                    @if($dano->reserva_id)
                                        <a href="{{ route('reserva.show', $dano->reserva_id) }}" class="xp-link">
                                            <i class="fas fa-calendar-alt mr-1"></i>#{{ $dano->reserva_id }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $dano->local ?? '—' }}</td>
                                <td>{{ $dano->tipo ?? '—' }}</td>
                                <td class="obs-cell">{{ $dano->observacoes ?? '—' }}</td>
                                <td>
                                    @if($dano->correcao)
                                        <span class="status-badge status-badge--green"><i class="fas fa-check mr-1"></i>Corrigido</span>
                                    @else
                                        <span class="status-badge status-badge--amber"><i class="fas fa-clock mr-1"></i>Pendente</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$dano->correcao)
                                        <button type="button"
                                            class="btn-xp btn-xp--sm btn-registrar-correcao"
                                            data-id="{{ $dano->id }}"
                                            data-veiculo="{{ optional($dano->veiculo)->placa ?? $dano->veiculo_id }}"
                                            data-create-os-url="{{ empty($dano->os_aberta) ? route('ordem-servico.create', ['veiculo_id' => $dano->veiculo_id, 'dano_ids' => [$dano->id]]) : '' }}"
                                            data-os-url="{{ !empty($dano->os_aberta) ? route('ordem-servico.show', $dano->os_aberta) : '' }}">
                                            <i class="fas fa-wrench mr-1"></i>Registrar correcao
                                        </button>
                                    @else
                                        <span class="text-muted" style="font-size:0.8rem;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-check-double"></i>
                                        <p>Nenhuma avaria encontrada para o filtro selecionado.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($danos->hasPages())
                <div class="mt-3">
                    {{ $danos->appends(['filtro' => $filtro ?? 'pendentes'])->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL DE CORRECAO --}}
<div class="modal fade" id="modalCorrecao" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content xp-modal">
            <div class="xp-modal-header">
                <div class="xp-modal-icon"><i class="fas fa-wrench"></i></div>
                <div>
                    <h5 class="xp-modal-title">Registrar Correcao</h5>
                    <p class="xp-modal-sub">Veiculo: <strong id="modalVeiculoNome">—</strong></p>
                </div>
                <button type="button" class="xp-modal-close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="xp-modal-body">
                <label for="correcaoTexto">Descricao da correcao realizada</label>
                <textarea id="correcaoTexto" class="form-control xp-textarea" rows="4" placeholder="Descreva a correcao realizada..."></textarea>
            </div>
            <div class="xp-modal-footer">
                <button type="button" class="btn-xp btn-xp--outline" data-dismiss="modal"><i class="fas fa-times mr-1"></i>Cancelar</button>
                <button type="button" class="btn-xp" id="btnSalvarCorrecao"><i class="fas fa-save mr-1"></i>Salvar Correcao</button>
            </div>
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

    /* MODULE CARD */
    .module-card { border-radius: 20px; overflow: hidden; background: var(--c-card); box-shadow: var(--shadow-lg); border: 1px solid var(--c-border); }
    .module-card-header {
        position: relative;
        background: linear-gradient(135deg, var(--c-ink) 0%, var(--c-navy) 58%, color-mix(in srgb, var(--c-sky) 42%, var(--c-navy) 58%) 100%);
        padding: 1.75rem 2rem; display: flex; align-items: center; gap: 16px; overflow: hidden;
    }
    .module-card-header::before {
        content: ''; position: absolute; inset: 0;
        background-image: radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px);
        background-size: 20px 20px; pointer-events: none;
    }
    .module-card-header::after {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, var(--c-indigo), var(--c-sky), var(--c-green));
    }
    .module-header-left { position: relative; display: flex; align-items: center; gap: 16px; z-index: 1; }
    .module-header-icon {
        width: 56px; height: 56px; border-radius: 14px;
        background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.18);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; color: rgba(255,255,255,0.85); flex-shrink: 0; backdrop-filter: blur(8px);
    }
    .module-header-eyebrow { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: rgba(148,163,184,0.8); margin-bottom: 3px; }
    .module-header-title { font-size: 1.2rem; font-weight: 800; color: #fff; margin: 0 0 3px; letter-spacing: -0.02em; }
    .module-header-sub { font-size: 0.78rem; color: rgba(148,163,184,0.75); margin: 0; }

    /* ABOUT */
    .module-about { display: flex; gap: 14px; padding: 1.25rem 2rem; background: color-mix(in srgb, var(--c-sky) 5%, #ffffff 95%); border-top: 1px solid var(--c-border); align-items: flex-start; }
    .module-about-icon { width: 36px; height: 36px; border-radius: 10px; background: color-mix(in srgb, var(--c-indigo) 12%, #ffffff 88%); display: flex; align-items: center; justify-content: center; color: var(--c-indigo); font-size: 1rem; flex-shrink: 0; margin-top: 2px; }
    .module-about-content strong { display: block; font-size: 0.9rem; color: #1e293b; margin-bottom: 4px; }
    .module-about-content p { font-size: 0.82rem; color: var(--c-slate); margin: 0; line-height: 1.6; }

    /* CONFIG CARD */
    .config-card { border-radius: 18px; overflow: hidden; background: var(--c-card); box-shadow: var(--shadow-sm); border: 1px solid var(--c-border); }
    .config-block { padding: 1.5rem; border-left: 3px solid var(--c-indigo); margin: 1.25rem; background: color-mix(in srgb, var(--c-sky) 4%, #ffffff 96%); border-radius: 0 12px 12px 0; }
    .config-block-label { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 1rem; }
    .config-block-label > i { margin-top: 3px; color: var(--c-indigo); font-size: 1rem; }
    .config-block-label strong { display: block; font-size: 0.9rem; color: #1e293b; }
    .config-block-label span { font-size: 0.78rem; color: var(--c-slate); }

    /* FILTERS */
    .filter-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .filter-tab { display: inline-flex; align-items: center; padding: 7px 16px; border-radius: 8px; font-size: 0.82rem; font-weight: 600; color: var(--c-slate); background: #fff; border: 1px solid var(--c-border); text-decoration: none; transition: all 0.2s; }
    .filter-tab:hover { color: var(--c-indigo); border-color: var(--c-indigo); text-decoration: none; }
    .filter-tab.active { background: linear-gradient(135deg, var(--c-indigo) 0%, var(--c-sky) 100%); color: #fff; border-color: transparent; box-shadow: 0 4px 12px color-mix(in srgb, var(--c-indigo) 28%, transparent 72%); }

    /* TABLE */
    .xp-table { margin: 0; font-size: 0.83rem; }
    .xp-table thead th { background: color-mix(in srgb, var(--c-sky) 10%, #f8fafc 90%); color: var(--c-ink); font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--c-border); padding: 10px 12px; }
    .xp-table tbody td { padding: 10px 12px; vertical-align: middle; border-color: #eef2f9; }
    .xp-table tbody tr:hover { background: #f8faff; }
    .xp-id { font-weight: 700; color: var(--c-slate); font-size: 0.78rem; }
    .xp-link { color: var(--c-indigo); font-weight: 600; text-decoration: none; font-size: 0.82rem; }
    .xp-link:hover { color: #3730a3; text-decoration: underline; }
    .obs-cell { max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* BADGES */
    .status-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 0.73rem; font-weight: 700; }
    .status-badge--green { background: #d1fae5; color: #065f46; }
    .status-badge--amber { background: #fef3c7; color: #92400e; }
    .status-badge--blue  { background: #dbeafe; color: #1e40af; }
    .status-badge--gray  { background: #f1f5f9; color: #475569; }

    /* BUTTONS */
    .btn-xp { display: inline-flex; align-items: center; padding: 8px 18px; border-radius: 8px; background: linear-gradient(135deg, var(--c-indigo) 0%, var(--c-sky) 100%); color: #fff; font-size: 0.82rem; font-weight: 700; border: none; cursor: pointer; transition: opacity 0.2s, transform 0.2s; box-shadow: 0 4px 12px color-mix(in srgb, var(--c-indigo) 30%, transparent 70%); text-decoration: none; }
    .btn-xp:hover { opacity: 0.9; transform: translateY(-1px); color: #fff; text-decoration: none; }
    .btn-xp--sm { padding: 5px 12px; font-size: 0.76rem; }
    .btn-xp--outline { background: transparent; color: var(--c-indigo); border: 2px solid var(--c-indigo); box-shadow: none; }
    .btn-xp--outline:hover { background: color-mix(in srgb, var(--c-indigo) 8%, #ffffff 92%); color: var(--c-indigo); }
    .action-stack { display: flex; flex-direction: column; align-items: flex-start; gap: 6px; }

    /* EMPTY STATE */
    .empty-state { display: flex; flex-direction: column; align-items: center; gap: 10px; color: var(--c-slate); padding: 1rem; }
    .empty-state i { font-size: 2.5rem; opacity: 0.35; }
    .empty-state p { margin: 0; font-size: 0.85rem; }

    /* MODAL */
    .xp-modal { border-radius: 18px; overflow: hidden; border: 1px solid var(--c-border); }
    .xp-modal-header { position: relative; background: linear-gradient(135deg, var(--c-ink) 0%, var(--c-navy) 58%, color-mix(in srgb, var(--c-sky) 42%, var(--c-navy) 58%) 100%); padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 12px; overflow: hidden; }
    .xp-modal-header::before { content: ''; position: absolute; inset: 0; background-image: radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px); background-size: 20px 20px; pointer-events: none; }
    .xp-modal-header::after { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--c-indigo), var(--c-sky), var(--c-green)); }
    .xp-modal-icon { position: relative; z-index: 1; width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; flex-shrink: 0; }
    .xp-modal-title { position: relative; z-index: 1; color: #fff; font-weight: 800; font-size: 1rem; margin: 0 0 2px; }
    .xp-modal-sub { position: relative; z-index: 1; color: rgba(148,163,184,0.8); font-size: 0.78rem; margin: 0; }
    .xp-modal-close { position: relative; z-index: 1; margin-left: auto; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); color: rgba(255,255,255,0.7); border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s; }
    .xp-modal-close:hover { background: rgba(255,255,255,0.2); color: #fff; }
    .xp-modal-body { padding: 1.5rem; }
    .xp-modal-body label { font-size: 0.82rem; font-weight: 700; color: #1e293b; margin-bottom: 6px; display: block; }
    .xp-textarea { border-radius: 10px; border: 1px solid var(--c-border); font-size: 0.85rem; resize: vertical; transition: border-color 0.2s; }
    .xp-textarea:focus { border-color: var(--c-indigo); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c-indigo) 18%, transparent 82%); }
    .xp-modal-footer { padding: 1rem 1.5rem; background: #f8faff; border-top: 1px solid var(--c-border); display: flex; justify-content: flex-end; gap: 10px; }
</style>
@stop

@section('js')
<script>
(function () {
    'use strict';

    var danoIdAtual = null;

    document.querySelectorAll('.btn-registrar-correcao').forEach(function (btn) {
        var wrapper = document.createElement('div');
        wrapper.className = 'action-stack';

        var parent = btn.parentNode;
        parent.insertBefore(wrapper, btn);
        wrapper.appendChild(btn);

        var osUrl = btn.dataset.osUrl || '';
        var createOsUrl = btn.dataset.createOsUrl || '';
        var targetUrl = osUrl || createOsUrl;

        if (targetUrl) {
            var osLink = document.createElement('a');
            osLink.href = targetUrl;
            osLink.className = 'btn-xp btn-xp--sm btn-xp--outline';
            osLink.innerHTML = osUrl
                ? '<i class="fas fa-clipboard-check mr-1"></i>Ver OS'
                : '<i class="fas fa-tools mr-1"></i>Abrir OS';
            wrapper.appendChild(osLink);
        }

        btn.addEventListener('click', function () {
            danoIdAtual = this.dataset.id;
            document.getElementById('modalVeiculoNome').textContent = this.dataset.veiculo || '—';
            document.getElementById('correcaoTexto').value = '';
            $('#modalCorrecao').modal('show');
        });
    });

    document.getElementById('btnSalvarCorrecao').addEventListener('click', function () {
        var correcao = document.getElementById('correcaoTexto').value.trim();
        if (!correcao) {
            alert('Por favor, descreva a correcao realizada.');
            return;
        }

        var baseUrl = @json(route('veiculo-dano.correcao', ['id' => '__DANO_ID__']));
        var url = baseUrl.replace('__DANO_ID__', danoIdAtual);
        var token = document.querySelector('meta[name="csrf-token"]');

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ correcao: correcao })
        })
        .then(function (res) {
            if (res.ok) {
                $('#modalCorrecao').modal('hide');
                location.reload();
            } else {
                return res.json().then(function (data) { alert(data.message || 'Erro ao salvar correcao.'); });
            }
        })
        .catch(function () {
            $('#modalCorrecao').modal('hide');
            location.reload();
        });
    });
}());
</script>
@stop
