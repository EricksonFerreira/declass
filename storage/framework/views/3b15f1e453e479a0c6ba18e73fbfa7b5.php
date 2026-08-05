<?php if(!empty($caucao) && is_array($caucao)): ?>
    <div class="section-title">Caução</div>
    <table class="detail-table">
        <tbody>
            <tr>
                <td><strong>Valor</strong><br><?php echo e($caucao['valor_calculado'] ?? $caucao['valor_autorizado'] ?? $caucao['valor_calculado'] ?? '-'); ?></td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/pdf/contratos/partials/caucao.blade.php ENDPATH**/ ?>