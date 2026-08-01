<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Termo de Devolução</title>
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
@endphp
<body>
<div class="page">
    @include('pdf.contratos.partials.header', [
        'get' => $get,
    ])
    <div class="accent-stripe"></div>
    <div class="section-title">Termo de Devolução / Check-out</div>
    <table class="info-table">
        <tr>
            <td>
                <div class="field-label">Contrato</div>
                <div class="field-value">{{ $get('contrato.numero', '-') }}</div>
            </td>
            <td>
                <div class="field-label">Status</div>
                <div class="field-value">{{ $get('reserva.status', '-') }}</div>
            </td>
            <td>
                <div class="field-label">Origem</div>
                <div class="field-value">{{ $get('reserva.origem', '-') }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="field-label">Saída</div>
                <div class="field-value">{{ $get('reserva.alugado_em.data', '-') }} {{ $get('reserva.alugado_em.hora', '-') }}</div>
            </td>
            <td>
                <div class="field-label">Devolução</div>
                <div class="field-value">{{ $get('reserva.devolver_em.data', '-') }} {{ $get('reserva.devolver_em.hora', '-') }}</div>
            </td>
            <td>
                <div class="field-label">Emissão</div>
                <div class="field-value">{{ $get('documento.data_emissao', '-') }}</div>
            </td>
        </tr>
    </table>

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
    @include('pdf.contratos.partials.checkout-resumo', [
        'get' => $get,
    ])
    @include('pdf.contratos.partials.checkout-estado-viatura', [
        'estadoViatura' => $estadoViatura,
        'formatarTipoDano' => $formatarTipoDano,
    ])
   
    @include('pdf.contratos.partials.danos-avarias', [
        'danos' => $danos,
        'formatarTipoDano' => $formatarTipoDano,
        'hideValor' => true,
    ])
    @include('pdf.contratos.partials.cobrancas-avaria', [
        'cobrancasAvaria' => $cobrancasAvaria,
    ])
     
    @include('pdf.contratos.partials.declaracao')
    @if(!empty($data['assinatura']) && !empty($data['documento']['data_assinatura'] ?? null))
        @include('pdf.contratos.partials.assinaturas', [
            'get' => $get,
        ])
    @endif
 
    @include('pdf.contratos.partials.footer', [
        'get' => $get,
    ])
    @include('pdf.contratos.partials.condicoes-gerais')
</div>
</body>
</html>
