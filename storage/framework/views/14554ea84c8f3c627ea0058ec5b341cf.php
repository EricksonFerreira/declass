<?php $__env->startSection('title', 'Operações Fiscais'); ?>

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
<?php $component = App\View\Components\PageTitle::resolve(['title' => 'Operações Fiscais'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
                    <h5 class="font-weight-bold text-dark mb-1">O que são as Operações Fiscais?</h5>
                    <p class="text-secondary mb-0 text-sm">
                        Cada locação finalizada gera uma <strong>operação fiscal</strong> (fato gerador de tributação). 
                        O motor tributário calcula automaticamente os valores devidos com base na data da operação e nas alíquotas configuradas. 
                        A partir dessa operação, o sistema gera a <strong>DPS (Declaração de Prestação de Serviço)</strong> para emissão da NFS-e Nacional sob o código tributário <code>99.04.01</code> (Locação de Bens Móveis).
                    </p>
                </div>
            </div>
        </div>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head">
                    <h5 class="fc-title">
                        <i class="fas fa-receipt"></i>
                        <div>
                            <span>Listagem de Fatos Geradores</span>
                            <span class="fc-subtitle">Histórico de operações tributadas sob o regime de IBS/CBS</span>
                        </div>
                    </h5>
                </div>
            </div>
            
            <div class="card-body p-0">
                <table class="table fc-table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Locação / Reserva ID</th>
                            <th>Operação</th>
                            <th>Data Fato Gerador</th>
                            <th>Valor Operação</th>
                            <th>Base Cálculo</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $operacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $operacao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($operacao->id); ?></td>
                                <td>
                                    <?php if($operacao->locacao_id): ?>
                                        <a href="<?php echo e(route('reserva.show', $operacao->locacao_id)); ?>" class="text-blue">
                                            #<?php echo e($operacao->locacao_id); ?>

                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($operacao->tipo); ?></td>
                                <td><?php echo e($operacao->data_fato_gerador->format('d/m/Y H:i')); ?></td>
                                <td><strong>R$ <?php echo e(number_format($operacao->valor_operacao, 2, ',', '.')); ?></strong></td>
                                <td>R$ <?php echo e(number_format($operacao->valor_base_calculo, 2, ',', '.')); ?></td>
                                <td>
                                    <?php if($operacao->status === 'AUTORIZADA'): ?>
                                        <span class="fc-badge fc-badge--pago">
                                            <i class="fas fa-check-circle"></i> <?php echo e($operacao->status); ?>

                                        </span>
                                    <?php elseif($operacao->status === 'CALCULADA'): ?>
                                        <span class="fc-badge fc-badge--receita">
                                            <i class="fas fa-calculator"></i> <?php echo e($operacao->status); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="fc-badge fc-badge--pendente">
                                            <i class="fas fa-clock"></i> <?php echo e($operacao->status); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    Nenhuma operação fiscal registrada.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($operacoes->hasPages()): ?>
                <div class="card-footer py-3">
                    <?php echo e($operacoes->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/fiscal/operacoes.blade.php ENDPATH**/ ?>