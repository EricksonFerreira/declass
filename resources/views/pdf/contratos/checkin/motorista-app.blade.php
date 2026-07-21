<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contrato Motorista de Aplicativo</title>

    @include('pdf.contratos.partials.styles')
</head>

@php
    $get = fn ($key, $default = '-') => data_get($data, $key, $default);

    $caucao = $data['caucao'] ?? null;
    $pagamentos = $data['pagamentos'] ?? [];
    $opcionais = $data['opcionais'] ?? [];
@endphp

<body>
<div class="page">
    @include('pdf.contratos.partials.header', [
        'get' => $get,
    ])

    <div class="accent-stripe"></div>

    <h1>Contrato de Locação para Motorista de Aplicativo</h1>

    <div class="two-col">
        <div class="col-left">
            @include('pdf.contratos.partials.dados-cliente', [
                'get' => $get,
            ])
        </div>

        <div class="col-right">
            @include('pdf.contratos.partials.motorista-app.dados-locacao-veiculo', [
                'get' => $get,
            ])
        </div>
    </div>

    @include('pdf.contratos.partials.motorista-app.resumo-reserva', [
        'get' => $get,
    ])

    @include('pdf.contratos.partials.motorista-app.valores', [
        'get' => $get,
        'opcionais' => $opcionais,
    ])

    @include('pdf.contratos.partials.motorista-app.pagamentos', [
        'pagamentos' => $pagamentos,
    ])

    @include('pdf.contratos.partials.motorista-app.caucao', [
        'get' => $get,
        'caucao' => $caucao,
    ])

    @include('pdf.contratos.partials.assinaturas', [
        'get' => $get,
    ])

    @include('pdf.contratos.partials.footer', [
        'get' => $get,
    ])
</div>
</body>
</html>