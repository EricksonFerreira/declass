<?php
/**
 * Pricing page view for Clockify.
 * It displays active plans with pricing and feature list.
 * Uses the existing pricing CSS defined in welcome.blade.php.
 */
?>


<?php $__env->startSection('title', 'Planos'); ?>

<?php $__env->startSection('content'); ?>
    <section class="pricing" id="planos">
        <div class="container">
            <h1 class="text-center mb-5">Planos de assinatura</h1>

            <div class="row">
                <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100" style="border: 1px solid var(--border); border-radius: 12px;">
                            <div class="card-body d-flex flex-column">
                                <h3 class="card-title text-center"><?php echo e($plan['nome']); ?></h3>
                                <p class="text-muted text-center mb-3"><?php echo e($plan['descricao'] ?? ''); ?></p>
                                <div class="mb-3 text-center">
                                    <span class="h4 font-weight-bold">R\$ <?php echo e(number_format($plan['valor_mensal'], 2, ',', '.')); ?></span>
                                    <small class="text-muted">/ mês</small>
                                </div>
                                <?php if($plan['valor_anual'] < $plan['valor_mensal'] * 12): ?>
                                    <div class="text-center mb-2">
                                        <span class="text-success font-weight-bold">
                                            R\$ <?php echo e(number_format($plan['valor_anual'], 2, ',', '.')); ?> / ano
                                        </span>
                                        <small class="text-muted">(economia de <?php echo e($plan['desconto_anual_percentual']); ?>%)</small>
                                    </div>
                                <?php endif; ?>
                                <hr>
                                <ul class="list-unstyled flex-grow-1">
                                    <?php $__currentLoopData = $plan['features']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($feature['incluso']): ?>
                                            <li class="mb-2 d-flex align-items-center">
                                                <i class="fas fa-check text-success mr-2"></i>
                                                <span><?php echo e($feature['label']); ?></span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                <a href="#" class="btn btn-primary mt-auto">Selecionar</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\clockify\pricing.blade.php ENDPATH**/ ?>