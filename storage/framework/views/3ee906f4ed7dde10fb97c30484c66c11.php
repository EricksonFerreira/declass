<div class="section-title">Resumo Detalhado da Reserva</div>
<table class="detail-table">
    <tbody>
        <tr>
            <td><strong>Status</strong><br><?php echo e($get('reserva.status', '-')); ?></td>
            <td><strong>Origem</strong><br><?php echo e($get('reserva.origem', '-')); ?></td>
            <td><strong>Pagamento efetuado</strong><br><?php echo e($get('reserva.pagamento_efetuado', '-')); ?></td>
            <td><strong>Criada em</strong><br><?php echo e($get('reserva.criada_em', '-')); ?></td>
        </tr>
        <tr>
            <td><strong>Grupo reservado</strong><br><?php echo e($get('reserva.grupo_reservado', '-')); ?></td>
            <td><strong>Modelo reservado</strong><br><?php echo e($get('reserva.modelo_reservado', '-')); ?></td>
            <td colspan="2"><strong>Observações</strong><br><?php echo e($get('reserva.observacoes', '-')); ?></td>
        </tr>
    </tbody>
</table>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/pdf/contratos/partials/resumo-reserva.blade.php ENDPATH**/ ?>