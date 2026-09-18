<?php $__env->startSection('title', $titulo); ?>

<?php $__env->startSection('content_header'); ?>
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['breadcrumbs' => $breadcrumb] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Breadcrumb::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal269900abaed345884ce342681cdc99f6)): ?>
<?php $attributes = $__attributesOriginal269900abaed345884ce342681cdc99f6; ?>
<?php unset($__attributesOriginal269900abaed345884ce342681cdc99f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal269900abaed345884ce342681cdc99f6)): ?>
<?php $component = $__componentOriginal269900abaed345884ce342681cdc99f6; ?>
<?php unset($__componentOriginal269900abaed345884ce342681cdc99f6); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $grupoMeta = [
        'reserva'           => ['label' => 'Reservas',           'icon' => 'fas fa-calendar-check',      'color' => '#4b80ff'],
        'veiculo'           => ['label' => 'Veículos',           'icon' => 'fas fa-car',                 'color' => '#28a745'],
        'usuario'           => ['label' => 'Usuários',           'icon' => 'fas fa-users',               'color' => '#17a2b8'],
        'permissao'         => ['label' => 'Permissões',         'icon' => 'fas fa-key',                 'color' => '#e0a800'],
        'regra'             => ['label' => 'Regras',             'icon' => 'fas fa-shield-alt',          'color' => '#e0a800'],
        'multa'             => ['label' => 'Multas',             'icon' => 'fas fa-file-invoice-dollar', 'color' => '#dc3545'],
        'movimentacao'      => ['label' => 'Movimentações',      'icon' => 'fas fa-exchange-alt',        'color' => '#6c757d'],
        'financeiro'        => ['label' => 'Financeiro',         'icon' => 'fas fa-dollar-sign',         'color' => '#28a745'],
        'calcao'            => ['label' => 'Caução',             'icon' => 'fas fa-hand-holding-usd',    'color' => '#28a745'],
        'empresa'           => ['label' => 'Empresa',            'icon' => 'fas fa-building',            'color' => '#343a40'],
        'estacao'           => ['label' => 'Estações',           'icon' => 'fas fa-map-marker-alt',      'color' => '#17a2b8'],
        'modelo'            => ['label' => 'Modelos',            'icon' => 'fas fa-car-side',            'color' => '#6c757d'],
        'nfse'              => ['label' => 'NFS-e',              'icon' => 'fas fa-file-alt',            'color' => '#343a40'],
        'pagamento'         => ['label' => 'Pagamento',          'icon' => 'fas fa-credit-card',         'color' => '#28a745'],
        'dashboard'         => ['label' => 'Dashboard',          'icon' => 'fas fa-chart-bar',           'color' => '#17a2b8'],
        '_outros'           => ['label' => 'Outros',             'icon' => 'fas fa-ellipsis-h',          'color' => '#6c757d'],
    ];

    $acaoBadge = [
        'index'   => ['Listar',    '#4b80ff'],
        'show'    => ['Ver',       '#17a2b8'],
        'create'  => ['Criar',     '#28a745'],
        'store'   => ['Salvar',    '#28a745'],
        'edit'    => ['Editar',    '#e0a800'],
        'update'  => ['Atualizar', '#e0a800'],
        'destroy' => ['Excluir',   '#dc3545'],
        'delete'  => ['Excluir',   '#dc3545'],
    ];
?>

<div class="rg-page">

    
    <div class="rg-hero mb-4">
        <div class="rg-hero__left">
            <div class="rg-hero__icon"><i class="fas fa-shield-alt"></i></div>
            <div>
                <h2 class="rg-hero__title">Regras de Acesso</h2>
                <p class="rg-hero__sub">Agrupe permissões em perfis e atribua-os aos usuários para controlar o acesso ao sistema.</p>
            </div>
        </div>
        <a href="<?php echo e(route('regra.create')); ?>" class="btn btn-primary rg-hero__btn">
            <i class="fas fa-plus mr-1"></i> Nova Regra
        </a>
    </div>

    
    <?php if(isset($filters) && count($filters) > 0): ?>
        <?php if (isset($component)) { $__componentOriginalb68d555c13273f9d26aedfa8ff6c880d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d = $attributes; } ?>
<?php $component = App\View\Components\Filter::resolve(['additionalFilters' => $additionalFilters,'filters' => $filters,'itensFiltered' => $itensFiltered] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Filter::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d)): ?>
<?php $attributes = $__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d; ?>
<?php unset($__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb68d555c13273f9d26aedfa8ff6c880d)): ?>
<?php $component = $__componentOriginalb68d555c13273f9d26aedfa8ff6c880d; ?>
<?php unset($__componentOriginalb68d555c13273f9d26aedfa8ff6c880d); ?>
<?php endif; ?>
    <?php endif; ?>

    
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
                <h3 class="card-title text-sm text-bold mb-0">
                    REGISTROS <span class="badge badge-success"><?php echo e($regras->total()); ?></span>
                </h3>
                <div class="d-flex align-items-center flex-wrap" style="gap:6px">
                    <form action="<?php echo e(route('regra.setPerPage')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php if (isset($component)) { $__componentOriginal65c0c73c271c840668c600386ae808f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65c0c73c271c840668c600386ae808f3 = $attributes; } ?>
<?php $component = App\View\Components\PerPageSelector::resolve(['perPages' => $perPages,'selectedPerPage' => $perPage] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('per-page-selector'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PerPageSelector::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal65c0c73c271c840668c600386ae808f3)): ?>
<?php $attributes = $__attributesOriginal65c0c73c271c840668c600386ae808f3; ?>
<?php unset($__attributesOriginal65c0c73c271c840668c600386ae808f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal65c0c73c271c840668c600386ae808f3)): ?>
<?php $component = $__componentOriginal65c0c73c271c840668c600386ae808f3; ?>
<?php unset($__componentOriginal65c0c73c271c840668c600386ae808f3); ?>
<?php endif; ?>
                    </form>
                    <div class="rg-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i> Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i> Tabela
                        </button>
                    </div>
                    <div class="d-flex align-items-end">
                        <?php echo e($regras->links()); ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 rg-index-body" data-view="cards">

    
    <?php if($regras->isEmpty()): ?>
        <div class="alert alert-light text-center py-5">
            <i class="fas fa-shield-alt fa-3x text-muted mb-3 d-block"></i>
            <p class="mb-0 text-muted">Nenhuma regra encontrada.</p>
            <a href="<?php echo e(route('regra.create')); ?>" class="btn btn-primary btn-sm mt-3">
                <i class="fas fa-plus mr-1"></i> Criar primeira regra
            </a>
        </div>
    <?php else: ?>
        <div class="rg-grid">
            <?php $__currentLoopData = $regras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $regra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $totalPerms = count($regra->permissoes);
                    $groupedPerms = $regra->permissoes->groupBy(
                        fn($p) => $p->grupo_efetivo ?: '_outros'
                    );
                    $totalGrupos = $groupedPerms->count();
                    $cardId = 'rg-card-' . $regra->id;
                ?>
                <div class="rg-card" onclick="window.location='<?php echo e(route('regra.show', $regra->id)); ?>';" style="cursor:pointer">

                    
                    <div class="rg-card__header">
                        <div class="rg-card__icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="rg-card__info">
                            <h5 class="rg-card__name"><?php echo e($regra->name); ?></h5>
                            <div class="rg-card__meta">
                                <span class="rg-card__guard"><i class="fas fa-lock mr-1" style="font-size:.7rem"></i><?php echo e($regra->guard_name); ?></span>
                            </div>
                        </div>
                        <div class="rg-card__actions" onclick="event.stopPropagation()">
                            <a href="<?php echo e(route('regra.show', $regra->id)); ?>" class="btn btn-xs btn-outline-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo e(route('regra.edit', $regra->id)); ?>" class="btn btn-xs btn-outline-warning" title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                            <?php if (isset($component)) { $__componentOriginal2550ac35d7599d92e03b1bde3934d26a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2550ac35d7599d92e03b1bde3934d26a = $attributes; } ?>
<?php $component = App\View\Components\DeleteButton::resolve(['action' => route('regra.destroy', $regra->id)] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('delete-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\DeleteButton::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2550ac35d7599d92e03b1bde3934d26a)): ?>
<?php $attributes = $__attributesOriginal2550ac35d7599d92e03b1bde3934d26a; ?>
<?php unset($__attributesOriginal2550ac35d7599d92e03b1bde3934d26a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2550ac35d7599d92e03b1bde3934d26a)): ?>
<?php $component = $__componentOriginal2550ac35d7599d92e03b1bde3934d26a; ?>
<?php unset($__componentOriginal2550ac35d7599d92e03b1bde3934d26a); ?>
<?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="rg-card__stats">
                        <span class="rg-badge rg-badge--blue">
                            <i class="fas fa-key mr-1"></i><?php echo e($totalPerms); ?> permissão<?php echo e($totalPerms !== 1 ? 'ões' : ''); ?>

                        </span>
                        <span class="rg-badge rg-badge--gray">
                            <i class="fas fa-th-large mr-1"></i><?php echo e($totalGrupos); ?> módulo<?php echo e($totalGrupos !== 1 ? 's' : ''); ?>

                        </span>
                        <?php if($totalPerms > 0): ?>
                            <button type="button"
                                class="btn btn-xs rg-btn-expand ml-auto"
                                data-toggle="collapse"
                                data-target="#<?php echo e($cardId); ?>"
                                aria-expanded="false">
                                <i class="fas fa-chevron-down"></i>
                                <span class="rg-btn-expand__lbl">Ver permissões</span>
                            </button>
                        <?php endif; ?>
                    </div>

                    
                    <?php if($totalPerms > 0): ?>
                        <div id="<?php echo e($cardId); ?>" class="collapse rg-card__perms">
                            <div class="rg-perms-inner">
                                <?php $__currentLoopData = $groupedPerms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo => $perms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $gm = $grupoMeta[$grupo] ?? $grupoMeta['_outros'];
                                        $hex = $gm['color'];
                                    ?>
                                    <div class="rg-perm-group">
                                        <div class="rg-perm-group__header" style="color:<?php echo e($hex); ?>">
                                            <i class="<?php echo e($gm['icon']); ?> mr-1"></i>
                                            <span><?php echo e($gm['label']); ?></span>
                                            <span class="rg-perm-group__count" style="background:<?php echo e($hex); ?>20;color:<?php echo e($hex); ?>">
                                                <?php echo e($perms->count()); ?>

                                            </span>
                                        </div>
                                        <div class="rg-perm-group__items">
                                            <?php $__currentLoopData = $perms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $acao = str_contains($p->name, '.') ? explode('.', $p->name)[1] : $p->name;
                                                    [$acaoLabel, $acaoColor] = $acaoBadge[$acao] ?? [$acao, '#6c757d'];
                                                ?>
                                                <div class="rg-perm-item" title="<?php echo e($p->name); ?>">
                                                    <span class="rg-perm-item__badge" style="background:<?php echo e($acaoColor); ?>20;color:<?php echo e($acaoColor); ?>">
                                                        <?php echo e($acaoLabel); ?>

                                                    </span>
                                                    <span class="rg-perm-item__desc">
                                                        <?php echo e($p->descricao ?: $acao); ?>

                                                    </span>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div class="rg-table table-responsive">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Guard</th>
                        <th>Permissões</th>
                        <th>Módulos</th>
                        <th style="width:120px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $regras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $regra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $totalPerms = count($regra->permissoes);
                            $totalGrupos = $regra->permissoes->groupBy(fn($p) => $p->grupo_efetivo ?: '_outros')->count();
                        ?>
                        <tr onclick="window.location='<?php echo e(route('regra.show', $regra->id)); ?>';" style="cursor:pointer">
                            <td><?php echo e($regra->id); ?></td>
                            <td><strong><?php echo e($regra->name); ?></strong></td>
                            <td><span class="badge badge-secondary"><?php echo e($regra->guard_name); ?></span></td>
                            <td><span class="badge badge-primary"><?php echo e($totalPerms); ?></span></td>
                            <td><span class="badge badge-info"><?php echo e($totalGrupos); ?></span></td>
                            <td onclick="event.stopPropagation()">
                                <a href="<?php echo e(route('regra.show', $regra->id)); ?>" class="btn btn-xs btn-info">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <a href="<?php echo e(route('regra.edit', $regra->id)); ?>" class="btn btn-xs btn-warning">
                                    <i class="fa fa-edit"></i> Editar
                                </a>
                                <?php if (isset($component)) { $__componentOriginal2550ac35d7599d92e03b1bde3934d26a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2550ac35d7599d92e03b1bde3934d26a = $attributes; } ?>
<?php $component = App\View\Components\DeleteButton::resolve(['action' => route('regra.destroy', $regra->id)] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('delete-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\DeleteButton::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2550ac35d7599d92e03b1bde3934d26a)): ?>
<?php $attributes = $__attributesOriginal2550ac35d7599d92e03b1bde3934d26a; ?>
<?php unset($__attributesOriginal2550ac35d7599d92e03b1bde3934d26a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2550ac35d7599d92e03b1bde3934d26a)): ?>
<?php $component = $__componentOriginal2550ac35d7599d92e03b1bde3934d26a; ?>
<?php unset($__componentOriginal2550ac35d7599d92e03b1bde3934d26a); ?>
<?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-2">
            <?php echo e($regras->links()); ?>

        </div>
    <?php endif; ?>

        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
/* ── Page ── */
.rg-page { padding-bottom: 2rem; }

/* ── Hero ── */
.rg-hero {
    display: flex; align-items: center; justify-content: space-between;
    background: linear-gradient(120deg, #f0f4ff 0%, #e8f0fe 100%);
    border-radius: 14px; padding: 1.25rem 1.5rem; gap: 1rem;
}
.rg-hero__left { display: flex; align-items: center; gap: 1rem; }
.rg-hero__icon {
    width: 52px; height: 52px; border-radius: 14px;
    background: #4b80ff20; display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: #4b80ff; flex-shrink: 0;
}
.rg-hero__title { font-size: 1.2rem; font-weight: 700; color: #0f2740; margin: 0 0 .2rem; }
.rg-hero__sub   { font-size: .82rem; color: #6c757d; margin: 0; }
.rg-hero__btn   { border-radius: 10px; font-weight: 600; white-space: nowrap; }

/* ── Stat chip ── */
.rg-stat {
    display: flex; align-items: center; gap: .8rem;
    background: #e8f0fe; border-radius: 12px; padding: .7rem 1rem;
}
.rg-stat__icon { font-size: 1.3rem; color: #4b80ff; opacity: .8; }
.rg-stat__val  { font-size: 1.25rem; font-weight: 700; color: #0f2740; line-height: 1; }
.rg-stat__lbl  { font-size: .74rem; color: #6c757d; }

/* ── Grid de cards ── */
.rg-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 1rem;
}

/* ── Card ── */
.rg-card {
    border: 1px solid #e5eaf2; border-radius: 14px;
    background: #fff; overflow: hidden;
    transition: box-shadow .15s;
}
.rg-card:hover { box-shadow: 0 4px 18px rgba(75,128,255,.1); }

/* ── Card header ── */
.rg-card__header {
    display: flex; align-items: flex-start; gap: .85rem;
    padding: 1rem 1.1rem .75rem;
    background: linear-gradient(120deg, #f8faff 0%, #f0f4ff 100%);
    border-bottom: 1px solid #edf1fb;
}
.rg-card__icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: #4b80ff18; display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; color: #4b80ff; flex-shrink: 0; margin-top: .1rem;
}
.rg-card__info  { flex: 1; min-width: 0; }
.rg-card__name  { font-size: .97rem; font-weight: 700; color: #0f2740; margin: 0 0 .2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rg-card__guard { font-size: .74rem; color: #8ca0b8; background: #f0f4ff; padding: .1rem .45rem; border-radius: 6px; }
.rg-card__actions { display: flex; gap: .3rem; flex-shrink: 0; }

/* ── Stats bar ── */
.rg-card__stats {
    display: flex; align-items: center; gap: .4rem;
    padding: .6rem 1.1rem; border-bottom: 1px solid #f0f3f8; flex-wrap: wrap;
}
.rg-badge {
    display: inline-flex; align-items: center;
    font-size: .72rem; font-weight: 600; padding: .2rem .55rem; border-radius: 20px;
}
.rg-badge--blue { background: #e8f0fe; color: #4b80ff; }
.rg-badge--gray { background: #f0f3f8; color: #6c757d; }

/* ── Expand button ── */
.rg-btn-expand {
    border: 1px solid #d8e1ec; border-radius: 8px;
    background: #fff; color: #4b80ff; font-size: .72rem; font-weight: 600;
    padding: .2rem .6rem; transition: all .15s;
}
.rg-btn-expand:hover { background: #f0f4ff; }
.rg-btn-expand[aria-expanded="true"] .fa-chevron-down { transform: rotate(180deg); }
.rg-btn-expand .fa-chevron-down { transition: transform .2s; }
.rg-btn-expand__lbl { margin-left: .25rem; }

/* ── Permissões panel ── */
.rg-card__perms { border-top: 1px solid #f0f3f8; }
.rg-perms-inner { padding: .85rem 1.1rem; display: flex; flex-direction: column; gap: .85rem; }

/* ── Grupo de permissões ── */
.rg-perm-group__header {
    display: flex; align-items: center; gap: .4rem;
    font-size: .78rem; font-weight: 700; margin-bottom: .45rem;
}
.rg-perm-group__count {
    font-size: .7rem; font-weight: 700;
    padding: .1rem .4rem; border-radius: 10px; margin-left: auto;
}
.rg-perm-group__items { display: flex; flex-direction: column; gap: .2rem; }

/* ── Item de permissão ── */
.rg-perm-item {
    display: flex; align-items: center; gap: .4rem;
    font-size: .78rem; padding: .2rem .35rem; border-radius: 6px;
    background: #f8faff;
}
.rg-perm-item__badge {
    font-size: .68rem; font-weight: 700; padding: .1rem .4rem;
    border-radius: 5px; flex-shrink: 0;
}
.rg-perm-item__desc { color: #495057; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── View toggle ── */
.rg-view-toggle { display: flex; gap: 5px; }
.rg-view-toggle .btn.active { background: #0f172a; color: #fff; border-color: #0f172a; }

.rg-index-body[data-view="table"] .rg-grid  { display: none; }
.rg-index-body[data-view="cards"] .rg-table { display: none; }

.rg-index-body[data-view="table"] .rg-table {
    max-height: calc(100vh - 360px);
    overflow: auto;
    border-radius: 6px;
}
.rg-index-body[data-view="table"] .rg-table thead th {
    position: sticky; top: 0; z-index: 2; background: #f8fafc;
}

@media (max-width: 576px) {
    .rg-hero { flex-direction: column; align-items: flex-start; }
    .rg-hero__btn { align-self: stretch; text-align: center; }
    .rg-grid { grid-template-columns: 1fr; }
}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
(function () {
    const body = document.querySelector('.rg-index-body');
    const buttons = document.querySelectorAll('.rg-view-toggle [data-view]');
    if (!body || !buttons.length) return;

    function setView(view) {
        body.setAttribute('data-view', view);
        localStorage.setItem('regraIndexView', view);
        buttons.forEach(btn => btn.classList.toggle('active', btn.dataset.view === view));
    }

    const saved = localStorage.getItem('regraIndexView') || 'cards';
    setView(saved);
    buttons.forEach(btn => btn.addEventListener('click', () => setView(btn.dataset.view)));

    // Expand/collapse label
    document.querySelectorAll('[data-target]').forEach(function (el) {
        el.addEventListener('click', function () {
            const lbl = this.querySelector('.rg-btn-expand__lbl');
            if (!lbl) return;
            const expanded = this.getAttribute('aria-expanded') === 'true';
            lbl.textContent = expanded ? 'Ver permissões' : 'Ocultar permissões';
        });
    });
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\regra\index.blade.php ENDPATH**/ ?>