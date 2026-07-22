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
    $fotosCheckout = data_get($data, 'checkout.fotos', []);
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
    <h1>Termo de Devolucao</h1>
    <div class="declaration">
        O check-out registra as condicoes apuradas no encerramento da locação, incluindo quilometragem final,
        nivel de combustivel, data e horario da devolucao, novas avarias, fotos do veiculo, multas, diarias ou
        valores pendentes, alem da entrega de chave e acessorios quando aplicavel.
        <br><br>
        A assinatura do cliente confirma que acompanhou a vistoria e tomou ciencia das condicoes registradas,
        fortalecendo a comprovacao em caso de contestacao posterior sobre avarias ou cobrancas.
    </div>
    <div class="section-title">Dados da Devolucao</div>
    <table class="info-table">
        <tr>
            <td>
                <div class="field-label">Quilometragem final</div>
                <div class="field-value">{{ $get('checkout.quilometragem_final', '-') }} km</div>
            </td>
            <td>
                <div class="field-label">Combustivel final</div>
                <div class="field-value">{{ $get('checkout.combustivel_final', '-') }}</div>
            </td>
            <td>
                <div class="field-label">Data/Hora da devolucao</div>
                <div class="field-value">{{ $get('checkout.data_hora_devolucao', '-') }}</div>
            </td>
        </tr>
    </table>
    @if($get('checkout.notas_devolucao', null) || $get('checkout.observacoes_devolucao', null))
        <table class="info-table" style="margin-top:6px">
            <tr>
                <td>
                    <div class="field-label">Notas de devolucao</div>
                    <div class="field-value">{{ $get('checkout.notas_devolucao', '-') }}</div>
                </td>
                <td>
                    <div class="field-label">Observacoes</div>
                    <div class="field-value">{{ $get('checkout.observacoes_devolucao', '-') }}</div>
                </td>
            </tr>
        </table>
    @endif
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
    @include('pdf.contratos.partials.pagamentos-checkout', [
        'pagamentos' => $pagamentos,
    ])
    @include('pdf.contratos.partials.caucao', [
        'caucao' => $caucao,
    ])
    @include('pdf.contratos.partials.danos-avarias', [
        'danos' => $danos,
        'formatarTipoDano' => $formatarTipoDano,
    ])
    @include('pdf.contratos.partials.cobrancas-avaria', [
        'cobrancasAvaria' => $cobrancasAvaria,
    ])
    <div class="section-title">Fotos do Veiculo na Devolucao</div>
    @if(!empty($fotosCheckout))
        <table class="detail-table">
            <tr>
                @foreach($fotosCheckout as $foto)
                    <td style="text-align:center;width:16.66%;">
                        <img class="thumb" src="{{ $foto['url'] }}" alt="{{ $foto['label'] }}" style="width:90px;height:70px;object-fit:cover;">
                        <div class="muted" style="margin-top:3px">{{ $foto['label'] }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    @else
        <div class="muted" style="font-style:italic;margin-top:4px">
            Nenhuma foto de devolucao registrada.
        </div>
    @endif
    @include('pdf.contratos.partials.faturacao', [
        'get' => $get,
        'opcionais' => $opcionais,
    ])
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
