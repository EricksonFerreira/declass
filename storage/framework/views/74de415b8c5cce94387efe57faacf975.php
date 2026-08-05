<div class="section-title">Resumo da Devolução</div>
<table class="info-table">
    <tr>
        <td>
            <div class="field-label">Veículo devolvido</div>
            <div class="field-value"><?php echo e($get('veiculo.nome', '-')); ?> - <?php echo e($get('veiculo.matricula', '-')); ?></div>
        </td>
        <td>
            <div class="field-label">Quilometragem na devolução</div>
            <div class="field-value"><?php echo e($get('veiculo.quilometros', '-')); ?></div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="field-label">Combustível na devolução</div>
            <div class="field-value"><?php echo e($get('veiculo.combustivel', '-')); ?></div>
        </td>
        <td>
            <div class="field-label">Funcionário responsável</div>
            <div class="field-value"><?php echo e($get('funcionario.nome', '-')); ?></div>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <div class="field-label">Observações do check-out</div>
            <div class="field-value"><?php echo e($get('reserva.observacoes', 'Sem observações registradas.')); ?></div>
        </td>
    </tr>
</table>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/pdf/contratos/partials/checkout-resumo.blade.php ENDPATH**/ ?>