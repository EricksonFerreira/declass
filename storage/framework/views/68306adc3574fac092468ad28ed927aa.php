<div class="rs-card mb-4">
    <div class="rs-card__header rs-card__header--between">
        <div class="rs-card__header-left">
            <i class="fas fa-car-crash rs-card__icon"></i>
            <h2>Sinistros</h2>
            <span class="rs-card__badge"><?php echo e($sinistros->count()); ?></span>
        </div>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sinistro.create')): ?>
            <a href="<?php echo e(route('sinistro.create', ['reserva_id' => $reserva->id, 'veiculo_id' => $reserva->veiculo_id])); ?>" class="rs-btn rs-btn--outline">
                <i class="fas fa-plus"></i> Novo Sinistro
            </a>
        <?php endif; ?>
    </div>
    <div class="rs-card__body">
        <?php if($sinistros->isEmpty()): ?>
            <div class="rs-empty">
                <i class="fas fa-check-circle" style="font-size:1.5rem;color:#10b981"></i>
                <span>Nenhum sinistro vinculado a esta reserva.</span>
            </div>
        <?php else: ?>
            <div class="rs-list">
                <?php $__currentLoopData = $sinistros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sinistro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rs-list__item">
                        <div class="rs-list__label">Sinistro #<?php echo e($sinistro->id); ?></div>
                        <div class="rs-list__value"><?php echo e($sinistro->descricao ?? 'Sem descricao'); ?></div>
                        <div class="mt-1">
                            <a href="<?php echo e(route('sinistro.show', $sinistro->id)); ?>" class="btn btn-xs btn-outline-primary">
                                <i class="fas fa-eye"></i> Detalhes
                            </a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/partials/common_sinistros.blade.php ENDPATH**/ ?>