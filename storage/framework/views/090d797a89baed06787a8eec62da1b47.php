<?php $__env->startSection('title', $titulo); ?>

<?php $__env->startSection('content_header'); ?>
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['breadcrumbs' => $breadcrumb ?? []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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

<?php $__env->startSection('css'); ?>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    /* =====================================================
       PADRÃO VISUAL DO SISTEMA (mesmo da tela de projeto)
    ===================================================== */
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f9; }

    .notif-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 18px rgba(0,0,0,0.05);
    }
    .notif-card > .card-header:first-child { border-radius: 12px 12px 0 0; }

    .notif-icon {
        width: 44px; height: 44px; border-radius: 12px;
        background: #fef3c7; color: #d97706;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }

    .notif-badge {
        padding: .42rem .78rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 800;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Cabeçalho -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card notif-card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="notif-icon mr-3"><i class="fas fa-bell"></i></span>
                        <div>
                            <h2 class="font-weight-bold text-dark mb-1" style="letter-spacing: -0.02em;">Central de Notificações</h2>
                            <p class="text-muted mb-0">
                                Alertas de prazos, eventos e avisos do sistema
                                <span class="mx-2 text-muted">|</span>
                                <span class="badge badge-<?php echo e(($naoLidas ?? 0) > 0 ? 'danger' : 'success'); ?> px-2 py-1 text-xs">
                                    <?php echo e($naoLidas ?? 0); ?> não lida(s)
                                </span>
                            </p>
                        </div>
                    </div>
                    <?php if(($naoLidas ?? 0) > 0): ?>
                        <form method="POST" action="<?php echo e(route('notificacoes.marcarTodasComoLidas')); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <button class="btn btn-primary font-weight-bold shadow-sm">
                                <i class="fas fa-check-double mr-1"></i> Marcar todas como lidas
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Listagem -->
<div class="row">
    <div class="col-12">
        <div class="card notif-card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h3 class="card-title text-bold text-dark mb-0">
                    <i class="fas fa-list text-primary mr-2"></i> Lista de Notificações
                    <small class="text-muted font-weight-normal ml-2">— marque como lidas quando necessário</small>
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr class="text-xs text-uppercase">
                                <th class="pl-4">Status</th>
                                <th>Assunto</th>
                                <th>Mensagem</th>
                                <th>Data</th>
                                <th class="text-center" style="width: 190px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $notificacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notificacao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $dados = $notificacao->data ?? [];
                                    $icone = $dados['icone'] ?? 'fas fa-bell';
                                    $cor = $dados['cor'] ?? 'warning';
                                    $tituloItem = $dados['titulo'] ?? 'Notificação';
                                    $mensagem = $dados['mensagem'] ?? '';
                                    $url = $dados['url'] ?? null;
                                    $lida = (bool) $notificacao->read_at;
                                ?>
                                <tr class="<?php echo e($lida ? '' : 'table-warning'); ?>">
                                    <td class="pl-4 align-middle">
                                        <span class="notif-badge badge badge-<?php echo e($cor); ?>"><i class="<?php echo e($icone); ?>"></i></span>
                                        <?php if(!$lida): ?>
                                            <span class="badge badge-danger px-2 py-1 text-xs ml-1">Nova</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary px-2 py-1 text-xs ml-1">Lida</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle font-weight-bold text-dark text-sm"><?php echo e($tituloItem); ?></td>
                                    <td class="align-middle text-muted text-sm"><?php echo e($mensagem); ?></td>
                                    <td class="align-middle text-sm text-secondary"><?php echo e($notificacao->created_at?->format('d/m/Y H:i')); ?></td>
                                    <td class="align-middle text-center">
                                        <?php if($url): ?>
                                            <a href="<?php echo e($url); ?>" class="btn btn-xs btn-info font-weight-bold mr-1">
                                                <i class="fas fa-external-link-alt mr-1"></i> Abrir
                                            </a>
                                        <?php endif; ?>
                                        <?php if(!$lida): ?>
                                            <form method="POST" action="<?php echo e(route('notificacoes.marcarLida', $notificacao->id)); ?>" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button class="btn btn-xs btn-success font-weight-bold">
                                                    <i class="fas fa-check mr-1"></i> Marcar lida
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="far fa-bell-slash fa-3x mb-3 text-muted"></i>
                                        <h5 class="font-weight-bold">Nenhuma notificação encontrada</h5>
                                        <p class="text-sm text-muted mb-0">Quando houver avisos e eventos do sistema, eles aparecem aqui.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if($notificacoes->hasPages()): ?>
                <div class="card-footer bg-white py-2 d-flex justify-content-end border-top">
                    <?php echo e($notificacoes->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\notificacoes\index.blade.php ENDPATH**/ ?>