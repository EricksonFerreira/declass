<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Aluguel</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @include('pdf.contratos.partials.styles')
</head>
@php
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
@endphp
<body>
<div class="page">
    @include('pdf.contratos.partials.header', [
        'get' => $get,
    ])
    <div class="accent-stripe"></div>
    <div class="two-col">
        <div class="col-left">
            @include('pdf.contratos.partials.dados-cliente', [
                'get' => $get,
            ])
        </div>
        <div class="col-right">
            @include('pdf.contratos.partials.dados-locacao-veiculo', [
                'get' => $get,
            ])
        </div>
    </div>
    <hr class="divider">
    @include('pdf.contratos.partials.resumo-reserva', [
        'get' => $get,
    ])
    @include('pdf.contratos.partials.valores', [
        'get' => $get,
    ])
    @include('pdf.contratos.partials.caucao', [
        'caucao' => $caucao,
    ])
    @include('pdf.contratos.partials.danos-avarias', [
        'danos' => $danos,
        'formatarTipoDano' => $formatarTipoDano,
        'hideValor' => true,
    ])
    @include('pdf.contratos.partials.cobrancas-avaria', [
        'cobrancasAvaria' => $cobrancasAvaria,
    ])
    @include('pdf.contratos.partials.faturacao', [
        'get' => $get,
        'opcionais' => $opcionais,
    ])
    @include('pdf.contratos.partials.declaracao')
    @include('pdf.contratos.partials.assinaturas', [
        'get' => $get,
    ])
    @include('pdf.contratos.partials.footer', [
        'get' => $get,
    ])
    @include('pdf.contratos.partials.condicoes-gerais')
</div>
</body>
</html>
