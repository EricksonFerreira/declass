<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h1 class="mb-4">Exportar Relatório</h1>
    <form method="POST" action="<?php echo e(route('relatorios.exportar')); ?>">
        <?php echo csrf_field(); ?>
        <div class="row g-3">
            <div class="col-md-4">
                <label for="data_inicio" class="form-label">Data início</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" required>
            </div>
            <div class="col-md-4">
                <label for="data_fim" class="form-label">Data fim</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" required>
            </div>
            <div class="col-md-4">
                <label for="report_type" class="form-label">Tipo de relatório</label>
                <select class="form-select" id="report_type" name="report_type" required>
                    <option value="horas_trabalhadas_por_projeto">Horas Trabalhadas por Projeto</option>
                </select>
            </div>
        </div>
        <div class="row g-3 mt-3">
            <div class="col-md-12">
                <label for="user_ids" class="form-label">Equipe (múltipla)</label>
                <select class="form-select select2" id="user_ids" name="user_ids[]" multiple="multiple" style="width: 100%;">
                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?> (<?php echo e($user->email); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <small class="text-muted">Deixe vazio para incluir todos os usuários.</small>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-success">Exportar CSV</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Selecione usuários',
            allowClear: true
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/relatorios/export.blade.php ENDPATH**/ ?>