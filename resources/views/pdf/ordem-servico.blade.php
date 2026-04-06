<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatorio de Ordem de Servico</title>
    <style>
        {!! $data['css'] ?? '' !!}

        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page { size: A4 portrait; margin: 12mm 14mm; }

        body {
            font-family: Arial, sans-serif;
            font-size: 9.5px;
            color: #1a2035;
            background: #fff;
        }

        .page {
            width: 100%;
            max-width: 190mm;
            min-height: 250mm;
            margin: 0 auto;
            padding: 0;
        }

        .doc-header,
        .section-title,
        .info-grid,
        .box,
        .items-table {
            page-break-inside: avoid;
        }

        .doc-header {
            display: table;
            width: 100%;
            border-bottom: 3px solid #1e3a8a;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .doc-header__logo { display: table-cell; width: 38%; vertical-align: middle; }
        .doc-header__logo img { max-height: 64px; max-width: 200px; }
        .doc-header__logo .sistema-logo { max-height: 48px; }
        .doc-header__info { display: table-cell; width: 62%; vertical-align: middle; text-align: right; }

        .doc-header__title {
            font-size: 14px;
            font-weight: 900;
            color: #1e3a8a;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .doc-header__subtitle {
            font-size: 8px;
            color: #475569;
            margin-top: 3px;
            letter-spacing: 0.04em;
        }

        .doc-header__num {
            display: inline-block;
            margin-top: 5px;
            background: #1e3a8a;
            color: #fff;
            font-weight: 800;
            font-size: 10px;
            padding: 3px 12px;
            border-radius: 20px;
            letter-spacing: 0.06em;
        }

        .accent-stripe {
            height: 4px;
            background: linear-gradient(90deg, #1e3a8a 0%, #3b82f6 50%, #60a5fa 100%);
            border-radius: 2px;
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 8px;
            font-weight: 900;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #1e3a8a;
            border-left: 3px solid #1e3a8a;
            padding-left: 6px;
            margin-bottom: 6px;
            margin-top: 10px;
        }

        .info-grid { width: 100%; border-collapse: collapse; }
        .info-grid td { padding: 3px 5px; vertical-align: top; }

        .field-label {
            font-size: 7.5px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .field-value {
            font-size: 9px;
            color: #0f172a;
            font-weight: 500;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 1px;
            min-width: 60px;
        }

        .box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
            margin-top: 6px;
        }

        .box p {
            font-size: 9px;
            color: #334155;
            line-height: 1.5;
            white-space: pre-wrap;
        }

        .items-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .items-table th {
            background: #1e3a8a;
            color: #fff;
            font-size: 7.5px;
            font-weight: 700;
            padding: 4px 5px;
            text-align: left;
        }
        .items-table td {
            padding: 3px 5px;
            font-size: 8.5px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .items-table tr:nth-child(even) td { background: #f8fafc; }

        .muted { color: #64748b; font-style: italic; }

        .doc-footer {
            margin-top: 14px;
            text-align: center;
            font-size: 7px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }
    </style>
</head>
<body>
<div class="page">

    <div class="doc-header">
        <div class="doc-header__logo">
            @if(!empty($data['documento']['empresa']['logo_url']))
                <img src="{{ $data['documento']['empresa']['logo_url'] }}" alt="Logo">
            @else
                <img class="sistema-logo" src="{!! asset('assets/imgs/logo.png') !!}" alt="XpandCars">
            @endif
        </div>
        <div class="doc-header__info">
            <div class="doc-header__title">Relatorio de Ordem de Servico</div>
            <div class="doc-header__subtitle">{{ $data['documento']['empresa']['nome'] ?? 'Empresa' }}</div>
            <div class="doc-header__num">{{ $data['os']['numero'] ?? '' }}</div>
        </div>
    </div>

    <div class="accent-stripe"></div>

    <div class="section-title">Resumo</div>
    <table class="info-grid">
        <tr>
            <td style="width:25%">
                <div class="field-label">Status</div>
                <div class="field-value">{{ strtoupper((string) ($data['os']['status'] ?? '')) }}</div>
            </td>
            <td style="width:25%">
                <div class="field-label">Aberta em</div>
                <div class="field-value">{{ $data['os']['aberta_em'] ?? '-' }}</div>
            </td>
            <td style="width:25%">
                <div class="field-label">Concluida em</div>
                <div class="field-value">{{ $data['os']['concluida_em'] ?? '-' }}</div>
            </td>
            <td style="width:25%">
                <div class="field-label">Reserva</div>
                <div class="field-value">{{ !empty($data['reserva']['id']) ? ('#' . $data['reserva']['id']) : '-' }}</div>
            </td>
        </tr>
        <tr>
            <td style="width:25%">
                <div class="field-label">Aberta por</div>
                <div class="field-value">{{ $data['os']['aberta_por'] ?? '-' }}</div>
            </td>
            <td style="width:25%">
                <div class="field-label">Concluida por</div>
                <div class="field-value">{{ $data['os']['concluida_por'] ?? '-' }}</div>
            </td>
            <td colspan="2">
                <div class="field-label">Veiculo</div>
                <div class="field-value">
                    {{ $data['veiculo']['nome'] ?? '-' }}
                    @if(!empty($data['veiculo']['placa']))
                        &bull; Placa: {{ $data['veiculo']['placa'] }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    @if(!empty($data['os']['observacoes']))
        <div class="section-title">Observacoes</div>
        <div class="box">
            <p>{{ $data['os']['observacoes'] }}</p>
        </div>
    @endif

    @if(!empty($data['os']['relatorio_conclusao']))
        <div class="section-title">Relatorio geral</div>
        <div class="box">
            <p>{{ $data['os']['relatorio_conclusao'] }}</p>
        </div>
    @endif

    <div class="section-title">Itens do Servico</div>
    @if(!empty($data['itens']) && is_array($data['itens']))
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:34%">Descricao</th>
                    <th style="width:26%">Resultado</th>
                    <th style="width:14%">Avaria</th>
                    <th style="width:26%">Obs avaria / Correcao</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['itens'] as $item)
                    <tr>
                        <td>{{ $item['descricao'] ?: '-' }}</td>
                        <td>{{ $item['resultado'] ?: '-' }}</td>
                        <td>
                            @if(!empty($item['dano']))
                                #{{ $item['dano']['id'] }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if(!empty($item['dano']))
                                <div><strong>{{ strtoupper((string) ($item['dano']['local'] ?? '')) }}</strong> {{ $item['dano']['tipo'] ?? '' }}</div>
                                @if(!empty($item['dano']['observacoes']))
                                    <div>{{ $item['dano']['observacoes'] }}</div>
                                @endif
                                @if(!empty($item['dano']['correcao']) || !empty($item['dano']['correcao_data']))
                                    <div class="muted">
                                        Correcao: {{ $item['dano']['correcao'] ?? '' }}{{ !empty($item['dano']['correcao_data']) ? (' - ' . $item['dano']['correcao_data']) : '' }}
                                    </div>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="box"><p class="muted">Nenhum item registrado nesta OS.</p></div>
    @endif

    @if(!empty($data['danos']) && is_array($data['danos']) && count($data['danos']) > 0)
        <div class="section-title">Avarias Vinculadas</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:10%">#</th>
                    <th style="width:18%">Local</th>
                    <th style="width:18%">Tipo</th>
                    <th style="width:34%">Observacoes</th>
                    <th style="width:20%">Correcao</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['danos'] as $d)
                    <tr>
                        <td>#{{ $d['id'] }}</td>
                        <td>{{ strtoupper((string) ($d['local'] ?? '')) }}</td>
                        <td>{{ $d['tipo'] ?? '-' }}</td>
                        <td>{{ $d['observacoes'] ?? '-' }}</td>
                        <td>
                            @if(!empty($d['correcao']) || !empty($d['correcao_data']))
                                {{ $d['correcao'] ?? '' }}{{ !empty($d['correcao_data']) ? (' - ' . $d['correcao_data']) : '' }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="doc-footer">
        Documento gerado pelo sistema &bull; {{ $data['documento']['empresa']['nome'] ?? '' }} &bull; Emissao {{ $data['documento']['data_emissao'] ?? '' }}
    </div>
</div>
</body>
</html>

