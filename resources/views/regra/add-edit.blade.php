@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $oldPermissions = old('permissions');

    // Mapa grupo → label + ícone (mesmo do index de permissões)
    $grupoMeta = [
        'operacao'                        => ['label' => 'Operação',                  'icon' => 'fas fa-clock',               'color' => '#4b80ff'],
        'assinatura'                      => ['label' => 'Assinatura',                'icon' => 'fas fa-file-signature',      'color' => '#ec4899'],
        'relatorios'                      => ['label' => 'Relatórios',                'icon' => 'fas fa-chart-line',          'color' => '#14b8a6'],
        'relatorio'                       => ['label' => 'Relatórios',                'icon' => 'fas fa-chart-line',          'color' => '#14b8a6'],
        'comandos'                        => ['label' => 'Comandos',                  'icon' => 'fas fa-terminal',            'color' => '#475569'],
        'importacao'                      => ['label' => 'Importação',                'icon' => 'fas fa-file-import',         'color' => '#f59e0b'],
        'documentos'                      => ['label' => 'Documentos',                'icon' => 'fas fa-folder-open',         'color' => '#10b981'],
        'licitacoes'                      => ['label' => 'Licitações',                'icon' => 'fas fa-gavel',               'color' => '#a855f7'],
        'tutoriais'                       => ['label' => 'Tutoriais',                 'icon' => 'fas fa-graduation-cap',      'color' => '#0ea5e9'],
        'notificacoes'                    => ['label' => 'Notificações',              'icon' => 'fas fa-bell',                'color' => '#f97316'],
        'onboarding'                      => ['label' => 'Onboarding',                'icon' => 'fas fa-rocket',              'color' => '#f97316'],
        'planos'                          => ['label' => 'Planos',                    'icon' => 'fas fa-layer-group',         'color' => '#8b5cf6'],
        'configuracao-ia'                 => ['label' => 'Config. de IA',             'icon' => 'fas fa-robot',               'color' => '#06b6d4'],
        'ia-uso'                          => ['label' => 'Uso de IA',                 'icon' => 'fas fa-chart-pie',           'color' => '#06b6d4'],
        'ia-global'                       => ['label' => 'IA Global',                 'icon' => 'fas fa-robot',               'color' => '#06b6d4'],
        'regra'                           => ['label' => 'Perfis de Acesso',          'icon' => 'fas fa-user-shield',         'color' => '#7c3aed'],
        'alerta'                          => ['label' => 'Alertas',                   'icon' => 'fas fa-exclamation-triangle','color' => '#e0a800'],
        'cadastro'                        => ['label' => 'Cadastro',                  'icon' => 'fas fa-user-plus',           'color' => '#6c757d'],
        'cron'                            => ['label' => 'Agendador (Cron)',          'icon' => 'fas fa-stopwatch',           'color' => '#475569'],
        'menu'                            => ['label' => 'Menu',                      'icon' => 'fas fa-bars',                'color' => '#6c757d'],
        'sitemap'                         => ['label' => 'Sitemap',                   'icon' => 'fas fa-sitemap',             'color' => '#6c757d'],
        'limpar-cache'                    => ['label' => 'Limpar Cache',              'icon' => 'fas fa-broom',               'color' => '#6c757d'],
        'log-viewer'                      => ['label' => 'Logs',                      'icon' => 'fas fa-file-alt',            'color' => '#475569'],
        'admin'                           => ['label' => 'Administração',             'icon' => 'fas fa-user-cog',            'color' => '#6c757d'],
        'adminlte'                        => ['label' => 'AdminLTE',                  'icon' => 'fas fa-th-large',            'color' => '#6c757d'],
        'pricing'                         => ['label' => 'Preços',                    'icon' => 'fas fa-tags',                'color' => '#28a745'],
        'reserva'                         => ['label' => 'Reservas',                  'icon' => 'fas fa-calendar-check',      'color' => '#4b80ff'],
        'veiculo'                         => ['label' => 'Veículos',                  'icon' => 'fas fa-car',                 'color' => '#28a745'],
        'usuario'                         => ['label' => 'Usuários',                  'icon' => 'fas fa-users',               'color' => '#17a2b8'],
        'permissao'                       => ['label' => 'Permissões',                'icon' => 'fas fa-key',                 'color' => '#e0a800'],
        'multa'                           => ['label' => 'Multas',                    'icon' => 'fas fa-file-invoice-dollar', 'color' => '#dc3545'],
        'sinistro'                        => ['label' => 'Sinistros',                 'icon' => 'fas fa-car-crash',           'color' => '#dc3545'],
        'movimentacao'                    => ['label' => 'Movimentações',             'icon' => 'fas fa-exchange-alt',        'color' => '#6c757d'],
        'financeiro'                      => ['label' => 'Financeiro',                'icon' => 'fas fa-dollar-sign',         'color' => '#28a745'],
        'calcao'                          => ['label' => 'Caução',                    'icon' => 'fas fa-hand-holding-usd',   'color' => '#28a745'],
        'empresa'                         => ['label' => 'Empresa',                   'icon' => 'fas fa-building',            'color' => '#343a40'],
        'empresa_context'                 => ['label' => 'Contexto de Empresa',       'icon' => 'fas fa-building',            'color' => '#343a40'],
        'modelo'                          => ['label' => 'Modelos de Veículo',        'icon' => 'fas fa-car-side',            'color' => '#6c757d'],
        'marca'                           => ['label' => 'Marcas',                    'icon' => 'fas fa-tag',                 'color' => '#6c757d'],
        'grupo_frota'                     => ['label' => 'Grupos de Frota',           'icon' => 'fas fa-layer-group',         'color' => '#4b80ff'],
        'frota'                           => ['label' => 'Frota',                     'icon' => 'fas fa-truck',               'color' => '#4b80ff'],
        'dano'                            => ['label' => 'Danos',                     'icon' => 'fas fa-exclamation-triangle','color' => '#e0a800'],
        'checklist'                       => ['label' => 'Checklists',                'icon' => 'fas fa-tasks',               'color' => '#17a2b8'],
        'ordem-servico'                   => ['label' => 'Ordens de Serviço',         'icon' => 'fas fa-tools',               'color' => '#6c757d'],
        'nfse'                            => ['label' => 'NFS-e',                     'icon' => 'fas fa-file-alt',            'color' => '#343a40'],
        'site'                            => ['label' => 'Site de Apresentação',      'icon' => 'fas fa-globe',               'color' => '#4b80ff'],
        'dashboard'                       => ['label' => 'Dashboard',                 'icon' => 'fas fa-chart-bar',           'color' => '#17a2b8'],
        'home'                            => ['label' => 'Início',                    'icon' => 'fas fa-home',                'color' => '#17a2b8'],
        'profile'                         => ['label' => 'Perfil',                    'icon' => 'fas fa-user-circle',         'color' => '#17a2b8'],
        'segmento'                        => ['label' => 'Segmentos',                 'icon' => 'fas fa-th-large',            'color' => '#6c757d'],
        'opcional'                        => ['label' => 'Opcionais',                 'icon' => 'fas fa-plus-circle',         'color' => '#6c757d'],
        'opcional_grupo_veiculo'          => ['label' => 'Opcionais por Grupo',       'icon' => 'fas fa-list-ul',             'color' => '#6c757d'],
        'cupom_desconto'                  => ['label' => 'Cupons de Desconto',        'icon' => 'fas fa-ticket-alt',          'color' => '#28a745'],
        'feriado'                         => ['label' => 'Feriados',                  'icon' => 'fas fa-calendar-times',      'color' => '#6c757d'],
        'dia_util'                        => ['label' => 'Dias Úteis',                'icon' => 'fas fa-calendar-day',        'color' => '#6c757d'],
        'horario_diario'                  => ['label' => 'Horários Diários',          'icon' => 'fas fa-clock',               'color' => '#6c757d'],
        'pagamento'                       => ['label' => 'Pagamento',                 'icon' => 'fas fa-credit-card',         'color' => '#28a745'],
        'stripe'                          => ['label' => 'Stripe',                    'icon' => 'fab fa-stripe-s',            'color' => '#28a745'],
        'cashier'                         => ['label' => 'Cashier / Assinatura',      'icon' => 'fas fa-file-invoice',        'color' => '#28a745'],
        'nfse'                            => ['label' => 'NFS-e',                     'icon' => 'fas fa-file-alt',            'color' => '#343a40'],
        'powerbi'                         => ['label' => 'Power BI',                  'icon' => 'fas fa-chart-pie',           'color' => '#17a2b8'],
        'tipo_usuario'                    => ['label' => 'Tipos de Usuário',          'icon' => 'fas fa-user-tag',            'color' => '#6c757d'],
        'tipo_frota'                      => ['label' => 'Tipos de Frota',            'icon' => 'fas fa-car-alt',             'color' => '#6c757d'],
        'tipo_movimentacao'               => ['label' => 'Tipos de Movimentação',     'icon' => 'fas fa-arrows-alt',          'color' => '#6c757d'],
        'tipo_opcional'                   => ['label' => 'Tipos de Opcional',         'icon' => 'fas fa-sliders-h',           'color' => '#6c757d'],
        'tipo_checklist'                  => ['label' => 'Tipos de Checklist',        'icon' => 'fas fa-clipboard-list',      'color' => '#6c757d'],
        'status_reserva'                  => ['label' => 'Status de Reserva',         'icon' => 'fas fa-toggle-on',           'color' => '#6c757d'],
        'status_veiculo'                  => ['label' => 'Status de Veículo',         'icon' => 'fas fa-toggle-on',           'color' => '#6c757d'],
        'status_movimentacao'             => ['label' => 'Status de Movimentação',    'icon' => 'fas fa-toggle-on',           'color' => '#6c757d'],
        'status_item_checklist'           => ['label' => 'Status Item Checklist',     'icon' => 'fas fa-toggle-on',           'color' => '#6c757d'],
        'categoria_dano'                  => ['label' => 'Categorias de Dano',        'icon' => 'fas fa-folder',              'color' => '#6c757d'],
        'item_checklist'                  => ['label' => 'Itens de Checklist',        'icon' => 'fas fa-check-square',        'color' => '#6c757d'],
        'configuracao-checklist'          => ['label' => 'Config. de Checklist',      'icon' => 'fas fa-cog',                 'color' => '#6c757d'],
        'configuracao_checklist'          => ['label' => 'Config. de Checklist',      'icon' => 'fas fa-cog',                 'color' => '#6c757d'],
        'caracteristica_frota'            => ['label' => 'Caract. de Frota',          'icon' => 'fas fa-list-alt',            'color' => '#6c757d'],
        'caracteristica_grupo_frota'      => ['label' => 'Caract. Grupo Frota',       'icon' => 'fas fa-list-alt',            'color' => '#6c757d'],
        'caracteristica_grupo'            => ['label' => 'Caract. de Grupo',          'icon' => 'fas fa-list-alt',            'color' => '#6c757d'],
        'frota_caracteristica'            => ['label' => 'Caract. de Frota',          'icon' => 'fas fa-list-alt',            'color' => '#6c757d'],
        'tipo_valor_caracteristica_frota' => ['label' => 'Valores de Caract.',        'icon' => 'fas fa-sliders-h',           'color' => '#6c757d'],
        'veiculo-dano'                    => ['label' => 'Veículo — Danos',           'icon' => 'fas fa-car-crash',           'color' => '#e0a800'],
        'avaria'                          => ['label' => 'Avarias',                   'icon' => 'fas fa-exclamation-circle',  'color' => '#e0a800'],
        'pais'                            => ['label' => 'Países',                    'icon' => 'fas fa-globe-americas',      'color' => '#6c757d'],
        'countries'                       => ['label' => 'Countries',                 'icon' => 'fas fa-globe',               'color' => '#6c757d'],
        'termo_consentimento'             => ['label' => 'Termos de Consentimento',   'icon' => 'fas fa-file-signature',      'color' => '#6c757d'],
        'replicacao_base'                 => ['label' => 'Replicação de Base',        'icon' => 'fas fa-database',            'color' => '#343a40'],
        'migrations'                      => ['label' => 'Migrações',                 'icon' => 'fas fa-database',            'color' => '#343a40'],
        'tipotransacao'                   => ['label' => 'Tipos de Transação',        'icon' => 'fas fa-receipt',             'color' => '#6c757d'],
        '_outros'                         => ['label' => 'Outros',                    'icon' => 'fas fa-ellipsis-h',          'color' => '#6c757d'],
    ];

    // Agrupa permissões pelo grupo efetivo
    $permissoesAgrupadas = $permissoes->groupBy(fn ($p) => $p->grupo_efetivo ?: '_outros');
    $totalSelecionado = $permissoes->filter(fn ($p) => is_array($oldPermissions)
        ? in_array($p->id, $oldPermissions)
        : !empty($p->checked)
    )->count();

    // Labels amigáveis para os subníveis (funcionalidades dentro de cada módulo)
    $featureLabel = [
        'calendario' => 'Calendário', 'clientes' => 'Clientes', 'funcoes' => 'Funções',
        'projetos' => 'Projetos', 'tags' => 'Tags', 'times' => 'Equipes',
        'timeoff' => 'Folgas e Férias', 'timesheet' => 'Planilha de Horas',
        'tracker' => 'Rastreador', 'cronogramas' => 'Cronogramas',
        'planejamento' => 'Planejamento', 'planejar-equipe' => 'Planejar Equipe',
        'planejar-equipe-geral' => 'Planejar Equipe (Geral)',
        'pagamento' => 'Pagamento', 'consumo' => 'Consumo', 'estimativas' => 'Estimativas',
        'membros' => 'Membros', 'categorias' => 'Categorias', 'arquivo' => 'Arquivos',
        'site-apresentacao' => 'Site de Apresentação', 'site-editor' => 'Editor do Site',
        'mudanca' => 'Mudança de Plano', 'downgrade' => 'Downgrade', 'contratar' => 'Contratar',
        'atribuir' => 'Atribuir', 'darkmode' => 'Modo Escuro',
        'cores' => 'Cores', 'empresa' => 'Empresa', 'orientacoes' => 'Orientações',
    ];

    $acaoClasses = [
        'index' => 'badge-primary', 'show' => 'badge-info',
        'create' => 'badge-success', 'store' => 'badge-success',
        'edit' => 'badge-warning', 'update' => 'badge-warning',
        'destroy' => 'badge-danger', 'delete' => 'badge-danger',
        'export' => 'badge-dark',
    ];

    // Monta árvore aninhada: módulo → [funcionalidade → [subnível → …]] → permissões
    $montarArvore = function ($itens) {
        $root = ['folders' => [], 'perms' => [], 'count' => 0];
        foreach ($itens as $p) {
            $parts = explode('.', $p->name);
            array_shift($parts); // remove o módulo (ex.: operacao)
            $node = &$root;
            $node['count']++;
            while (count($parts) > 1) {
                $k = array_shift($parts);
                if (!isset($node['folders'][$k])) {
                    $node['folders'][$k] = ['folders' => [], 'perms' => [], 'count' => 0];
                }
                $node = &$node['folders'][$k];
                $node['count']++;
            }
            $node['perms'][] = $p;
            unset($node);
        }
        return $root;
    };

    $arvores = $permissoesAgrupadas->mapWithKeys(fn ($itens, $g) => [$g => $montarArvore($itens)]);
@endphp

@if ($errors->any())
    <div class="alert alert-danger col-xl-11 col-lg-12 col-md-12">
        <ul class="mb-0 pl-3">
            @foreach (array_unique($errors->all()) as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="col-xl-11 col-lg-12 col-md-12 px-0">
    <div class="re-card shadow-sm">

        {{-- Header --}}
        <div class="re-card__header">
            <div class="re-card__icon"><i class="fas fa-shield-alt"></i></div>
            <div>
                <h4 class="re-card__title">{{ isset($regra) ? 'Editar Regra de Acesso' : 'Nova Regra de Acesso' }}</h4>
                <p class="re-card__sub">Configure o nome e selecione exatamente quais permissões esta regra terá.</p>
            </div>
        </div>

        <div class="re-card__body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">

                {{-- Dados básicos --}}
                <div class="col-12 mb-2">
                    <div class="re-section-title"><i class="fas fa-info-circle mr-1"></i> Identificação</div>
                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm re-input"
                            id="nome" name="name" label="Nome da Regra"
                            value="{{ old('name', $regra->name ?? '') }}" required />
                    </div>
                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm re-input"
                            id="guard_name" name="guard_name" label="Guard (política de segurança)"
                            value="{{ old('guard_name', $regra->guard_name ?? 'web') }}" required />
                    </div>
                </div>

                {{-- Permissões --}}
                <div class="col-12 mt-2 mb-2">
                    <div class="re-section-title"><i class="fas fa-key mr-1"></i> Permissões</div>
                </div>

                <div class="col-12">
                    {{-- Toolbar de permissões --}}
                    <div class="re-perm-toolbar">
                        <div class="re-perm-toolbar__left">
                            <div class="re-perm-search-wrap">
                                <i class="fas fa-search re-perm-search-icon"></i>
                                <input type="text" id="reBusca" class="re-perm-search"
                                    placeholder="Buscar permissão…" autocomplete="off">
                            </div>
                        </div>
                        <div class="re-perm-toolbar__right">
                            <span class="re-counter">
                                <span id="reContador">{{ $totalSelecionado }}</span>
                                de {{ $permissoes->count() }} selecionada(s)
                            </span>
                            <button type="button" class="re-btn-sm re-btn-check" id="reSelecionarTodos">
                                <i class="fas fa-check-double mr-1"></i> Selecionar todos
                            </button>
                            <button type="button" class="re-btn-sm re-btn-uncheck" id="reDesmarcarTodos">
                                <i class="fas fa-times mr-1"></i> Desmarcar todos
                            </button>
                        </div>
                    </div>

                    {{-- Accordion de grupos --}}
                    <div id="reAccordion" class="re-accordion">
                        @foreach ($permissoesAgrupadas as $grupo => $itens)
                            @php
                                $meta = $grupoMeta[$grupo] ?? $grupoMeta['_outros'];
                                $accordionId = 'reg_' . preg_replace('/[^a-z0-9]/', '_', strtolower($grupo));
                                $qtdChecked = $itens->filter(fn ($p) => is_array($oldPermissions)
                                    ? in_array($p->id, $oldPermissions)
                                    : !empty($p->checked)
                                )->count();
                            @endphp

                            <div class="re-group" data-grupo="{{ strtolower($grupo) }}">
                                {{-- Group header --}}
                                <div class="re-group__header collapsed"
                                     data-toggle="collapse"
                                     data-target="#{{ $accordionId }}"
                                     aria-expanded="false">
                                    <div class="re-group__left">
                                        <span class="re-group__dot" style="background:{{ $meta['color'] }}"></span>
                                        <span class="re-group__icon" style="color:{{ $meta['color'] }}">
                                            <i class="{{ $meta['icon'] }}"></i>
                                        </span>
                                        <span class="re-group__name">{{ $meta['label'] }}</span>
                                        <span class="re-group__badge re-group__badge--total">
                                            {{ $itens->count() }}
                                        </span>
                                        <span class="re-group__badge re-group__badge--checked"
                                              id="badge-{{ $accordionId }}"
                                              style="{{ $qtdChecked > 0 ? '' : 'display:none' }}"
                                              data-color="{{ $meta['color'] }}">
                                            {{ $qtdChecked }} ativa{{ $qtdChecked !== 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                    <div class="re-group__actions">
                                        <button type="button" class="re-btn-group-all"
                                                data-group="{{ $accordionId }}"
                                                title="Selecionar todas do grupo"
                                                onclick="event.stopPropagation()">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                        <button type="button" class="re-btn-group-none"
                                                data-group="{{ $accordionId }}"
                                                title="Desmarcar todas do grupo"
                                                onclick="event.stopPropagation()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <i class="fas fa-chevron-down re-group__chevron"></i>
                                    </div>
                                </div>

                                {{-- Group body --}}
                                <div id="{{ $accordionId }}" class="collapse">
                                    <div class="re-group__body" data-grid="{{ $accordionId }}">
                                        @include('regra._perm-node', [
                                            'node'           => $arvores[$grupo],
                                            'prefix'         => $accordionId,
                                            'featureLabel'   => $featureLabel,
                                            'acaoClasses'    => $acaoClasses,
                                            'oldPermissions' => $oldPermissions,
                                        ])
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <x-form-errors name="permissions" />
                </div>

                {{-- Salvar --}}
                <div class="col-12 mt-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary re-btn-save flex-fill">
                            <i class="fas fa-save mr-1"></i> Salvar Regra
                        </button>
                        <a href="{{ route('regra.index') }}" class="btn btn-outline-secondary re-btn-cancel">
                            <i class="fas fa-arrow-left mr-1"></i> Voltar
                        </a>
                    </div>
                </div>

            </x-form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
/* ── Card ── */
.re-card { border: 1px solid #e5eaf2; border-radius: 14px; overflow: hidden; background: #fff; }
.re-card__header {
    display: flex; align-items: center; gap: 1rem;
    background: linear-gradient(120deg, #f0f4ff 0%, #e8f0fe 100%);
    padding: 1.2rem 1.5rem;
}
.re-card__icon {
    width: 48px; height: 48px; border-radius: 12px;
    background: #4b80ff20; display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; color: #4b80ff; flex-shrink: 0;
}
.re-card__title { font-size: 1.1rem; font-weight: 700; color: #0f2740; margin: 0 0 .2rem; }
.re-card__sub   { font-size: .82rem; color: #6c757d; margin: 0; }
.re-card__body  { padding: 1.5rem; }

.re-section-title {
    font-size: .88rem; font-weight: 700; color: #21364f;
    border-bottom: 2px solid #e8f0fe; padding-bottom: 6px; margin-bottom: 1rem;
}
.re-input { border-radius: 8px; border-color: #d8e1ec; }
.re-btn-save   { border-radius: 10px; font-weight: 600; }
.re-btn-cancel { border-radius: 10px; }
.gap-2 { gap: .5rem; }

/* ── Toolbar ── */
.re-perm-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: .6rem;
    background: #f8faff; border: 1px solid #e5eaf2; border-radius: 10px 10px 0 0;
    padding: .6rem .9rem; margin-bottom: 0;
}
.re-perm-toolbar__left { display: flex; align-items: center; gap: .6rem; flex: 1; min-width: 200px; }
.re-perm-toolbar__right { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.re-perm-search-wrap { position: relative; width: 100%; max-width: 300px; }
.re-perm-search-icon { position: absolute; left: 9px; top: 50%; transform: translateY(-50%); color: #9aacbf; font-size: .78rem; }
.re-perm-search {
    width: 100%; padding: .3rem .6rem .3rem 2rem;
    border: 1px solid #d8e1ec; border-radius: 8px;
    font-size: .82rem; background: #fff; color: #21364f;
}
.re-perm-search:focus { outline: none; border-color: #4b80ff; box-shadow: 0 0 0 .12rem rgba(75,128,255,.18); }
.re-counter { font-size: .78rem; color: #6c757d; white-space: nowrap; }
.re-btn-sm {
    font-size: .75rem; font-weight: 600; padding: .25rem .65rem;
    border-radius: 7px; border: 1px solid; cursor: pointer; white-space: nowrap;
}
.re-btn-check   { background: #e8f5e9; border-color: #c3e6cb; color: #155724; }
.re-btn-uncheck { background: #fef3f3; border-color: #f5c6cb; color: #721c24; }

/* ── Accordion ── */
.re-accordion { border: 1px solid #e5eaf2; border-top: none; border-radius: 0 0 12px 12px; overflow: hidden; }
.re-group     { border-bottom: 1px solid #eef1f7; }
.re-group:last-child { border-bottom: none; }
.re-group.re-hidden { display: none; }

.re-group__header {
    display: flex; align-items: center; justify-content: space-between;
    padding: .65rem 1rem; cursor: pointer; background: #fafbfd;
    user-select: none; transition: background .15s; gap: .5rem;
}
.re-group__header:hover { background: #f0f4ff; }
.re-group__left  { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.re-group__dot   { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.re-group__icon  { font-size: .9rem; width: 16px; text-align: center; flex-shrink: 0; }
.re-group__name  { font-weight: 600; font-size: .85rem; color: #21364f; }
.re-group__badge { font-size: .68rem; font-weight: 700; padding: .1rem .45rem; border-radius: 999px; }
.re-group__badge--total   { background: #f0f3f8; color: #6c757d; border: 1px solid #e5eaf2; }
.re-group__badge--checked { border: 1px solid; }

.re-group__actions { display: flex; align-items: center; gap: .4rem; }
.re-btn-group-all, .re-btn-group-none {
    width: 22px; height: 22px; border: 1px solid #d8e1ec; border-radius: 6px;
    background: #fff; cursor: pointer; font-size: .7rem; color: #6c757d;
    display: flex; align-items: center; justify-content: center; padding: 0;
    transition: all .12s;
}
.re-btn-group-all:hover  { background: #e8f5e9; border-color: #28a745; color: #28a745; }
.re-btn-group-none:hover { background: #fef3f3; border-color: #dc3545; color: #dc3545; }
.re-group__chevron { font-size: .7rem; color: #aab5c4; transition: transform .2s; margin-left: .2rem; }
.re-group__header:not(.collapsed) .re-group__chevron { transform: rotate(180deg); }

/* ── Group body + grid ── */
.re-group__body { background: #fff; padding: .8rem; }

/* ── Subníveis (árvore dentro do módulo) ── */
.re-sub { margin-bottom: .45rem; }
.re-sub .re-sub { margin-left: 1.1rem; }
.re-sub__head {
    display: flex; align-items: center; gap: .45rem;
    padding: .4rem .6rem; border-radius: 8px;
    background: #f6f8fc; border: 1px solid #e9edf5;
    cursor: pointer; user-select: none; transition: background .12s;
}
.re-sub__head:hover { background: #eef2fa; }
.re-sub__chev   { font-size: .62rem; color: #8ba0b8; transition: transform .2s; width: 10px; }
.re-sub__head:not(.collapsed) .re-sub__chev { transform: rotate(90deg); }
.re-sub__icon   { font-size: .75rem; color: #94a9c4; }
.re-sub__label  { font-size: .78rem; font-weight: 600; color: #2c4a6e; }
.re-sub__count  { font-size: .65rem; font-weight: 700; color: #8ba0b8; background: #e8edf5;
                  padding: .05rem .4rem; border-radius: 999px; }
.re-sub__checked{ font-size: .65rem; font-weight: 700; color: #155724; background: #e8f5e9;
                  border: 1px solid #c3e6cb; padding: .05rem .4rem; border-radius: 999px; }
.re-sub__actions { margin-left: auto; display: flex; gap: .3rem; }
.re-sub__body   { padding: .55rem .3rem .2rem 1.1rem; border-left: 2px solid #edf1f8; margin-left: .45rem; }

.re-perm-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 6px;
}

/* ── Permission item ── */
.re-perm-item {
    display: flex; align-items: flex-start; gap: 8px;
    border: 1px solid #e5eaf2; border-radius: 9px; padding: 8px 10px;
    cursor: pointer; background: #fff; transition: all .12s;
    margin: 0;
}
.re-perm-item:hover { border-color: #b9c9e0; background: #f8fbff; }
.re-perm-item--checked { border-color: #4b80ff; background: #f4f8ff; }
.re-perm-item__chk { flex: 0 0 auto; margin-top: 3px; }
.re-perm-item__body { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.re-perm-item__badge { font-size: .65rem; width: fit-content; }
.re-perm-item__name {
    font-family: 'SFMono-Regular', Consolas, monospace;
    font-size: .72rem; color: #2c4a6e;
    background: #f0f4ff; padding: .05rem .3rem; border-radius: 4px;
    word-break: break-all; line-height: 1.4;
}
.re-perm-item--checked .re-perm-item__name { background: #dce9ff; }
.re-perm-item__desc { font-size: .7rem; color: #6c757d; line-height: 1.3; }

/* Hidden by search */
.re-perm-item.re-hidden { display: none; }

/* ── Responsive ── */
@media (max-width: 1199px) { .re-perm-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 767px)  { .re-perm-grid { grid-template-columns: 1fr; } .re-perm-toolbar { flex-direction: column; align-items: stretch; } .re-perm-toolbar__right { justify-content: flex-end; } }
</style>
@stop

@section('js')
<script>
$(function () {
    const $all    = $('input[name="permissions[]"]');
    const $counter = $('#reContador');
    const $busca   = $('#reBusca');

    // ── Contador e visual checked ──────────────────────────────────────────
    function atualizarContador() {
        const n = $all.filter(':checked').length;
        $counter.text(n);
    }

    function atualizarVisual($chk) {
        $chk.each(function () {
            $(this).closest('.re-perm-item').toggleClass('re-perm-item--checked', this.checked);
        });
    }

    function atualizarBadgeGrupo(accordionId) {
        const $grids  = $(`[data-grid="${accordionId}"]`);
        const total   = $grids.find('input[name="permissions[]"]:checked').length;
        const $badge  = $(`#badge-${accordionId}`);
        if (total > 0) {
            const color = $badge.data('color') || '#4b80ff';
            $badge.css({ background: color + '20', color: color, borderColor: color + '60' });
            $badge.text(total + (total === 1 ? ' ativa' : ' ativas')).show();
        } else {
            $badge.hide();
        }
    }

    function atualizarTodosBadges() {
        $('[data-grid]').each(function () {
            atualizarBadgeGrupo($(this).data('grid'));
        });
    }

    function atualizarBadgesSubniveis($el) {
        $el.parents('.collapse').each(function () {
            const n  = $(this).find('input[name="permissions[]"]:checked').length;
            const $b = $(`#subbadge-${this.id}`);
            if ($b.length) { n > 0 ? $b.text(n).show() : $b.hide(); }
        });
    }

    $all.on('change', function () {
        atualizarVisual($(this));
        atualizarContador();
        const grid = $(this).closest('[data-grid]').data('grid');
        if (grid) atualizarBadgeGrupo(grid);
        atualizarBadgesSubniveis($(this));
    });

    // Init
    atualizarVisual($all);
    atualizarContador();
    atualizarTodosBadges();

    // ── Selecionar / desmarcar todos ──────────────────────────────────────
    $('#reSelecionarTodos').on('click', function () {
        const $visible = $all.filter(function () {
            return $(this).closest('.re-perm-item').not('.re-hidden').length > 0;
        });
        $visible.prop('checked', true);
        atualizarVisual($visible);
        atualizarContador();
        atualizarTodosBadges();
    });

    $('#reDesmarcarTodos').on('click', function () {
        const $visible = $all.filter(function () {
            return $(this).closest('.re-perm-item').not('.re-hidden').length > 0;
        });
        $visible.prop('checked', false);
        atualizarVisual($visible);
        atualizarContador();
        atualizarTodosBadges();
    });

    // ── Selecionar / desmarcar por grupo ou subnível ──────────────────────
    $(document).on('click', '.re-btn-group-all', function () {
        const gid = $(this).data('group');
        const sub = $(this).data('sub');
        const $scope = gid ? $(`[data-grid="${gid}"]`) : $(`#${sub}`);
        const $chks = $scope.find('input[name="permissions[]"]').filter(function () {
            return $(this).closest('.re-perm-item').not('.re-hidden').length > 0;
        });
        $chks.prop('checked', true);
        atualizarVisual($chks);
        atualizarContador();
        if (gid) atualizarBadgeGrupo(gid);
        else if ($chks.length) { atualizarBadgeGrupo($chks.first().closest('[data-grid]').data('grid')); }
        atualizarBadgesSubniveis($chks.first());
        // Abrir o grupo/subnível se estiver fechado
        const $body = gid ? $(`#${gid}`) : $(`#${sub}`);
        if (!$body.hasClass('show')) $body.collapse('show');
    });

    $(document).on('click', '.re-btn-group-none', function () {
        const gid = $(this).data('group');
        const sub = $(this).data('sub');
        const $scope = gid ? $(`[data-grid="${gid}"]`) : $(`#${sub}`);
        const $chks = $scope.find('input[name="permissions[]"]');
        $chks.prop('checked', false);
        atualizarVisual($chks);
        atualizarContador();
        if (gid) atualizarBadgeGrupo(gid);
        else if ($chks.length) { atualizarBadgeGrupo($chks.first().closest('[data-grid]').data('grid')); }
        atualizarBadgesSubniveis($chks.first());
    });

    // ── Busca ao vivo ──────────────────────────────────────────────────────
    $busca.on('input', function () {
        const q = $(this).val().toLowerCase().trim();

        $('.re-group').each(function () {
            const $group = $(this);
            const grupo  = ($group.data('grupo') || '').toLowerCase();
            let grupoVisivel = false;

            $group.find('.re-perm-item').each(function () {
                const name = $(this).data('name') || '';
                const desc = $(this).data('desc') || '';
                const match = !q || name.includes(q) || desc.includes(q) || grupo.includes(q);
                $(this).toggleClass('re-hidden', !match);
                if (match) grupoVisivel = true;
            });

            $group.toggleClass('re-hidden', !grupoVisivel);

            // Auto-expandir grupos com resultado
            if (q && grupoVisivel) {
                const $body = $group.find('.collapse');
                if (!$body.hasClass('show')) $body.collapse('show');
            }
        });
    });

    // ── Auto-expandir grupos com permissões ativas ─────────────────────────
    $('[data-grid]').each(function () {
        const gid  = $(this).data('grid');
        const qtd  = $(this).find('input:checked').length;
        if (qtd > 0) {
            $(`#${gid}`).collapse('show');
        }
    });
});
</script>
@stop
