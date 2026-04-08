@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger col-xl-9 col-lg-11 col-md-12">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        // Agrupa permissões por módulo (prefixo antes do primeiro ponto ou primeira palavra)
        $grupos = [];
        $moduloLabels = [
            'reserva'            => ['label' => 'Reservas',            'icon' => 'fas fa-calendar-check'],
            'usuario'            => ['label' => 'Usuários',            'icon' => 'fas fa-users'],
            'veiculo'            => ['label' => 'Veículos',            'icon' => 'fas fa-car'],
            'estacao'            => ['label' => 'Estações',            'icon' => 'fas fa-building'],
            'multa'              => ['label' => 'Multas',              'icon' => 'fas fa-exclamation-triangle'],
            'calcao'             => ['label' => 'Calção',              'icon' => 'fas fa-shield-alt'],
            'avaria'             => ['label' => 'Avarias',             'icon' => 'fas fa-car-crash'],
            'nfse'               => ['label' => 'NFS-e',               'icon' => 'fas fa-file-invoice'],
            'permissao'          => ['label' => 'Permissões',          'icon' => 'fas fa-key'],
            'tipo_usuario'       => ['label' => 'Tipos de Usuário',    'icon' => 'fas fa-user-tag'],
            'horario_diario'     => ['label' => 'Horários Diários',    'icon' => 'fas fa-clock'],
            'modelo'             => ['label' => 'Modelos',             'icon' => 'fas fa-car-side'],
            'marca'              => ['label' => 'Marcas',              'icon' => 'fas fa-tags'],
            'grupo_frota'        => ['label' => 'Grupos de Frota',     'icon' => 'fas fa-layer-group'],
            'opcional'           => ['label' => 'Opcionais',           'icon' => 'fas fa-plus-circle'],
            'movimentacao'       => ['label' => 'Movimentações',       'icon' => 'fas fa-exchange-alt'],
            'financeiro'         => ['label' => 'Financeiro',          'icon' => 'fas fa-file-invoice-dollar'],
            'dano'               => ['label' => 'Danos',               'icon' => 'fas fa-tools'],
            'caracteristica'     => ['label' => 'Características',     'icon' => 'fas fa-list'],
            'categoria_dano'     => ['label' => 'Categorias de Dano',  'icon' => 'fas fa-folder'],
            'item_checklist'     => ['label' => 'Itens de Checklist',  'icon' => 'fas fa-clipboard-list'],
            'checklist'          => ['label' => 'Checklists',          'icon' => 'fas fa-clipboard-check'],
        ];

        foreach ($permissoes as $permissao) {
            $prefixo = strtolower(explode('.', $permissao->name)[0]);
            // Normaliza prefixos compostos
            if (str_contains($prefixo, 'caracteristica')) $prefixo = 'caracteristica';
            if (str_contains($prefixo, 'categoria_dano')) $prefixo = 'categoria_dano';
            if (str_contains($prefixo, 'item_checklist')) $prefixo = 'item_checklist';
            if (!isset($grupos[$prefixo])) $grupos[$prefixo] = [];
            $grupos[$prefixo][] = $permissao;
        }
        ksort($grupos);

        // Descreve ações padrão
        $acaoDesc = [
            'index'          => 'Listar',
            'show'           => 'Ver detalhes',
            'create'         => 'Criar formulário',
            'store'          => 'Salvar novo',
            'edit'           => 'Editar formulário',
            'update'         => 'Salvar edição',
            'destroy'        => 'Excluir',
            'filtro'         => 'Filtrar lista',
            'removerFiltro'  => 'Remover filtro',
            'setPerPage'     => 'Definir itens/página',
            'autorizar'      => 'Autorizar',
            'capturar'       => 'Capturar valor',
            'liberar'        => 'Liberar/cancelar',
            'visualizar'     => 'Visualizar',
            'cobrar'         => 'Cobrar',
            'cancelar'       => 'Cancelar',
            'checkin'        => 'Realizar check-in',
            'checkout'       => 'Realizar check-out',
            'emitirNfse'     => 'Emitir NFS-e',
            'processarPagamento' => 'Processar pagamento',
        ];
    @endphp

    <div class="col-xl-9 col-lg-11 col-md-12 px-0">
        <div class="card shadow-sm border-0" style="border-radius:14px;overflow:hidden">
            <div class="card-header" style="background:linear-gradient(120deg,#f6f9ff,#edf2ff);border:none;padding:1.1rem 1.5rem">
                <h4 class="mb-0" style="color:#0f2740;font-weight:700">
                    <i class="fas fa-user-tag mr-2 text-primary"></i>{{ $titulo }}
                </h4>
                <p class="text-muted mb-0 mt-1" style="font-size:.88rem">Configure o nome da regra e quais permissões ela concede aos usuários vinculados.</p>
            </div>
            <div class="card-body p-4">
                <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">

                    {{-- Nome e Guard --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-600">Nome da Regra <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="name" name="name"
                                   value="{{ $tipoUsuario->name ?? '' }}" required placeholder="Ex: Atendente, Gerente...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-600">Guard</label>
                            <input type="text" class="form-control form-control-sm" id="guard_name" name="guard_name"
                                   value="{{ $tipoUsuario->guard_name ?? 'web' }}" required>
                        </div>
                    </div>

                    {{-- Seção de Permissões --}}
                    <div class="col-12 mt-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="mb-0" style="font-size:.97rem;font-weight:700;color:#1e3a5f">
                                <i class="fas fa-key mr-1 text-warning"></i> Permissões por Módulo
                            </h5>
                            <div>
                                <button type="button" class="btn btn-xs btn-outline-success mr-1" onclick="toggleAll(true)">
                                    <i class="fas fa-check-square mr-1"></i>Marcar tudo
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="toggleAll(false)">
                                    <i class="fas fa-square mr-1"></i>Desmarcar tudo
                                </button>
                            </div>
                        </div>

                        <div class="permissoes-grupos">
                            @foreach ($grupos as $prefixo => $perms)
                                @php
                                    $meta = $moduloLabels[$prefixo] ?? ['label' => ucfirst(str_replace('_', ' ', $prefixo)), 'icon' => 'fas fa-circle'];
                                    $totalGrupo = count($perms);
                                    $marcados = collect($perms)->where('checked', 'checked')->count();
                                @endphp
                                <div class="perm-grupo" id="grupo_{{ $prefixo }}">
                                    <div class="perm-grupo__header" onclick="toggleGrupo('{{ $prefixo }}')">
                                        <div class="perm-grupo__left">
                                            <i class="{{ $meta['icon'] }}"></i>
                                            <span>{{ $meta['label'] }}</span>
                                            <span class="perm-grupo__count" id="count_{{ $prefixo }}">
                                                {{ $marcados }}/{{ $totalGrupo }}
                                            </span>
                                        </div>
                                        <div class="perm-grupo__actions">
                                            <button type="button" class="btn-grupo-toggle" title="Marcar todos do módulo"
                                                    onclick="event.stopPropagation(); toggleGrupoAll('{{ $prefixo }}', true)">
                                                <i class="fas fa-check-square"></i>
                                            </button>
                                            <button type="button" class="btn-grupo-toggle btn-grupo-toggle--clear" title="Desmarcar todos do módulo"
                                                    onclick="event.stopPropagation(); toggleGrupoAll('{{ $prefixo }}', false)">
                                                <i class="fas fa-minus-square"></i>
                                            </button>
                                            <i class="fas fa-chevron-down perm-grupo__chevron" id="chev_{{ $prefixo }}"></i>
                                        </div>
                                    </div>
                                    <div class="perm-grupo__body" id="body_{{ $prefixo }}">
                                        <div class="perm-grid">
                                            @foreach ($perms as $permissao)
                                                @php
                                                    $partes = explode('.', $permissao->name);
                                                    $acao = $partes[count($partes) - 1] ?? $permissao->name;
                                                    $descAuto = $acaoDesc[$acao] ?? $acao;
                                                    $desc = $permissao->descricao ?: $descAuto;
                                                @endphp
                                                <label class="perm-item {{ ($permissao->checked ?? '') === 'checked' ? 'perm-item--checked' : '' }}"
                                                       data-grupo="{{ $prefixo }}"
                                                       for="perm_{{ $permissao->id }}">
                                                    <input
                                                        type="checkbox"
                                                        name="permissions[]"
                                                        id="perm_{{ $permissao->id }}"
                                                        value="{{ $permissao->id }}"
                                                        {{ $permissao->checked ?? '' }}
                                                        onchange="onPermChange(this)"
                                                    >
                                                    <div class="perm-item__info">
                                                        <span class="perm-item__name">{{ $permissao->name }}</span>
                                                        <span class="perm-item__desc">{{ $desc }}</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary btn-sm px-5">
                            <i class="fas fa-save mr-1"></i> Salvar Regra
                        </button>
                        <a href="{{ route('tipo_usuario.index') }}" class="btn btn-secondary btn-sm ml-2">
                            Cancelar
                        </a>
                    </div>

                </x-form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.permissoes-grupos {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.perm-grupo {
    border: 1px solid #dbe6f3;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
}
.perm-grupo__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    background: #f6f9ff;
    cursor: pointer;
    user-select: none;
    border-bottom: 1px solid transparent;
    transition: background .15s;
}
.perm-grupo__header:hover { background: #edf2ff; }
.perm-grupo__left {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    font-size: .9rem;
    color: #1e3a5f;
}
.perm-grupo__left i { color: #3b82f6; width: 16px; }
.perm-grupo__count {
    font-size: .75rem;
    font-weight: 600;
    background: #dbeafe;
    color: #1d4ed8;
    border-radius: 20px;
    padding: 1px 8px;
}
.perm-grupo__actions {
    display: flex;
    align-items: center;
    gap: 6px;
}
.btn-grupo-toggle {
    background: none;
    border: none;
    color: #10b981;
    font-size: .9rem;
    cursor: pointer;
    padding: 2px 5px;
    border-radius: 4px;
}
.btn-grupo-toggle--clear { color: #94a3b8; }
.btn-grupo-toggle:hover { background: rgba(0,0,0,.06); }
.perm-grupo__chevron {
    font-size: .75rem;
    color: #94a3b8;
    transition: transform .2s;
}
.perm-grupo__chevron.open { transform: rotate(180deg); }
.perm-grupo__body {
    display: none;
    padding: 12px;
    border-top: 1px solid #e8eef7;
    background: #fafcff;
}
.perm-grupo__body.open { display: block; }
.perm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 6px;
}
.perm-item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    border: 1px solid #dbe6f3;
    border-radius: 8px;
    padding: 8px 10px;
    cursor: pointer;
    background: #fff;
    transition: border-color .12s, background .12s;
    margin: 0;
}
.perm-item:hover { border-color: #93c5fd; background: #f0f7ff; }
.perm-item--checked { border-color: #6ee7b7 !important; background: #f0fdf4 !important; }
.perm-item input[type="checkbox"] {
    margin-top: 2px;
    flex: 0 0 15px;
    accent-color: #10b981;
    width: 15px;
    height: 15px;
}
.perm-item__info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}
.perm-item__name {
    font-size: .8rem;
    font-weight: 700;
    color: #1e3a5f;
    word-break: break-all;
}
.perm-item__desc {
    font-size: .73rem;
    color: #64748b;
    line-height: 1.3;
}
</style>
@stop

@section('js')
<script>
function toggleGrupo(prefixo) {
    const body = document.getElementById('body_' + prefixo);
    const chev = document.getElementById('chev_' + prefixo);
    body.classList.toggle('open');
    chev.classList.toggle('open');
}

function toggleGrupoAll(prefixo, marcar) {
    document.querySelectorAll('.perm-item[data-grupo="' + prefixo + '"] input[type="checkbox"]')
        .forEach(function(cb) {
            cb.checked = marcar;
            cb.closest('.perm-item').classList.toggle('perm-item--checked', marcar);
        });
    updateCount(prefixo);
}

function toggleAll(marcar) {
    document.querySelectorAll('.perm-item input[type="checkbox"]').forEach(function(cb) {
        cb.checked = marcar;
        cb.closest('.perm-item').classList.toggle('perm-item--checked', marcar);
    });
    document.querySelectorAll('.perm-grupo').forEach(function(g) {
        const prefixo = g.id.replace('grupo_', '');
        updateCount(prefixo);
    });
}

function onPermChange(cb) {
    cb.closest('.perm-item').classList.toggle('perm-item--checked', cb.checked);
    const prefixo = cb.closest('.perm-item').dataset.grupo;
    updateCount(prefixo);
}

function updateCount(prefixo) {
    const items = document.querySelectorAll('.perm-item[data-grupo="' + prefixo + '"] input[type="checkbox"]');
    const marcados = Array.from(items).filter(cb => cb.checked).length;
    const countEl = document.getElementById('count_' + prefixo);
    if (countEl) countEl.textContent = marcados + '/' + items.length;
}

// Abre grupos com permissões já marcadas
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.perm-grupo').forEach(function(g) {
        const prefixo = g.id.replace('grupo_', '');
        const marcados = g.querySelectorAll('input[type="checkbox"]:checked').length;
        if (marcados > 0) {
            const body = document.getElementById('body_' + prefixo);
            const chev = document.getElementById('chev_' + prefixo);
            if (body) body.classList.add('open');
            if (chev) chev.classList.add('open');
        }
    });
});
</script>
@stop
