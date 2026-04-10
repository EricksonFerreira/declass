@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    // Mapa de grupos → label, ícone, cor Bootstrap
    $grupoMeta = [
        'reserva'                          => ['label' => 'Reservas',                   'icon' => 'fas fa-calendar-check',        'color' => 'primary'],
        'veiculo'                          => ['label' => 'Veículos',                   'icon' => 'fas fa-car',                   'color' => 'success'],
        'usuario'                          => ['label' => 'Usuários',                   'icon' => 'fas fa-users',                 'color' => 'info'],
        'permissao'                        => ['label' => 'Permissões',                 'icon' => 'fas fa-key',                   'color' => 'warning'],
        'regra'                            => ['label' => 'Regras de Acesso',           'icon' => 'fas fa-shield-alt',            'color' => 'warning'],
        'multa'                            => ['label' => 'Multas',                     'icon' => 'fas fa-file-invoice-dollar',   'color' => 'danger'],
        'sinistro'                         => ['label' => 'Sinistros',                  'icon' => 'fas fa-car-crash',             'color' => 'danger'],
        'movimentacao'                     => ['label' => 'Movimentações',              'icon' => 'fas fa-exchange-alt',          'color' => 'secondary'],
        'financeiro'                       => ['label' => 'Financeiro',                 'icon' => 'fas fa-dollar-sign',           'color' => 'success'],
        'calcao'                           => ['label' => 'Caução',                     'icon' => 'fas fa-hand-holding-usd',      'color' => 'success'],
        'empresa'                          => ['label' => 'Empresa',                    'icon' => 'fas fa-building',              'color' => 'dark'],
        'empresa_context'                  => ['label' => 'Contexto de Empresa',        'icon' => 'fas fa-building',              'color' => 'dark'],
        'estacao'                          => ['label' => 'Estações',                   'icon' => 'fas fa-map-marker-alt',        'color' => 'info'],
        'modelo'                           => ['label' => 'Modelos de Veículo',         'icon' => 'fas fa-car-side',              'color' => 'secondary'],
        'marca'                            => ['label' => 'Marcas',                     'icon' => 'fas fa-tag',                   'color' => 'secondary'],
        'grupo_frota'                      => ['label' => 'Grupos de Frota',            'icon' => 'fas fa-layer-group',           'color' => 'primary'],
        'frota'                            => ['label' => 'Frota',                      'icon' => 'fas fa-truck',                 'color' => 'primary'],
        'dano'                             => ['label' => 'Danos',                      'icon' => 'fas fa-exclamation-triangle',  'color' => 'warning'],
        'checklist'                        => ['label' => 'Checklists',                 'icon' => 'fas fa-tasks',                 'color' => 'info'],
        'ordem-servico'                    => ['label' => 'Ordens de Serviço',          'icon' => 'fas fa-tools',                 'color' => 'secondary'],
        'nfse'                             => ['label' => 'NFS-e',                      'icon' => 'fas fa-file-alt',              'color' => 'dark'],
        'site'                             => ['label' => 'Site de Apresentação',       'icon' => 'fas fa-globe',                 'color' => 'primary'],
        'dashboard'                        => ['label' => 'Dashboard',                  'icon' => 'fas fa-chart-bar',             'color' => 'info'],
        'home'                             => ['label' => 'Início',                     'icon' => 'fas fa-home',                  'color' => 'info'],
        'profile'                          => ['label' => 'Perfil',                     'icon' => 'fas fa-user-circle',           'color' => 'info'],
        'segmento'                         => ['label' => 'Segmentos',                  'icon' => 'fas fa-th-large',              'color' => 'secondary'],
        'opcional'                         => ['label' => 'Opcionais',                  'icon' => 'fas fa-plus-circle',           'color' => 'secondary'],
        'opcional_grupo_veiculo'           => ['label' => 'Opcionais por Grupo',        'icon' => 'fas fa-list-ul',               'color' => 'secondary'],
        'cupom_desconto'                   => ['label' => 'Cupons de Desconto',         'icon' => 'fas fa-ticket-alt',            'color' => 'success'],
        'feriado'                          => ['label' => 'Feriados',                   'icon' => 'fas fa-calendar-times',        'color' => 'secondary'],
        'dia_util'                         => ['label' => 'Dias Úteis',                 'icon' => 'fas fa-calendar-day',          'color' => 'secondary'],
        'horario_diario'                   => ['label' => 'Horários Diários',           'icon' => 'fas fa-clock',                 'color' => 'secondary'],
        'tipotransacao'                    => ['label' => 'Tipos de Transação',         'icon' => 'fas fa-receipt',               'color' => 'secondary'],
        'tipo_usuario'                     => ['label' => 'Tipos de Usuário',           'icon' => 'fas fa-user-tag',              'color' => 'secondary'],
        'tipo_frota'                       => ['label' => 'Tipos de Frota',             'icon' => 'fas fa-car-alt',               'color' => 'secondary'],
        'tipo_movimentacao'                => ['label' => 'Tipos de Movimentação',      'icon' => 'fas fa-arrows-alt',            'color' => 'secondary'],
        'tipo_opcional'                    => ['label' => 'Tipos de Opcional',          'icon' => 'fas fa-sliders-h',             'color' => 'secondary'],
        'tipo_checklist'                   => ['label' => 'Tipos de Checklist',         'icon' => 'fas fa-clipboard-list',        'color' => 'secondary'],
        'status_reserva'                   => ['label' => 'Status de Reserva',          'icon' => 'fas fa-toggle-on',             'color' => 'secondary'],
        'status_veiculo'                   => ['label' => 'Status de Veículo',          'icon' => 'fas fa-toggle-on',             'color' => 'secondary'],
        'status_movimentacao'              => ['label' => 'Status de Movimentação',     'icon' => 'fas fa-toggle-on',             'color' => 'secondary'],
        'status_item_checklist'            => ['label' => 'Status de Item Checklist',   'icon' => 'fas fa-toggle-on',             'color' => 'secondary'],
        'categoria_dano'                   => ['label' => 'Categorias de Dano',         'icon' => 'fas fa-folder',                'color' => 'secondary'],
        'item_checklist'                   => ['label' => 'Itens de Checklist',         'icon' => 'fas fa-check-square',          'color' => 'secondary'],
        'configuracao-checklist'           => ['label' => 'Config. de Checklist',       'icon' => 'fas fa-cog',                   'color' => 'secondary'],
        'configuracao_checklist'           => ['label' => 'Config. de Checklist',       'icon' => 'fas fa-cog',                   'color' => 'secondary'],
        'caracteristica_frota'             => ['label' => 'Características de Frota',   'icon' => 'fas fa-list-alt',              'color' => 'secondary'],
        'caracteristica_grupo_frota'       => ['label' => 'Caract. Grupo Frota',        'icon' => 'fas fa-list-alt',              'color' => 'secondary'],
        'caracteristica_grupo'             => ['label' => 'Caract. Grupo',              'icon' => 'fas fa-list-alt',              'color' => 'secondary'],
        'frota_caracteristica'             => ['label' => 'Caract. de Frota',           'icon' => 'fas fa-list-alt',              'color' => 'secondary'],
        'tipo_valor_caracteristica_frota'  => ['label' => 'Valores de Características', 'icon' => 'fas fa-sliders-h',             'color' => 'secondary'],
        'veiculo-dano'                     => ['label' => 'Veículo — Danos',            'icon' => 'fas fa-car-crash',             'color' => 'warning'],
        'avaria'                           => ['label' => 'Avarias',                    'icon' => 'fas fa-exclamation-circle',    'color' => 'warning'],
        'pais'                             => ['label' => 'Países',                     'icon' => 'fas fa-globe-americas',        'color' => 'secondary'],
        'countries'                        => ['label' => 'Countries',                  'icon' => 'fas fa-globe',                 'color' => 'secondary'],
        'termo_consentimento'              => ['label' => 'Termos de Consentimento',    'icon' => 'fas fa-file-signature',        'color' => 'secondary'],
        'tempo_entre_entacao'              => ['label' => 'Tempo entre Estações',       'icon' => 'fas fa-route',                 'color' => 'secondary'],
        'replicacao_base'                  => ['label' => 'Replicação de Base',         'icon' => 'fas fa-database',              'color' => 'dark'],
        'migrations'                       => ['label' => 'Migrações',                  'icon' => 'fas fa-database',              'color' => 'dark'],
        'pagamento'                        => ['label' => 'Pagamento',                  'icon' => 'fas fa-credit-card',           'color' => 'success'],
        'stripe'                           => ['label' => 'Stripe',                     'icon' => 'fab fa-stripe-s',              'color' => 'success'],
        'cashier'                          => ['label' => 'Cashier / Assinatura',       'icon' => 'fas fa-file-invoice',          'color' => 'success'],
        'powerbi'                          => ['label' => 'Power BI',                   'icon' => 'fas fa-chart-pie',             'color' => 'info'],
        '_outros'                          => ['label' => 'Outros',                     'icon' => 'fas fa-ellipsis-h',            'color' => 'secondary'],
    ];

    $colorBadge = [
        'primary'   => '#4b80ff',
        'success'   => '#28a745',
        'info'      => '#17a2b8',
        'warning'   => '#e0a800',
        'danger'    => '#dc3545',
        'secondary' => '#6c757d',
        'dark'      => '#343a40',
    ];

    // Rótulos de ação para badges
    $acaoBadge = [
        'index'         => ['Listar',    'badge-primary'],
        'show'          => ['Ver',       'badge-info'],
        'create'        => ['Criar',     'badge-success'],
        'store'         => ['Salvar',    'badge-success'],
        'edit'          => ['Editar',    'badge-warning'],
        'update'        => ['Atualizar', 'badge-warning'],
        'destroy'       => ['Excluir',   'badge-danger'],
        'delete'        => ['Excluir',   'badge-danger'],
        'filtro'        => ['Filtro',    'badge-light'],
        'export'        => ['Exportar',  'badge-dark'],
        'setPerPage'    => ['Paginação', 'badge-light'],
        'removerFiltro' => ['Filtro',    'badge-light'],
    ];

    function getAcaoFromName(string $name): ?string {
        if (!str_contains($name, '.')) return null;
        return (string) str($name)->afterLast('.');
    }

    function getAcaoBadge(string $name, array $acaoBadge): array {
        $acao = getAcaoFromName($name);
        return $acaoBadge[$acao] ?? [$acao ?? $name, 'badge-secondary'];
    }
@endphp

<div class="pm-page">

    {{-- ── Hero / stats ── --}}
    <div class="pm-hero mb-4">
        <div class="pm-hero__left">
            <div class="pm-hero__icon">
                <i class="fas fa-key"></i>
            </div>
            <div>
                <h2 class="pm-hero__title">Permissões do Sistema</h2>
                <p class="pm-hero__sub">Gerencie o que cada usuário pode fazer. Permissões são agrupadas por módulo para facilitar a gestão.</p>
            </div>
        </div>
        <a href="{{ route('permissao.create') }}" class="btn btn-primary pm-hero__btn">
            <i class="fas fa-plus mr-1"></i> Nova Permissão
        </a>
    </div>

    {{-- ── Painel principal ── --}}
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
                <h3 class="card-title text-sm text-bold mb-0">
                    REGISTROS
                    <span class="badge badge-success">{{ $totalPermissoes }}</span>
                    <span class="badge badge-info ml-1">{{ $totalGrupos }} grupos</span>
                </h3>
                <div class="d-flex align-items-center flex-wrap" style="gap:6px">
                    <form method="GET" action="{{ route('permissao.index') }}" class="d-flex align-items-center" style="gap:4px">
                        <input type="text" name="busca" value="{{ $busca }}"
                            placeholder="Buscar..."
                            class="form-control form-control-xs pm-search-input"
                            style="height:28px;font-size:.8rem;border-radius:6px;border:1px solid #d0daea;"
                            oninput="filterPermissoes(this.value)">
                        @if($busca)
                            <a href="{{ route('permissao.index') }}" class="btn btn-xs btn-outline-secondary" title="Limpar">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </form>
                    <div class="pm-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards">
                            <i class="fas fa-layer-group mr-1"></i> Grupos
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i> Tabela
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 pm-index-body" data-view="cards">

    @if($permissaos->isEmpty())
        <div class="alert alert-warning m-2">
            <i class="fas fa-search mr-1"></i> Nenhuma permissão encontrada
            @if($busca) para "<strong>{{ $busca }}</strong>" @endif.
            <a href="{{ route('permissao.index') }}" class="ml-2">Limpar busca</a>
        </div>
    @endif

    {{-- ── Card view: Accordion de grupos ── --}}
    <div class="pm-cards-view" id="pm-accordion">
        @foreach($permissaos as $grupo => $itens)
            @php
                $meta  = $grupoMeta[$grupo] ?? $grupoMeta['_outros'];
                $color = $meta['color'];
                $hex   = $colorBadge[$color] ?? '#6c757d';
                $accordionId = 'grp_' . preg_replace('/[^a-z0-9]/', '_', strtolower($grupo));
                $isFirst = $loop->first;
            @endphp

            <div class="pm-group-card mb-2" data-grupo="{{ strtolower($grupo) }}">
                {{-- Group header --}}
                <div class="pm-group-header collapsed"
                     data-toggle="collapse"
                     data-target="#{{ $accordionId }}"
                     aria-expanded="{{ $isFirst ? 'true' : 'false' }}"
                     style="--grp-color: {{ $hex }}">

                    <div class="pm-group-header__left">
                        <span class="pm-group-header__dot" style="background:{{ $hex }}"></span>
                        <span class="pm-group-header__icon" style="color:{{ $hex }}">
                            <i class="{{ $meta['icon'] }}"></i>
                        </span>
                        <span class="pm-group-header__name">{{ $meta['label'] }}</span>
                        <span class="badge badge-pill pm-group-header__count"
                              style="background:{{ $hex }}20; color:{{ $hex }}; border:1px solid {{ $hex }}40">
                            {{ $itens->count() }} permissão{{ $itens->count() !== 1 ? 'ões' : '' }}
                        </span>
                    </div>

                    <div class="pm-group-header__right">
                        <a href="{{ route('permissao.create') }}?grupo={{ urlencode($grupo) }}"
                           class="btn btn-xs pm-btn-add-group"
                           style="border-color:{{ $hex }}40; color:{{ $hex }}"
                           title="Nova permissão neste grupo"
                           onclick="event.stopPropagation()">
                            <i class="fas fa-plus"></i>
                        </a>
                        <span class="pm-group-header__chevron">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </div>
                </div>

                {{-- Group body --}}
                <div id="{{ $accordionId }}" class="collapse {{ $isFirst ? 'show' : '' }}">
                    <div class="pm-group-body">
                        <table class="pm-perm-table" data-group-table>
                            <thead>
                                <tr>
                                    <th style="width:46px">Ação</th>
                                    <th>Nome técnico</th>
                                    <th>Descrição</th>
                                    <th style="width:110px">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itens as $permissao)
                                    @php
                                        [$acaoLabel, $acaoCls] = getAcaoBadge($permissao->name, $acaoBadge);
                                    @endphp
                                    <tr class="pm-perm-row"
                                        data-perm="{{ strtolower($permissao->name) }}"
                                        data-desc="{{ strtolower($permissao->descricao ?? '') }}">
                                        <td>
                                            <span class="badge {{ $acaoCls }} badge-sm">{{ $acaoLabel }}</span>
                                        </td>
                                        <td>
                                            <code class="pm-perm-name">{{ $permissao->name }}</code>
                                        </td>
                                        <td class="pm-desc-cell" data-id="{{ $permissao->id }}">
                                            <div class="pm-desc-view">
                                                @if($permissao->descricao)
                                                    <span class="pm-perm-desc">{{ $permissao->descricao }}</span>
                                                @else
                                                    <span class="pm-perm-desc--empty">Sem descrição</span>
                                                @endif
                                                <button type="button" class="pm-btn-edit-desc" title="Editar descrição">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            </div>
                                            <div class="pm-desc-edit" style="display:none">
                                                <form method="POST" action="{{ route('permissao.update', $permissao->id) }}" class="pm-inline-form">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="name"       value="{{ $permissao->name }}">
                                                    <input type="hidden" name="guard_name" value="{{ $permissao->guard_name }}">
                                                    <input type="hidden" name="grupo"      value="{{ $permissao->grupo }}">
                                                    <textarea name="descricao" class="form-control form-control-sm pm-inline-textarea"
                                                        rows="2" placeholder="Descreva o que esta permissão concede…">{{ $permissao->descricao }}</textarea>
                                                    <div class="pm-inline-actions">
                                                        <button type="submit" class="btn btn-xs btn-success"><i class="fas fa-check"></i> Salvar</button>
                                                        <button type="button" class="btn btn-xs btn-outline-secondary pm-btn-cancel-desc"><i class="fas fa-times"></i></button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('permissao.edit', $permissao->id) }}"
                                               class="btn btn-xs btn-outline-warning" title="Editar">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <x-delete-button :action="route('permissao.destroy', $permissao->id)" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>{{-- pm-cards-view --}}

    {{-- ── Table view: flat list ── --}}
    <div class="pm-table-view table-responsive" style="max-height:calc(100vh - 320px);overflow:auto">
        <table class="table table-striped table-bordered table-sm pm-flat-table">
            <thead>
                <tr>
                    <th>Grupo</th>
                    <th>Ação</th>
                    <th>Nome técnico</th>
                    <th>Descrição</th>
                    <th style="width:110px">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permissaos as $grupo => $itens)
                    @php $meta = $grupoMeta[$grupo] ?? $grupoMeta['_outros']; $hex = $colorBadge[$meta['color']] ?? '#6c757d'; @endphp
                    @foreach($itens as $permissao)
                        @php [$acaoLabel, $acaoCls] = getAcaoBadge($permissao->name, $acaoBadge); @endphp
                        <tr class="pm-perm-row"
                            data-perm="{{ strtolower($permissao->name) }}"
                            data-desc="{{ strtolower($permissao->descricao ?? '') }}"
                            data-grupo="{{ strtolower($grupo) }}">
                            <td>
                                <span style="color:{{ $hex }};font-size:.75rem;font-weight:600">
                                    <i class="{{ $meta['icon'] }} mr-1"></i>{{ $meta['label'] }}
                                </span>
                            </td>
                            <td><span class="badge {{ $acaoCls }} badge-sm">{{ $acaoLabel }}</span></td>
                            <td><code class="pm-perm-name">{{ $permissao->name }}</code></td>
                            <td class="pm-desc-cell" data-id="{{ $permissao->id }}">
                                <div class="pm-desc-view">
                                    @if($permissao->descricao)
                                        <span class="pm-perm-desc">{{ $permissao->descricao }}</span>
                                    @else
                                        <span class="pm-perm-desc--empty">—</span>
                                    @endif
                                    <button type="button" class="pm-btn-edit-desc" title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                </div>
                                <div class="pm-desc-edit" style="display:none">
                                    <form method="POST" action="{{ route('permissao.update', $permissao->id) }}" class="pm-inline-form">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="name"       value="{{ $permissao->name }}">
                                        <input type="hidden" name="guard_name" value="{{ $permissao->guard_name }}">
                                        <input type="hidden" name="grupo"      value="{{ $permissao->grupo }}">
                                        <textarea name="descricao" class="form-control form-control-sm pm-inline-textarea" rows="2">{{ $permissao->descricao }}</textarea>
                                        <div class="pm-inline-actions">
                                            <button type="submit" class="btn btn-xs btn-success"><i class="fas fa-check"></i> Salvar</button>
                                            <button type="button" class="btn btn-xs btn-outline-secondary pm-btn-cancel-desc"><i class="fas fa-times"></i></button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('permissao.edit', $permissao->id) }}" class="btn btn-xs btn-outline-warning"><i class="fas fa-pen"></i></a>
                                <x-delete-button :action="route('permissao.destroy', $permissao->id)" />
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>{{-- pm-table-view --}}

        </div>{{-- card-body --}}
    </div>{{-- card --}}

</div>{{-- pm-page --}}
@stop

@section('css')
<style>
/* ── Page wrapper ── */
.pm-page { padding-bottom: 2rem; }

/* ── Hero ── */
.pm-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(120deg, #f0f4ff 0%, #e8f0fe 100%);
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    gap: 1rem;
}
.pm-hero__left { display: flex; align-items: center; gap: 1rem; }
.pm-hero__icon {
    width: 52px; height: 52px;
    border-radius: 14px;
    background: #4b80ff20;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: #4b80ff;
    flex-shrink: 0;
}
.pm-hero__title { font-size: 1.2rem; font-weight: 700; color: #0f2740; margin: 0 0 .2rem; }
.pm-hero__sub   { font-size: .82rem; color: #6c757d; margin: 0; }
.pm-hero__btn   { border-radius: 10px; font-weight: 600; white-space: nowrap; }

/* ── Stats ── */
.pm-stat {
    display: flex; align-items: center; gap: .9rem;
    border-radius: 12px; padding: .85rem 1rem;
}
.pm-stat--blue  { background: #e8f0fe; }
.pm-stat--green { background: #e8f5e9; }
.pm-stat--gray  { background: #f8f9fa; border: 1px solid #e9ecef; }
.pm-stat__icon  { font-size: 1.4rem; opacity: .7; flex-shrink: 0; }
.pm-stat--blue  .pm-stat__icon { color: #4b80ff; }
.pm-stat--green .pm-stat__icon { color: #28a745; }
.pm-stat--gray  .pm-stat__icon { color: #6c757d; }
.pm-stat__val   { font-size: 1.35rem; font-weight: 700; color: #0f2740; line-height: 1; }
.pm-stat__lbl   { font-size: .75rem; color: #6c757d; margin-top: .1rem; }
.pm-search-input { border-radius: 8px; }

/* ── Group card ── */
.pm-group-card  { border: 1px solid #e5eaf2; border-radius: 12px; overflow: hidden; background: #fff; }

/* ── Group header ── */
.pm-group-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: .65rem 1rem;
    cursor: pointer;
    background: #fafbfd;
    user-select: none;
    transition: background .15s;
    gap: .5rem;
}
.pm-group-header:hover { background: #f0f4ff; }
.pm-group-header__left  { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
.pm-group-header__right { display: flex; align-items: center; gap: .4rem; flex-shrink: 0; }
.pm-group-header__dot  { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.pm-group-header__icon { font-size: 1rem; width: 18px; text-align: center; flex-shrink: 0; }
.pm-group-header__name { font-weight: 600; font-size: .9rem; color: #21364f; }
.pm-group-header__count { font-size: .72rem; font-weight: 600; }
.pm-group-header__chevron { color: #aab5c4; font-size: .75rem; transition: transform .2s; }
.pm-group-header:not(.collapsed) .pm-group-header__chevron { transform: rotate(180deg); }

/* "+ Nova permissão no grupo" button */
.pm-btn-add-group {
    border-radius: 6px;
    border: 1.5px solid;
    background: transparent;
    padding: .15rem .4rem;
    font-size: .75rem;
    line-height: 1.4;
    opacity: 0;
    transition: opacity .15s;
}
.pm-group-header:hover .pm-btn-add-group { opacity: 1; }

/* ── Group body ── */
.pm-group-body { overflow-x: auto; }
.pm-perm-table  {
    width: 100%; border-collapse: collapse; font-size: .82rem;
}
.pm-perm-table thead th {
    background: #f4f7fc; color: #7a8fa6;
    font-weight: 600; font-size: .73rem; letter-spacing: .04em;
    padding: .4rem .8rem; border-bottom: 1px solid #e5eaf2;
    white-space: nowrap;
}
.pm-perm-row    { border-bottom: 1px solid #f0f3f8; transition: background .1s; }
.pm-perm-row:last-child { border-bottom: none; }
.pm-perm-row:hover { background: #f8faff; }
.pm-perm-row td { padding: .45rem .8rem; vertical-align: middle; }

.pm-perm-name { font-family: 'SFMono-Regular', Consolas, monospace; font-size: .78rem; color: #2c4a6e; background: #f0f4ff; padding: .1rem .35rem; border-radius: 4px; }
.pm-perm-desc { color: #495057; font-size: .82rem; }
.pm-perm-desc--empty { color: #bbc5d0; font-size: .78rem; font-style: italic; }

/* ── Inline description edit ── */
.pm-desc-cell { position: relative; min-width: 180px; }
.pm-desc-view { display: flex; align-items: flex-start; gap: .3rem; }
.pm-btn-edit-desc {
    background: none; border: none; padding: 0 .2rem;
    color: #aab5c4; font-size: .7rem; cursor: pointer;
    opacity: 0; transition: opacity .1s; flex-shrink: 0; margin-top: .05rem;
}
.pm-perm-row:hover .pm-btn-edit-desc { opacity: 1; }
.pm-inline-textarea { font-size: .8rem; border-radius: 6px; resize: vertical; margin-top: .3rem; }
.pm-inline-actions { display: flex; gap: .25rem; margin-top: .3rem; }

/* ── Hidden by search ── */
.pm-perm-row.pm-hidden { display: none; }
.pm-group-card.pm-group-hidden { display: none; }

/* ── View toggle ── */
.pm-view-toggle { display: flex; gap: 5px; }
.pm-view-toggle .btn.active { background: #0f172a; color: #fff; border-color: #0f172a; }

.pm-index-body[data-view="cards"] .pm-table-view { display: none; }
.pm-index-body[data-view="table"] .pm-cards-view { display: none; }

.pm-index-body[data-view="table"] .pm-table-view thead th {
    position: sticky; top: 0; z-index: 2; background: #f8fafc;
}

.pm-flat-table { font-size: .82rem; }

/* ── Responsive ── */
@media (max-width: 767px) {
    .pm-hero { flex-direction: column; align-items: flex-start; }
    .pm-hero__btn { align-self: stretch; text-align: center; }
    .pm-hero__left { flex-direction: column; align-items: flex-start; gap: .6rem; }
}
</style>
@stop

@section('js')
<script>
// ── View toggle ──
(function () {
    const body = document.querySelector('.pm-index-body');
    const buttons = document.querySelectorAll('.pm-view-toggle [data-view]');
    if (!body || !buttons.length) return;
    function setView(view) {
        body.setAttribute('data-view', view);
        localStorage.setItem('permissaoIndexView', view);
        buttons.forEach(btn => btn.classList.toggle('active', btn.dataset.view === view));
    }
    const saved = localStorage.getItem('permissaoIndexView') || 'cards';
    setView(saved);
    buttons.forEach(btn => btn.addEventListener('click', () => setView(btn.dataset.view)));
})();

// JS live search across all groups + table
function filterPermissoes(val) {
    const q = val.toLowerCase().trim();

    // Card view: filter within accordion groups
    document.querySelectorAll('.pm-group-card').forEach(function (card) {
        const grupo = card.dataset.grupo || '';
        let grupoVisible = false;
        card.querySelectorAll('.pm-perm-row').forEach(function (row) {
            const perm = row.dataset.perm || '';
            const desc = row.dataset.desc || '';
            const match = !q || perm.includes(q) || desc.includes(q) || grupo.includes(q);
            row.classList.toggle('pm-hidden', !match);
            if (match) grupoVisible = true;
        });
        card.classList.toggle('pm-group-hidden', !grupoVisible);
        if (q && grupoVisible) {
            const collapse = card.querySelector('.collapse');
            if (collapse && !collapse.classList.contains('show')) {
                $(collapse).collapse('show');
            }
        }
    });

    // Table view: filter flat rows
    document.querySelectorAll('.pm-flat-table .pm-perm-row').forEach(function (row) {
        const perm = row.dataset.perm || '';
        const desc = row.dataset.desc || '';
        const grupo = row.dataset.grupo || '';
        const match = !q || perm.includes(q) || desc.includes(q) || grupo.includes(q);
        row.classList.toggle('pm-hidden', !match);
    });
}

// Sync search input with JS filter on page load (for back-button)
document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('[name="busca"]');
    if (input && input.value) filterPermissoes(input.value);
    if (input) input.addEventListener('input', function () { filterPermissoes(this.value); });

    // ── Inline description edit ──
    document.querySelectorAll('.pm-btn-edit-desc').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const cell = btn.closest('.pm-desc-cell');
            cell.querySelector('.pm-desc-view').style.display = 'none';
            const editDiv = cell.querySelector('.pm-desc-edit');
            editDiv.style.display = '';
            editDiv.querySelector('textarea').focus();
        });
    });

    document.querySelectorAll('.pm-btn-cancel-desc').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const cell = btn.closest('.pm-desc-cell');
            cell.querySelector('.pm-desc-edit').style.display = 'none';
            cell.querySelector('.pm-desc-view').style.display = '';
        });
    });
});
</script>
@stop
