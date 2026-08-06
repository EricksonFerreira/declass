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
    <?php if (isset($component)) { $__componentOriginal2ff7b3043c692896c2a650ccae21c5e4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4 = $attributes; } ?>
<?php $component = App\View\Components\PageTitle::resolve(['title' => $titulo,'subtitle' => $subtitulo] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-title'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PageTitle::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <a href="<?php echo e(route('planos.hub')); ?>" class="btn btn-xs btn-outline-secondary">
            <i class="fas fa-th-large mr-1"></i> Central
        </a>
        <a href="<?php echo e(route('plano.create')); ?>" class="btn btn-xs btn-primary">
            <i class="fa fa-plus" style="font-size:10px"></i> Novo Plano
        </a>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4)): ?>
<?php $attributes = $__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4; ?>
<?php unset($__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ff7b3043c692896c2a650ccae21c5e4)): ?>
<?php $component = $__componentOriginal2ff7b3043c692896c2a650ccae21c5e4; ?>
<?php unset($__componentOriginal2ff7b3043c692896c2a650ccae21c5e4); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> <?php echo e(session('success')); ?>

            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i> <?php echo e(session('error')); ?>

            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalbe88afad70cdab82a68e885e1a13a928 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbe88afad70cdab82a68e885e1a13a928 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header-card','data' => ['showHeader' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['showHeader' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
        <div class="row">
            <?php $__empty_1 = true; $__currentLoopData = $planos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plano): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card h-100 shadow-sm <?php echo e($plano->destaque ? 'border-primary' : ''); ?>" style="border-width:<?php echo e($plano->destaque ? '2px' : '1px'); ?>;">
                        <?php if($plano->destaque): ?>
                            <div class="card-header bg-primary text-white py-1 text-center" style="font-size:.75rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;">
                                <i class="fas fa-star mr-1"></i> Mais Escolhido
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="mb-0 font-weight-bold"><?php echo e($plano->nome); ?></h5>
                                <span class="badge <?php echo e($plano->ativo ? 'badge-success' : 'badge-secondary'); ?>">
                                    <?php echo e($plano->ativo ? 'Ativo' : 'Inativo'); ?>

                                </span>
                            </div>

                            <div class="mb-3">
                                <div class="text-muted" style="font-size:.8rem;">Mensal</div>
                                <div class="font-weight-bold" style="font-size:1.5rem;color:#0a1628;">
                                    R$ <?php echo e(number_format($plano->valor_mensal, 2, ',', '.')); ?>

                                    <small class="text-muted font-weight-normal" style="font-size:.8rem;">/mes</small>
                                </div>
                                <div class="text-muted" style="font-size:.8rem;">
                                    Anual: R$ <?php echo e(number_format($plano->valor_anual, 2, ',', '.')); ?>/mes
                                    <?php if($plano->desconto_anual_percentual > 0): ?>
                                        <span class="badge badge-success ml-1"><?php echo e($plano->desconto_anual_percentual); ?>% off</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            
                            <div class="mb-3 flex-grow-1">
                                
                                <div class="d-flex align-items-center mb-1" style="font-size:.82rem;">
                                    <i class="fas fa-car text-primary mr-2" style="width:14px;text-align:center;"></i>
                                    <span><strong><?php echo e($plano->limite_veiculos_texto); ?></strong> veiculos</span>
                                </div>
                                
                                <div class="d-flex align-items-center mb-1" style="font-size:.82rem;">
                                    <i class="fas fa-car text-primary mr-2" style="width:14px;text-align:center;"></i>
                                    <span><strong><?php echo e($plano->limite_veiculos_texto); ?></strong> carro(s)</span>
                                </div>
                                
                                <div class="d-flex align-items-center mb-1" style="font-size:.82rem;">
                                    <i class="fas fa-map-marker-alt <?php echo e($plano->multi_estacao ? 'text-success' : 'text-muted'); ?> mr-2" style="width:14px;text-align:center;"></i>
                                    <span class="<?php echo e($plano->multi_estacao ? '' : 'text-muted'); ?>">
                                        <?php echo e($plano->multi_estacao ? 'Multi-estacoes' : '1 estacao'); ?>

                                    </span>
                                </div>
                                
                                <div class="d-flex align-items-center mb-1" style="font-size:.82rem;">
                                    <i class="fas fa-globe <?php echo e($plano->site_vendas ? 'text-success' : 'text-muted'); ?> mr-2" style="width:14px;text-align:center;"></i>
                                    <span class="<?php echo e($plano->site_vendas ? '' : 'text-muted'); ?>">
                                        Site de vendas
                                        <?php if(!$plano->site_vendas): ?><i class="fas fa-times text-secondary ml-1" style="font-size:.65rem;"></i><?php endif; ?>
                                    </span>
                                </div>
                                
                                <div class="d-flex align-items-center mb-1" style="font-size:.82rem;">
                                    <i class="fas fa-file-invoice <?php echo e($plano->modulo_multas ? 'text-success' : 'text-muted'); ?> mr-2" style="width:14px;text-align:center;"></i>
                                    <span class="<?php echo e($plano->modulo_multas ? '' : 'text-muted'); ?>">
                                        Modulo de multas
                                        <?php if(!$plano->modulo_multas): ?><i class="fas fa-times text-secondary ml-1" style="font-size:.65rem;"></i><?php endif; ?>
                                    </span>
                                </div>
                                
                                <div class="d-flex align-items-center mb-1" style="font-size:.82rem;">
                                    <i class="fas fa-receipt <?php echo e($plano->modulo_nfse ? 'text-success' : 'text-muted'); ?> mr-2" style="width:14px;text-align:center;"></i>
                                    <span class="<?php echo e($plano->modulo_nfse ? '' : 'text-muted'); ?>">
                                        NFS-e
                                        <?php if(!$plano->modulo_nfse): ?><i class="fas fa-times text-secondary ml-1" style="font-size:.65rem;"></i><?php endif; ?>
                                    </span>
                                </div>
                                
                                <div class="d-flex align-items-center mb-1" style="font-size:.82rem;">
                                    <i class="fas fa-plug <?php echo e($plano->api_integracao ? 'text-success' : 'text-muted'); ?> mr-2" style="width:14px;text-align:center;"></i>
                                    <span class="<?php echo e($plano->api_integracao ? '' : 'text-muted'); ?>">
                                        API de integracao
                                        <?php if(!$plano->api_integracao): ?><i class="fas fa-times text-secondary ml-1" style="font-size:.65rem;"></i><?php endif; ?>
                                    </span>
                                </div>
                                
                                <?php $__currentLoopData = [
                                    ['calendar-check','Reservas completas'],
                                    ['file-signature','Contratos digitais'],
                                    ['dollar-sign',   'Controle financeiro'],
                                    ['users',         'Multiplos usuarios'],
                                    ['chart-bar',     'Relatorios e exportacao'],
                                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$icon, $label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="d-flex align-items-center mb-1 text-muted" style="font-size:.78rem;">
                                        <i class="fas fa-<?php echo e($icon); ?> text-success mr-2" style="width:14px;text-align:center;font-size:.7rem;"></i>
                                        <span><?php echo e($label); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                            <div class="text-muted mb-3" style="font-size:.8rem;">
                                <?php
                                    $storageMb = $plano->limite_armazenamento_mb ?? 0;
                                    $storageTxt = $storageMb >= 1024 ? number_format($storageMb / 1024, 1, ',', '.') . ' GB' : $storageMb . ' MB';
                                ?>
                                <i class="fas fa-database mr-1"></i>
                                <?php echo e($storageTxt); ?> de armazenamento
                                <br>
                                <i class="fas fa-users mr-1"></i>
                                <?php echo e($plano->assinaturas_count); ?> assinatura(s)
                                <br>
                                <i class="fas fa-user-shield mr-1"></i>
                                <?php echo e($plano->limite_permissoes_por_regra ? $plano->limite_permissoes_por_regra . ' permissões por perfil' : 'Permissões por perfil sem limite'); ?>

                            </div>

                            <div class="mt-auto d-flex flex-wrap" style="gap:6px;">
                                <a href="<?php echo e(route('plano.edit', $plano)); ?>" class="btn btn-outline-secondary btn-sm flex-fill">
                                    <i class="fas fa-edit mr-1"></i> Editar
                                </a>
                                <a href="<?php echo e(route('plano.acessos', $plano)); ?>" class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="fas fa-user-shield mr-1"></i> Acessos
                                </a>
                                <?php if($plano->assinaturas_count === 0): ?>
                                    <form action="<?php echo e(route('plano.destroy', $plano)); ?>" method="POST"
                                          onsubmit="return confirm('Remover o plano <?php echo e(addslashes($plano->nome)); ?>?')">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        Nenhum plano cadastrado ainda.
                        <a href="<?php echo e(route('plano.create')); ?>">Criar primeiro plano</a>.
                    </div>
                </div>
            <?php endif; ?>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbe88afad70cdab82a68e885e1a13a928)): ?>
<?php $attributes = $__attributesOriginalbe88afad70cdab82a68e885e1a13a928; ?>
<?php unset($__attributesOriginalbe88afad70cdab82a68e885e1a13a928); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbe88afad70cdab82a68e885e1a13a928)): ?>
<?php $component = $__componentOriginalbe88afad70cdab82a68e885e1a13a928; ?>
<?php unset($__componentOriginalbe88afad70cdab82a68e885e1a13a928); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/plano/index.blade.php ENDPATH**/ ?>