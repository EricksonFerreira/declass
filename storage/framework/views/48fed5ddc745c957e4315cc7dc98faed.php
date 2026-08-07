<?php $__env->startSection('title', 'Gestão Tributária'); ?>

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
<?php $component = App\View\Components\PageTitle::resolve(['title' => 'Gestão Tributária (Reforma Tributária - LC 214/2025)'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
                    <h5 class="font-weight-bold text-dark mb-1">Como funciona a Reforma Tributária na Locação de Veículos?</h5>
                    <p class="text-secondary mb-0 text-sm">
                        De acordo com a <strong>Lei Complementar 214/2025</strong>, a locação de bens móveis (incluindo veículos) passa a ser fato gerador do <strong>IBS (Imposto sobre Bens e Serviços)</strong> e da <strong>CBS (Contribuição sobre Bens e Serviços)</strong>. 
                        Este painel consolida seus débitos gerados nas locações finalizadas e os créditos fiscais gerados nas compras de frota ou manutenção, garantindo a neutralidade tributária (não cumulatividade) até a apuração final.
                    </p>
                </div>
            </div>
        </div>

        <!-- KPIs Row -->
        <div class="row mb-4">
            <!-- Débitos IBS -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="fc-kpi fc-kpi--despesa">
                    <span>Débitos IBS</span>
                    <strong>R$ <?php echo e(number_format($debitosIbs, 2, ',', '.')); ?></strong>
                    <small>Imposto estadual/municipal recolhido nas locações</small>
                </div>
            </div>

            <!-- Débitos CBS -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="fc-kpi fc-kpi--despesa" style="border-left: 4px solid var(--fc-amber);">
                    <span>Débitos CBS</span>
                    <strong>R$ <?php echo e(number_format($debitosCbs, 2, ',', '.')); ?></strong>
                    <small>Contribuição federal recolhida nas locações</small>
                </div>
            </div>

            <!-- Créditos Disponíveis -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="fc-kpi fc-kpi--receita">
                    <span>Créditos Disponíveis</span>
                    <strong>R$ <?php echo e(number_format($creditosDisponiveis, 2, ',', '.')); ?></strong>
                    <small>Créditos acumulados na compra de veículos/peças</small>
                </div>
            </div>

            <!-- Saldo Estimado -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="fc-kpi fc-kpi--saldo">
                    <span>Saldo Estimado a Pagar</span>
                    <strong>R$ <?php echo e(number_format($saldoEstimado, 2, ',', '.')); ?></strong>
                    <small>Saldo a recolher na apuração periódica</small>
                </div>
            </div>
        </div>

        <!-- Links Cards -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card fc-card">
                    <div class="card-header py-3">
                        <div class="fc-head">
                            <h5 class="fc-title">
                                <i class="fas fa-receipt"></i>
                                <span>Operações Fiscais</span>
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted text-sm">Monitore os fatos geradores tributados e acompanhe o status da emissão da DPS/NFS-e Nacional.</p>
                        <a href="<?php echo e(route('fiscal.operacoes')); ?>" class="btn btn-primary fc-btn btn-block">
                            <i class="fas fa-eye"></i> Acompanhar Fatos Geradores
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card fc-card">
                    <div class="card-header py-3">
                        <div class="fc-head">
                            <h5 class="fc-title">
                                <i class="fas fa-cogs"></i>
                                <span>Regras Tributárias</span>
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted text-sm">Parametrizador dinâmico de alíquotas por período, garantindo que o sistema funcione na transição (2026-2033).</p>
                        <a href="<?php echo e(route('fiscal.regras')); ?>" class="btn btn-primary fc-btn btn-block">
                            <i class="fas fa-sliders-h"></i> Configurar Vigências e Alíquotas
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card fc-card">
                    <div class="card-header py-3">
                        <div class="fc-head">
                            <h5 class="fc-title">
                                <i class="fas fa-piggy-bank"></i>
                                <span>Créditos Tributários</span>
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted text-sm">Registre créditos de IBS/CBS documentados na compra de frota e custos operacionais.</p>
                        <a href="<?php echo e(route('fiscal.creditos')); ?>" class="btn btn-primary fc-btn btn-block">
                            <i class="fas fa-wallet"></i> Gerenciar Saldo de Créditos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/fiscal/dashboard.blade.php ENDPATH**/ ?>