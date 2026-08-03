<?php
    $fin = $data['financeiro_detalhado'] ?? [];
?>
<div class="section-title">Valores Detalhados</div>
<table class="detail-table">
    <tbody>
        <tr>
            <td>Grupo</td>
            <td class="money"><?php echo e($fin['grupo'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td>Tarifa diária</td>
            <td class="money"><?php echo e($fin['tarifa_diaria'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td><?php echo e($fin['dias_label'] ?? 'Dias x tarifa'); ?></td>
            <td class="money"><?php echo e($fin['valor_tarifa_base'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td>Opcionais</td>
            <td class="money"><?php echo e($fin['opcionais'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td>Retirada fora do expediente</td>
            <td class="money"><?php echo e($fin['taxa_coleta'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td>Devolução fora do expediente</td>
            <td class="money"><?php echo e($fin['taxa_entrega'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td>Subtotal</td>
            <td class="money"><?php echo e($fin['subtotal'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td>Desconto <?php echo e($fin['desconto_label'] ?? ''); ?></td>
            <td class="money">- <?php echo e($fin['desconto_valor'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td class="money">
                <strong><?php echo e($fin['total'] ?? $get('faturacao.valor_total', '-')); ?></strong>
            </td>
        </tr>
    </tbody>
</table>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/pdf/contratos/partials/valores.blade.php ENDPATH**/ ?>