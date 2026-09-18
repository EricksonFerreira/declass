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
        <a href="<?php echo e(route('assinatura.index')); ?>" class="btn btn-xs btn-outline-secondary">
            <i class="fas fa-id-card mr-1"></i> Assinaturas
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
        <div class="card card-outline card-secondary mb-0">
            <div class="card-header py-2">
                <ul class="nav nav-pills card-header-pills" role="tablist" style="gap:.35rem;">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab-regras" role="tab">
                            <i class="fas fa-book mr-1"></i> Regras do Sistema
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-logica" role="tab">
                            <i class="fas fa-sitemap mr-1"></i> Logica de Planos
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-regras" role="tabpanel">
                        <div class="markdown-body"><?php echo $regrasHtml; ?></div>
                    </div>
                    <div class="tab-pane fade" id="tab-logica" role="tabpanel">
                        <div class="markdown-body"><?php echo $logicaHtml; ?></div>
                    </div>
                </div>
            </div>
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

<?php $__env->startPush('css'); ?>
    <style>
        .markdown-body h1 { font-size: 1.4rem; margin-top: 0; }
        .markdown-body h2 { font-size: 1.15rem; margin-top: 1.25rem; }
        .markdown-body h3 { font-size: 1.0rem; margin-top: 1rem; }
        .markdown-body pre { background: #0b1220; color: #e2e8f0; padding: .75rem; border-radius: .5rem; }
        .markdown-body code { background: rgba(15, 23, 42, .06); padding: .1rem .25rem; border-radius: .25rem; }
        .markdown-body table { width: 100%; }
        .markdown-body table th, .markdown-body table td { border: 1px solid #e5e7eb; padding: .4rem .5rem; }
        .markdown-body blockquote { border-left: 3px solid #cbd5e1; padding-left: .75rem; color: #475569; }
        .markdown-body hr { border-top: 1px solid #e5e7eb; }
    </style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\assinatura\regras.blade.php ENDPATH**/ ?>