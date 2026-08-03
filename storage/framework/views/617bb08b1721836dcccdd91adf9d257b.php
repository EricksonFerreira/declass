<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contrato de aluguel</title>

    <?php echo $__env->make('pdf.contratos.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</head>

<?php
    $get = fn ($key, $default = '-') =>
        data_get($data, $key, $default);

    $caucao = $data['caucao'] ?? $data['calcao'] ?? null;
    $danos = $data['danos_detalhados'] ?? [];
    $pagamentos = $data['pagamentos'] ?? [];
    $opcionais = $data['opcionais'] ?? [];
    $cobrancasAvaria = $data['cobrancas_avaria'] ?? [];
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
        return $map[$tipo] ?? ucfirst(str_replace('_', ' ', (string) $tipo));
    };
?>

<body>
<div class="page">
    <?php echo $__env->make('pdf.contratos.partials.header', [
        'get' => $get,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="accent-stripe"></div>

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

    <?php echo $__env->make('pdf.contratos.partials.resumo-reserva', [
        'get' => $get,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('pdf.contratos.partials.caucao', [
        'caucao' => $caucao,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php echo $__env->make('pdf.contratos.partials.danos-avarias', [
        'danos' => $danos,
        'formatarTipoDano' => $formatarTipoDano,
        'hideValor' => true,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php echo $__env->make('pdf.contratos.partials.valores', [
        'get' => $get,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php echo $__env->make('pdf.contratos.partials.faturacao', [
        'get' => $get,
        'opcionais' => $opcionais,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php echo $__env->make('pdf.contratos.partials.assinaturas', [
        'get' => $get,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php echo $__env->make('pdf.contratos.partials.footer', [
        'get' => $get,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php echo $__env->make('pdf.contratos.partials.condicoes-gerais', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>
</body>
</html><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/pdf/contratos/checkin/comum.blade.php ENDPATH**/ ?>