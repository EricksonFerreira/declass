<?php $__env->startSection('title', 'Regras Tributárias'); ?>

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
<?php $component = App\View\Components\PageTitle::resolve(['title' => 'Regras Tributárias (Parâmetros)'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-title'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PageTitle::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
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
    <?php echo $__env->make('financeiro.fluxo_caixa._styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="fc-page">
        <!-- Explanatory Box -->
        <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 8px; background-color: #ebf5ff;">
            <div class="d-flex">
                <div class="mr-3 text-blue" style="font-size: 1.5rem;">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h5 class="font-weight-bold text-dark mb-1">Como funcionam as regras de alíquota na Transição (2026-2033)?</h5>
                    <p class="text-secondary mb-0 text-sm">
                        O motor tributário utiliza esta tabela parametrizada para encontrar as alíquotas correspondentes no momento em que a locação é fechada.
                        Isso evita regras rígidas (*hardcoded*) no código. Em 2026, iniciamos com o período de testes trazendo <strong>0,9% de CBS</strong> e <strong>0,1% de IBS</strong>.
                        A partir de 2027, as alíquotas plenas passam a vigorar conforme regulamentação regional/federal, e o sistema fará a transição automática com base nas datas de início e fim cadastradas.
                    </p>
                </div>
            </div>
        </div>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head">
                    <h5 class="fc-title">
                        <i class="fas fa-cogs"></i>
                        <div>
                            <span>Listagem de Regras Cadastradas</span>
                            <span class="fc-subtitle">Configurações de vigência e percentuais das alíquotas de IBS/CBS</span>
                        </div>
                    </h5>
                </div>
            </div>
            
            <div class="card-body p-0">
                <table class="table fc-table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tributo</th>
                            <th>Operação</th>
                            <th>Alíquota</th>
                            <th>Início Vigência</th>
                            <th>Fim Vigência</th>
                            <th>Gera Crédito</th>
                            <th>Ativo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $regras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $regra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($regra->id); ?></td>
                                <td><span class="badge badge-info"><?php echo e($regra->tributo); ?></span></td>
                                <td><code><?php echo e($regra->tipo_operacao); ?></code></td>
                                <td><strong><?php echo e(number_format($regra->aliquota, 2, ',', '.')); ?>%</strong></td>
                                <td><?php echo e($regra->data_inicio->format('d/m/Y')); ?></td>
                                <td><?php echo e($regra->data_fim ? $regra->data_fim->format('d/m/Y') : 'Vigência Indeterminada'); ?></td>
                                <td>
                                    <span class="badge <?php echo e($regra->gera_credito ? 'badge-success' : 'badge-light'); ?>">
                                        <?php echo e($regra->gera_credito ? 'Sim' : 'Não'); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php if($regra->ativo): ?>
                                        <span class="fc-badge fc-badge--pago">
                                            <i class="fas fa-check"></i> Ativo
                                        </span>
                                    <?php else: ?>
                                        <span class="fc-badge fc-badge--cancelado">
                                            <i class="fas fa-times"></i> Inativo
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Nenhuma regra tributária configurada.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($regras->hasPages()): ?>
                <div class="card-footer py-3">
                    <?php echo e($regras->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/fiscal/regras.blade.php ENDPATH**/ ?>