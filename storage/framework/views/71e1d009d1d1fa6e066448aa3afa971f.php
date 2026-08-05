<?php if(!empty($cobrancasAvaria)): ?>
    <div class="section-title">Cobranças de Avarias</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Status</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $cobrancasAvaria; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cobranca): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($cobranca['data'] ?? '-'); ?></td>
                    <td><?php echo e($cobranca['descricao'] ?? '-'); ?></td>
                    <td><?php echo e($cobranca['status'] ?? '-'); ?></td>
                    <td class="money"><?php echo e($cobranca['valor'] ?? '-'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/pdf/contratos/partials/cobrancas-avaria.blade.php ENDPATH**/ ?>