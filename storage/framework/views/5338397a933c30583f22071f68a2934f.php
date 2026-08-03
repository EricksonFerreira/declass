<div class="rs-card mb-4">
    <div class="rs-card__header rs-card__header--between">
        <div class="rs-card__header-left">
            <i class="fas fa-exclamation-triangle rs-card__icon text-danger"></i>
            <h2>Multas</h2>
            <span class="rs-card__badge"><?php echo e($totalMultas); ?></span>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('multa.create')): ?>
            <?php if($pagamentoEfetuado): ?>
                <a href="<?php echo e(route('multa.create', ['reserva_id' => $reserva->id])); ?>" class="rs-btn rs-btn--outline">
                    <i class="fas fa-plus"></i> Nova multa
                </a>
            <?php endif; ?>
            <?php endif; ?>
            <?php if($totalMultas > 0): ?>
            <a href="<?php echo e(route('multa.index', ['reserva_id' => $reserva->id])); ?>" class="rs-btn rs-btn--outline">
                <i class="fas fa-list"></i> Ver todas
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="rs-card__body">
        <?php if($reserva->multas->isEmpty()): ?>
            <div class="rs-empty">
                <i class="fas fa-check-circle" style="font-size:1.5rem;color:#10b981"></i>
                <span>Nenhuma multa registrada para esta reserva.</span>
            </div>
        <?php else: ?>
            <div class="rs-list">
                <?php $__currentLoopData = $reserva->multas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $multa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $multaData = $multa->data_infracao ? \Carbon\Carbon::parse($multa->data_infracao)->format('d/m/Y') : '-';
                        $multaValor = (float) ($multa->valor_total ?? $multa->valor_final ?? $multa->valor ?? 0);
                    ?>
                    <div class="rs-list__item">
                        <div class="rs-list__label">
                            Auto <?php echo e($multa->auto_infracao ?? ('#' . $multa->id)); ?> &middot; <?php echo e($multaData); ?>

                        </div>
                        <div class="rs-list__value"><?php echo e($multa->descricao ?? $multa->infracao ?? 'Infração de trânsito'); ?></div>
                        <div class="mt-2 text-danger font-weight-bold">R$ <?php echo e(number_format($multaValor, 2, ',', '.')); ?></div>
                        <div class="mt-1">
                            <a href="<?php echo e(route('multa.show', $multa->id)); ?>" class="btn btn-xs btn-outline-primary">
                                <i class="fas fa-eye"></i> Detalhes
                            </a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/partials/common_multas.blade.php ENDPATH**/ ?>