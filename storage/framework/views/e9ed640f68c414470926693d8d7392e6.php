<?php $__env->startSection('title', 'Créditos Tributários'); ?>

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
<?php $component = App\View\Components\PageTitle::resolve(['title' => 'Créditos Tributários'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
                    <h5 class="font-weight-bold text-dark mb-1">Como funciona a não cumulatividade e os créditos fiscais?</h5>
                    <p class="text-secondary mb-0 text-sm">
                        Sob o regime da Lei Complementar 214/2025, os impostos pagos na aquisição de insumos (como veículos para a frota, peças de reposição e contratação de serviços de manutenção) geram <strong>créditos tributários de IBS e CBS</strong>.
                        Esses créditos documentados podem ser integralmente compensados com os débitos gerados no faturamento das locações. 
                        Este painel controla o saldo acumulado e a apropriação dos créditos fiscais com base nos documentos fiscais de entrada.
                    </p>
                </div>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success shadow-sm mb-3" style="border-radius: 8px;"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="alert alert-danger shadow-sm mb-3" style="border-radius: 8px;"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                    <h5 class="fc-title mb-0">
                        <i class="fas fa-piggy-bank"></i>
                        <div>
                            <span>Listagem de Créditos Apropriados</span>
                            <span class="fc-subtitle">Créditos de não cumulatividade gerados por aquisição de frota, insumos e serviços</span>
                        </div>
                    </h5>
                    <div class="d-flex" style="gap: 8px;">
                        <button type="button" class="btn btn-outline-primary fc-btn" data-toggle="modal" data-target="#importXmlModal">
                            <i class="fas fa-file-upload mr-1"></i> Importar XML de NF-e
                        </button>
                        <a href="<?php echo e(route('fiscal.creditos.criar')); ?>" class="btn btn-primary fc-btn">
                            <i class="fas fa-plus mr-1"></i> Apropriar Crédito
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <table class="table fc-table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tributo</th>
                            <th>Valor Original</th>
                            <th>Valor Utilizado</th>
                            <th>Saldo</th>
                            <th>Origem</th>
                            <th>Data Apropriação</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $creditos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $credito): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($credito->id); ?></td>
                                <td><span class="badge badge-success"><?php echo e($credito->tributo); ?></span></td>
                                <td><strong>R$ <?php echo e(number_format($credito->valor_original, 2, ',', '.')); ?></strong></td>
                                <td>R$ <?php echo e(number_format($credito->valor_utilizado, 2, ',', '.')); ?></td>
                                <td><strong class="text-blue">R$ <?php echo e(number_format($credito->saldo, 2, ',', '.')); ?></strong></td>
                                <td><?php echo e($credito->origem); ?></td>
                                <td><?php echo e($credito->data_apropriacao->format('d/m/Y H:i')); ?></td>
                                <td>
                                    <?php if($credito->status === 'DISPONIVEL'): ?>
                                        <span class="fc-badge fc-badge--pago">
                                            <i class="fas fa-check-circle"></i> <?php echo e($credito->status); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="fc-badge fc-badge--cancelado">
                                            <?php echo e($credito->status); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Nenhum crédito tributário apropriado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($creditos->hasPages()): ?>
                <div class="card-footer py-3">
                    <?php echo e($creditos->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- XML Upload Modal -->
    <div class="modal fade" id="importXmlModal" tabindex="-1" role="dialog" aria-labelledby="importXmlModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold text-dark" id="importXmlModalLabel">
                        <i class="fas fa-file-invoice mr-2 text-primary"></i>
                        Importar XML de NF-e
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?php echo e(route('fiscal.creditos.importar-xml')); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body p-4">
                        <p class="text-secondary text-sm mb-3">
                            Faça o upload do arquivo XML (.xml) da nota fiscal eletrônica de compra (NF-e) do veículo ou da O.S. de manutenção. 
                            O sistema lerá os impostos destacados de IBS/CBS e gerará o saldo de créditos automaticamente.
                        </p>
                        <div class="form-group mb-0">
                            <label class="fc-label">Selecione o arquivo XML *</label>
                            <input type="file" name="xml_file" class="form-control-file" accept=".xml" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-outline-secondary fc-btn" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary fc-btn">
                            <i class="fas fa-file-import mr-1"></i> Importar Nota
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/fiscal/creditos.blade.php ENDPATH**/ ?>