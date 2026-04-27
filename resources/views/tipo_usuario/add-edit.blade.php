@extends('adminlte::page')

@section('title', isset($tipoUsuario) ? 'Editar Perfil' : 'Novo Perfil de Acesso')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title
        :title="isset($tipoUsuario) ? 'Editar Perfil: ' . $tipoUsuario->name : 'Novo Perfil de Acesso'"
        subtitle="Configure o nome do perfil e quais funcionalidades ele pode acessar." />
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        // Módulos exibíveis com ícone e label amigável.
        // Módulos de plataforma (empresa, regra, permissao, etc.) são removidos no service.
        $moduloMeta = [
            'reserva'        => ['label' => 'Reservas',             'icon' => 'fas fa-calendar-check',      'cor' => '#3b82f6'],
            'veiculo'        => ['label' => 'Veículos',             'icon' => 'fas fa-car',                 'cor' => '#10b981'],
            'frota'          => ['label' => 'Frota',                'icon' => 'fas fa-car-side',            'cor' => '#10b981'],
            'grupo_frota'    => ['label' => 'Grupos de Frota',      'icon' => 'fas fa-layer-group',         'cor' => '#6366f1'],
            'estacao'        => ['label' => 'Estações',             'icon' => 'fas fa-building',            'cor' => '#f59e0b'],
            'horario_diario' => ['label' => 'Horários de Funcionamento','icon'=>'fas fa-clock',             'cor' => '#f59e0b'],
            'usuario'        => ['label' => 'Usuários',             'icon' => 'fas fa-users',               'cor' => '#8b5cf6'],
            'financeiro'     => ['label' => 'Financeiro',           'icon' => 'fas fa-file-invoice-dollar', 'cor' => '#059669'],
            'transacao'      => ['label' => 'Transações',           'icon' => 'fas fa-exchange-alt',        'cor' => '#059669'],
            'calcao'         => ['label' => 'Caução',               'icon' => 'fas fa-shield-alt',          'cor' => '#0ea5e9'],
            'cupom'          => ['label' => 'Cupons de Desconto',   'icon' => 'fas fa-tag',                 'cor' => '#ec4899'],
            'movimentacao'   => ['label' => 'Movimentação de Frota','icon' => 'fas fa-truck-moving',        'cor' => '#84cc16'],
            'checklist'      => ['label' => 'Checklists',           'icon' => 'fas fa-clipboard-check',     'cor' => '#0d9488'],
            'item_checklist' => ['label' => 'Itens de Checklist',   'icon' => 'fas fa-clipboard-list',     'cor' => '#0d9488'],
            'dano'           => ['label' => 'Danos',                'icon' => 'fas fa-tools',               'cor' => '#ef4444'],
            'categoria_dano' => ['label' => 'Categorias de Dano',  'icon' => 'fas fa-folder-open',         'cor' => '#ef4444'],
            'sinistro'       => ['label' => 'Sinistros',            'icon' => 'fas fa-exclamation-circle',  'cor' => '#dc2626'],
            'avaria'         => ['label' => 'Avarias',              'icon' => 'fas fa-car-crash',           'cor' => '#dc2626'],
            'multa'          => ['label' => 'Multas',               'icon' => 'fas fa-file-invoice',        'cor' => '#f97316'],
            'nfse'           => ['label' => 'NFS-e (Nota Fiscal)',  'icon' => 'fas fa-receipt',             'cor' => '#7c3aed'],
            'opcional'       => ['label' => 'Opcionais',            'icon' => 'fas fa-plus-circle',         'cor' => '#0ea5e9'],
            'modelo'         => ['label' => 'Modelos de Veículo',   'icon' => 'fas fa-car-side',            'cor' => '#64748b'],
            'marca'          => ['label' => 'Marcas',               'icon' => 'fas fa-tags',                'cor' => '#64748b'],
            'caracteristica' => ['label' => 'Características',      'icon' => 'fas fa-list-ul',             'cor' => '#64748b'],
            'site'           => ['label' => 'Site de Reservas',     'icon' => 'fas fa-globe',               'cor' => '#2563eb'],
            'home'           => ['label' => 'Dashboard / Início',   'icon' => 'fas fa-home',                'cor' => '#0a1628'],
            'dashboard'      => ['label' => 'Dashboard',            'icon' => 'fas fa-chart-line',          'cor' => '#0a1628'],
            'relatorio'      => ['label' => 'Relatórios',           'icon' => 'fas fa-chart-bar',           'cor' => '#0369a1'],
            'cliente'        => ['label' => 'Clientes',             'icon' => 'fas fa-user',                'cor' => '#7c3aed'],
            'condutor'       => ['label' => 'Condutores',           'icon' => 'fas fa-id-card',             'cor' => '#7c3aed'],
            'contrato'         => ['label' => 'Contratos',               'icon' => 'fas fa-file-signature',   'cor' => '#0369a1'],
            'status_veiculo'   => ['label' => 'Status de Veículo',       'icon' => 'fas fa-car',              'cor' => '#64748b'],
            'status_veiculos'  => ['label' => 'Status de Veículo',       'icon' => 'fas fa-car',              'cor' => '#64748b'],
            'status_movimentacao'=> ['label'=> 'Status de Movimentação', 'icon' => 'fas fa-exchange-alt',     'cor' => '#64748b'],
            'status_reserva'   => ['label' => 'Status de Reserva',       'icon' => 'fas fa-calendar-check',  'cor' => '#64748b'],
        ];

        // Prefixos que nunca devem aparecer (já filtrados no service, mas garantia extra).
        $prefixosBloqueados = [
            'empresa', 'replicacao_base', 'regra', 'permissao', 'tipo_usuario',
            'plano', 'planos', 'assinatura', 'migrations', 'fipe',
            'powerbi', 'stripe', 'cashier', 'webhook', 'cancel', 'success',
            'subscription', 'profile', 'onboarding',
        ];

        $grupos = [];
        foreach ($permissoes as $permissao) {
            $prefixo = strtolower(explode('.', $permissao->name)[0]);
            if (in_array($prefixo, $prefixosBloqueados)) continue;
            // Normaliza variações
            if (str_contains($prefixo, 'caracteristica')) $prefixo = 'caracteristica';
            if (str_contains($prefixo, 'categoria_dano')) $prefixo = 'categoria_dano';
            if (str_contains($prefixo, 'item_checklist')) $prefixo = 'item_checklist';
            if (!isset($grupos[$prefixo])) $grupos[$prefixo] = [];
            $grupos[$prefixo][] = $permissao;
        }
        ksort($grupos);

        $acaoDesc = [
            'index'                  => 'Listar registros',
            'show'                   => 'Ver detalhes',
            'create'                 => 'Abrir formulário de criação',
            'store'                  => 'Salvar novo registro',
            'edit'                   => 'Abrir formulário de edição',
            'update'                 => 'Salvar alterações',
            'destroy'                => 'Excluir registro',
            'filtro'                 => 'Aplicar filtros',
            'removerFiltro'          => 'Remover filtro',
            'setPerPage'             => 'Definir itens por página',
            'autorizar'              => 'Autorizar operação',
            'capturar'               => 'Capturar valor',
            'liberar'                => 'Liberar / cancelar',
            'cobrar'                 => 'Registrar cobrança',
            'cancelar'               => 'Cancelar',
            'checkin'                => 'Realizar check-in',
            'checkout'               => 'Realizar check-out',
            'emitirNfse'             => 'Emitir nota fiscal (NFS-e)',
            'processarPagamento'     => 'Processar pagamento',
            'confirmarPagamento'     => 'Confirmar pagamento',
            'efetuarPagamento'       => 'Efetuar pagamento',
            'historico'              => 'Ver histórico',
            'export'                 => 'Exportar dados',
            'horarios_diarios'       => 'Ver horários diários',
            'salvar_horarios_diarios'=> 'Salvar horários diários',
            'veiculosDisponiveis'    => 'Consultar veículos disponíveis',
            'disponibilidadeHorarios'=> 'Consultar disponibilidade de horários',
            'adicionarDanoVeiculo'   => 'Registrar dano no veículo',
            'criarContratoCheckin'   => 'Criar contrato no check-in',
            'assinarContratoCheckin' => 'Assinar contrato no check-in',
            'alterarSenha'           => 'Alterar senha',
            'salvarNovaSenha'        => 'Salvar nova senha',
            'metodoPagamento'        => 'Ver métodos de pagamento',
            'adicionarMetodoPagamento'=> 'Adicionar método de pagamento',
        ];
    @endphp

    <div class="row">
        <div class="col-xl-10 col-lg-12">
            <div class="card shadow-sm border-0" style="border-radius:14px;overflow:hidden;">
                <div class="card-body p-4">
                    <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">

                        {{-- Nome do perfil --}}
                        <div class="col-md-6 mb-4">
                            <label class="font-weight-700 mb-1" style="font-size:.875rem;">
                                Nome do Perfil <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="name"
                                   value="{{ old('name', $tipoUsuario->name ?? '') }}"
                                   required placeholder="Ex: Atendente, Supervisor, Gerente Financeiro...">
                            <small class="text-muted">Escolha um nome que descreva bem a função do colaborador.</small>
                            {{-- guard_name oculto, sempre web --}}
                            <input type="hidden" name="guard_name" value="web">
                        </div>

                        @if ($plano ?? null)
                            <div class="col-12 mb-3">
                                <div class="alert alert-light border py-2" style="font-size:.82rem;">
                                    <i class="fas fa-tags text-primary mr-1"></i>
                                    Plano <strong>{{ $plano->nome }}</strong> —
                                    as permissões exibidas abaixo são as disponíveis no seu plano.
                                </div>
                            </div>
                        @endif

                        {{-- Permissões por módulo --}}
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="mb-0" style="font-size:.95rem;font-weight:700;color:#0f172a;">
                                    <i class="fas fa-key text-warning mr-2"></i>Permissões por Módulo
                                </h5>
                                <div style="display:flex;gap:.5rem;">
                                    <button type="button" class="btn btn-xs btn-outline-success" onclick="toggleAll(true)">
                                        <i class="fas fa-check-square mr-1"></i>Marcar tudo
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="toggleAll(false)">
                                        <i class="fas fa-square mr-1"></i>Desmarcar tudo
                                    </button>
                                </div>
                            </div>

                            <div class="permissoes-grupos">
                                @forelse ($grupos as $prefixo => $perms)
                                    @php
                                        $meta     = $moduloMeta[$prefixo] ?? ['label' => ucfirst(str_replace('_', ' ', $prefixo)), 'icon' => 'fas fa-circle', 'cor' => '#94a3b8'];
                                        $marcados = collect($perms)->where('checked', 'checked')->count();
                                        $total    = count($perms);
                                    @endphp
                                    <div class="perm-grupo" id="grupo_{{ $prefixo }}">
                                        <div class="perm-grupo__header" onclick="toggleGrupo('{{ $prefixo }}')">
                                            <div class="perm-grupo__left">
                                                <span class="perm-grupo__icon" style="background:{{ $meta['cor'] }}1a;color:{{ $meta['cor'] }};">
                                                    <i class="{{ $meta['icon'] }}"></i>
                                                </span>
                                                <span>{{ $meta['label'] }}</span>
                                                <span class="perm-grupo__count" id="count_{{ $prefixo }}">{{ $marcados }}/{{ $total }}</span>
                                            </div>
                                            <div class="perm-grupo__actions">
                                                <button type="button" class="btn-grupo-toggle" title="Marcar todos"
                                                        onclick="event.stopPropagation(); toggleGrupoAll('{{ $prefixo }}', true)">
                                                    <i class="fas fa-check-square"></i>
                                                </button>
                                                <button type="button" class="btn-grupo-toggle btn-grupo-toggle--clear" title="Desmarcar todos"
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
                                                        $partes   = explode('.', $permissao->name);
                                                        $acao     = $partes[count($partes) - 1] ?? $permissao->name;
                                                        $desc     = $permissao->descricao ?: ($acaoDesc[$acao] ?? $acao);
                                                    @endphp
                                                    <label class="perm-item {{ ($permissao->checked ?? '') === 'checked' ? 'perm-item--checked' : '' }}"
                                                           data-grupo="{{ $prefixo }}"
                                                           for="perm_{{ $permissao->id }}">
                                                        <input type="checkbox"
                                                               name="permissions[]"
                                                               id="perm_{{ $permissao->id }}"
                                                               value="{{ $permissao->id }}"
                                                               {{ $permissao->checked ?? '' }}
                                                               onchange="onPermChange(this)">
                                                        <div class="perm-item__info">
                                                            <span class="perm-item__desc">{{ $desc }}</span>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Nenhuma permissão disponível para o seu plano atual.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-12 mt-4 d-flex" style="gap:.75rem;">
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="fas fa-save mr-1"></i> Salvar Perfil
                            </button>
                            <a href="{{ route('tipo_usuario.index') }}" class="btn btn-secondary">
                                Cancelar
                            </a>
                        </div>

                    </x-form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.permissoes-grupos { display:flex; flex-direction:column; gap:8px; }
.perm-grupo { border:1px solid #e2e8f0; border-radius:10px; overflow:hidden; background:#fff; }
.perm-grupo__header {
    display:flex; align-items:center; justify-content:space-between;
    padding:10px 14px; background:#f8fafc; cursor:pointer; user-select:none;
    border-bottom:1px solid transparent; transition:background .15s;
}
.perm-grupo__header:hover { background:#f1f5f9; }
.perm-grupo__left { display:flex; align-items:center; gap:8px; font-weight:700; font-size:.88rem; color:#0f172a; }
.perm-grupo__icon {
    width:28px; height:28px; border-radius:7px;
    display:flex; align-items:center; justify-content:center;
    font-size:.8rem; flex-shrink:0;
}
.perm-grupo__count {
    font-size:.72rem; font-weight:600; background:#dbeafe; color:#1d4ed8;
    border-radius:20px; padding:1px 8px;
}
.perm-grupo__actions { display:flex; align-items:center; gap:6px; }
.btn-grupo-toggle { background:none; border:none; color:#10b981; font-size:.88rem; cursor:pointer; padding:2px 5px; border-radius:4px; }
.btn-grupo-toggle--clear { color:#94a3b8; }
.btn-grupo-toggle:hover { background:rgba(0,0,0,.06); }
.perm-grupo__chevron { font-size:.72rem; color:#94a3b8; transition:transform .2s; }
.perm-grupo__chevron.open { transform:rotate(180deg); }
.perm-grupo__body { display:none; padding:12px; border-top:1px solid #e8eef7; background:#fafcff; }
.perm-grupo__body.open { display:block; }
.perm-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:6px; }
.perm-item {
    display:flex; align-items:flex-start; gap:8px;
    border:1px solid #e2e8f0; border-radius:8px; padding:8px 10px;
    cursor:pointer; background:#fff; transition:border-color .12s, background .12s; margin:0;
}
.perm-item:hover { border-color:#93c5fd; background:#f0f7ff; }
.perm-item--checked { border-color:#6ee7b7 !important; background:#f0fdf4 !important; }
.perm-item input[type="checkbox"] { margin-top:2px; flex:0 0 15px; accent-color:#10b981; width:15px; height:15px; }
.perm-item__info { display:flex; flex-direction:column; gap:1px; min-width:0; }
.perm-item__desc { font-size:.78rem; color:#374151; line-height:1.35; }
</style>
@stop

@section('js')
<script>
const limitePermissoesPorRegra = {{ (int) ($limitePermissoesPorRegra ?? 0) }};

function toggleGrupo(prefixo) {
    document.getElementById('body_' + prefixo).classList.toggle('open');
    document.getElementById('chev_' + prefixo).classList.toggle('open');
}

function toggleGrupoAll(prefixo, marcar) {
    document.querySelectorAll('.perm-item[data-grupo="' + prefixo + '"] input[type="checkbox"]')
        .forEach(cb => { cb.checked = marcar; cb.closest('.perm-item').classList.toggle('perm-item--checked', marcar); });
    enforcePermissionLimit();
    updateCount(prefixo);
}

function toggleAll(marcar) {
    document.querySelectorAll('.perm-item input[type="checkbox"]')
        .forEach(cb => { cb.checked = marcar; cb.closest('.perm-item').classList.toggle('perm-item--checked', marcar); });
    enforcePermissionLimit();
    document.querySelectorAll('.perm-grupo').forEach(g => updateCount(g.id.replace('grupo_', '')));
}

function onPermChange(cb) {
    if (limitePermissoesPorRegra > 0 && cb.checked) {
        const total = document.querySelectorAll('.perm-item input[type="checkbox"]:checked').length;
        if (total > limitePermissoesPorRegra) {
            cb.checked = false;
            alert('Este plano permite no máximo ' + limitePermissoesPorRegra + ' permissões por perfil.');
            return;
        }
    }
    cb.closest('.perm-item').classList.toggle('perm-item--checked', cb.checked);
    updateCount(cb.closest('.perm-item').dataset.grupo);
}

function enforcePermissionLimit() {
    if (limitePermissoesPorRegra <= 0) return;
    const marcados = Array.from(document.querySelectorAll('.perm-item input[type="checkbox"]:checked'));
    if (marcados.length <= limitePermissoesPorRegra) return;
    marcados.slice(limitePermissoesPorRegra).forEach(cb => {
        cb.checked = false;
        cb.closest('.perm-item').classList.remove('perm-item--checked');
    });
}

function updateCount(prefixo) {
    const items = document.querySelectorAll('.perm-item[data-grupo="' + prefixo + '"] input[type="checkbox"]');
    const marcados = Array.from(items).filter(cb => cb.checked).length;
    const el = document.getElementById('count_' + prefixo);
    if (el) el.textContent = marcados + '/' + items.length;
}

// Abre grupos que já têm permissões marcadas
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.perm-grupo').forEach(g => {
        const prefixo = g.id.replace('grupo_', '');
        if (g.querySelectorAll('input[type="checkbox"]:checked').length > 0) {
            document.getElementById('body_' + prefixo)?.classList.add('open');
            document.getElementById('chev_' + prefixo)?.classList.add('open');
        }
    });
});
</script>
@stop
