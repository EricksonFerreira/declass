<?php $__env->startSection('title', 'Classificações Fiscais (NCM)'); ?>

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
<?php $component = App\View\Components\PageTitle::resolve(['title' => 'Classificações Fiscais (NCM)'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
                    <h5 class="font-weight-bold text-dark mb-1">Classificações Fiscais (NCM do Bem Móvel)</h5>
                    <p class="text-secondary mb-0 text-sm">
                        De acordo com as especificações da NFS-e Nacional para o grupo <strong>gLocBensMoveis</strong>, cada veículo cadastrado no sistema deve possuir um código de <strong>NCM (Nomenclatura Comum do Mercosul)</strong> associado. 
                        Esta tabela centraliza as classificações fiscais que serão compartilhadas por sua frota de veículos (ex: automóveis de passageiros, vans, furgões) para emissão correta da declaração fiscal.
                    </p>
                </div>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success shadow-sm mb-3" style="border-radius: 8px;"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head d-flex justify-content-between align-items-center">
                    <h5 class="fc-title mb-0">
                        <i class="fas fa-tags"></i>
                        <div>
                            <span>Listagem de NCMs Cadastrados</span>
                            <span class="fc-subtitle">Classificações fiscais associadas à frota de veículos</span>
                        </div>
                    </h5>
                    <a href="<?php echo e(route('fiscal.classificacoes.criar')); ?>" class="btn btn-primary fc-btn">
                        <i class="fas fa-plus mr-1"></i> Nova Classificação
                    </a>
                </div>
            </div>
            
            <div class="card-body p-0">
                <table class="table fc-table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descrição</th>
                            <th>NCM</th>
                            <th>Tipo do Bem</th>
                            <th>Qtd. Veículos Associados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $classificacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classificacao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($classificacao->id); ?></td>
                                <td><strong><?php echo e($classificacao->descricao); ?></strong></td>
                                <td><code><?php echo e($classificacao->ncm); ?></code></td>
                                <td><?php echo e($classificacao->tipo_bem); ?></td>
                                <td><?php echo e($classificacao->veiculos()->count()); ?> veiculo(s)</td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Nenhuma classificação fiscal configurada.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($classificacoes->hasPages()): ?>
                <div class="card-footer py-3">
                    <?php echo e($classificacoes->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/fiscal/classificacoes.blade.php ENDPATH**/ ?>