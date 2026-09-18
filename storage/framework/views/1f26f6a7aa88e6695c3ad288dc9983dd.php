<?php $__env->startSection('title', 'Exportar Relatório'); ?>

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
<?php $component = App\View\Components\PageTitle::resolve(['title' => 'Exportar Relatório'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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

<?php $__env->startSection('css'); ?>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    .exp-card { font-family: 'Plus Jakarta Sans', sans-serif; }

    .exp-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #64748b; }
    .exp-hint  { font-size: 12px; color: #94a3b8; }

    .select2-container--bootstrap4 .select2-selection--multiple { min-height: 38px; border-color: #ced4da; }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice { margin-top: 6px; font-size: 12px; }
    .select2-container { max-width: 100%; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card exp-card">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-file-export mr-1"></i> Parâmetros</h3>
                <small class="exp-hint d-block mt-1">Gere um CSV com as horas registradas pela equipe no período.</small>
            </div>
            <form method="POST" action="<?php echo e(route('relatorios.exportar')); ?>">
                <?php echo csrf_field(); ?>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data_inicio" class="exp-label">Data início</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio"
                                       value="<?php echo e(old('data_inicio')); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data_fim" class="exp-label">Data fim</label>
                                <input type="date" class="form-control" id="data_fim" name="data_fim"
                                       value="<?php echo e(old('data_fim')); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="report_type" class="exp-label">Tipo de relatório</label>
                        <select class="form-control" id="report_type" name="report_type" required>
                            <option value="horas_trabalhadas_por_projeto">Horas Trabalhadas por Projeto</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label for="user_ids" class="exp-label">Equipe</label>
                        <select class="form-control select2" id="user_ids" name="user_ids[]" multiple="multiple" style="width: 100%;">
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($user->id); ?>" <?php if(in_array($user->id, (array) old('user_ids', []))): echo 'selected'; endif; ?>><?php echo e($user->name); ?> (<?php echo e($user->email); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <small class="exp-hint">Deixe vazio para incluir todos os usuários.</small>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end" style="gap: 8px; background:#f8fafc; border-top:1px solid #e5e7eb;">
                    <a href="<?php echo e(route('relatorios.index')); ?>" class="btn btn-default font-weight-bold shadow-sm">
                        Voltar
                    </a>
                    <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
                        <i class="fas fa-file-csv mr-1"></i> Exportar CSV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(function () {
        if ($.fn.select2) {
            $('#user_ids').select2({
                theme: 'bootstrap4',
                placeholder: 'Todos os usuários',
                allowClear: true,
                language: { noResults: function () { return 'Nenhum usuário encontrado'; } }
            });
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/relatorios/export.blade.php ENDPATH**/ ?>