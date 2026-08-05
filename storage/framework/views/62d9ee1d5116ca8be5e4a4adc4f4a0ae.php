<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Termo de Devolução</title>
    <link rel="icon" href="<?php echo e(asset('favicon.ico')); ?>">
    <?php echo $__env->make('pdf.contratos.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</head>
<?php
    $get = fn ($key, $default = '-') => data_get($data, $key, $default);
    $filledValue = function ($key, $default = 'Não informado') use ($data) {
        $value = data_get($data, $key);
        return filled($value) ? $value : $default;
    };
    $caucao = $data['caucao'] ?? $data['calcao'] ?? null;
    $danos = $data['danos_detalhados'] ?? [];
    $pagamentos = $data['pagamentos'] ?? [];
    $opcionais = $data['opcionais'] ?? [];
    $cobrancasAvaria = $data['cobrancas_avaria'] ?? [];
    $estadoViatura = $data['estado_viatura']['itens'] ?? [];
    $formatarTipoDano = function ($tipo) {
        $map = [
            'amolgadela_grave' => 'Amassado grave',
            'amolgadela_leve' => 'Amassado leve',
            'risco_leve' => 'Risco leve',
            'risco_profundo' => 'Risco profundo',
            'pneu_danificado' => 'Pneu danificado',
            'vidro_trincado' => 'Vidro trincado',
            'para_choque_danificado' => 'Para-choque danificado',
        ];
        if (blank($tipo)) {
            return '-';
        }
        return $map[$tipo] ?? ucfirst(str_replace('_', ' ', $tipo));
    };
?>
<body>
<div class="page">
    <?php echo $__env->make('pdf.contratos.partials.header', [
        'get' => $get,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="accent-stripe"></div>
    <div class="section-title">Termo de Devolução / Check-out</div>
    <table class="info-table">
        <tr>
            <td>
                <div class="field-label">Contrato</div>
                <div class="field-value"><?php echo e($get('contrato.numero', '-')); ?></div>
            </td>
            <td>
                <div class="field-label">Status</div>
                <div class="field-value"><?php echo e($get('reserva.status', '-')); ?></div>
            </td>
            <td>
                <div class="field-label">Origem</div>
                <div class="field-value"><?php echo e($get('reserva.origem', '-')); ?></div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="field-label">Saída</div>
                <div class="field-value"><?php echo e($get('reserva.alugado_em.data', '-')); ?> <?php echo e($get('reserva.alugado_em.hora', '-')); ?></div>
            </td>
            <td>
                <div class="field-label">Devolução</div>
                <div class="field-value"><?php echo e($get('reserva.devolver_em.data', '-')); ?> <?php echo e($get('reserva.devolver_em.hora', '-')); ?></div>
            </td>
            <td>
                <div class="field-label">Emissão</div>
                <div class="field-value"><?php echo e($get('documento.data_emissao', '-')); ?></div>
            </td>
        </tr>
    </table>

    <div class="two-col">
        <div class="col-left">
            <?php echo $__env->make('pdf.contratos.partials.dados-cliente', [
                'get' => $get,
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
        <div class="col-right">
            <?php echo $__env->make('pdf.contratos.partials.dados-locacao-veiculo', [
                'get' => $get,
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>
    <hr class="divider">
    <?php echo $__env->make('pdf.contratos.partials.checkout-resumo', [
        'get' => $get,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('pdf.contratos.partials.checkout-estado-viatura', [
        'estadoViatura' => $estadoViatura,
        'formatarTipoDano' => $formatarTipoDano,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
   
    <?php echo $__env->make('pdf.contratos.partials.danos-avarias', [
        'danos' => $danos,
        'formatarTipoDano' => $formatarTipoDano,
        'hideValor' => true,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('pdf.contratos.partials.cobrancas-avaria', [
        'cobrancasAvaria' => $cobrancasAvaria,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
     
    <?php echo $__env->make('pdf.contratos.partials.declaracao', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php if(!empty($data['assinatura']) && !empty($data['documento']['data_assinatura'] ?? null)): ?>
        <?php echo $__env->make('pdf.contratos.partials.assinaturas', [
            'get' => $get,
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
 
    <?php echo $__env->make('pdf.contratos.partials.footer', [
        'get' => $get,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('pdf.contratos.partials.condicoes-gerais', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>
</body>
</html>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/pdf/contratos/checkout/comum.blade.php ENDPATH**/ ?>