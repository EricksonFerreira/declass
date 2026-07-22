@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="row">

        <!-- Faturamento por Reserva -->
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card h-100 border-0 shadow-sm portal-card" style="border-radius: 16px; transition: transform 0.2s, box-shadow 0.2s;">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <div>
                        <div class="portal-icon bg-success-light text-success mb-3">
                            <i class="fas fa-receipt fa-2x"></i>
                        </div>
                        <h4 class="font-weight-bold mb-2" style="color: #1e293b;">Faturamento por Reserva</h4>
                        <p class="text-secondary small">Visualize o faturamento total acumulado e o ticket médio agrupados detalhadamente por contrato de locação.</p>
                    </div>
                    <a href="{{ route('financeiro.reservas') }}" class="btn btn-success w-100 font-weight-bold mt-3 py-2" style="border-radius: 8px;">
                        Acessar Faturamento
                    </a>
                </div>
            </div>
        </div>

        <!-- Fluxo de Caixa -->
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card h-100 border-0 shadow-sm portal-card" style="border-radius: 16px; transition: transform 0.2s, box-shadow 0.2s;">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <div>
                        <div class="portal-icon bg-info-light text-info mb-3">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                        <h4 class="font-weight-bold mb-2" style="color: #1e293b;">Fluxo de Caixa</h4>
                        <p class="text-secondary small">Controle geral de entradas, despesas operacionais da frota, saldos de caixa, vencimentos e projeções financeiras.</p>
                    </div>
                    <a href="{{ route('financeiro.fluxo-caixa.dashboard') }}" class="btn btn-info w-100 text-white font-weight-bold mt-3 py-2" style="border-radius: 8px;">
                        Acessar Fluxo de Caixa
                    </a>
                </div>
            </div>
        </div>

        <!-- Notas Fiscais -->
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card h-100 border-0 shadow-sm portal-card" style="border-radius: 16px; transition: transform 0.2s, box-shadow 0.2s;">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <div>
                        <div class="portal-icon bg-danger-light text-danger mb-3">
                            <i class="fas fa-file-invoice fa-2x"></i>
                        </div>
                        <h4 class="font-weight-bold mb-2" style="color: #1e293b;">Notas Fiscais (NFS-e)</h4>
                        <p class="text-secondary small">Gerencie a emissão, cancelamento e o lote de envio de Notas Fiscais de Serviço eletrônicas com a prefeitura.</p>
                    </div>
                    <a href="{{ route('financeiro.nfse.index') }}" class="btn btn-danger w-100 font-weight-bold mt-3 py-2" style="border-radius: 8px;">
                        Acessar Notas Fiscais
                    </a>
                </div>
            </div>
        </div>

        <!-- Analise por Veiculo -->
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card h-100 border-0 shadow-sm portal-card" style="border-radius: 16px; transition: transform 0.2s, box-shadow 0.2s;">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <div>
                        <div class="portal-icon bg-primary-light text-primary mb-3">
                            <i class="fas fa-car-side fa-2x"></i>
                        </div>
                        <h4 class="font-weight-bold mb-2" style="color: #1e293b;">Análise por Veículo</h4>
                        <p class="text-secondary small">Compare receitas, despesas e resultado operacional por placa para identificar quais ativos dão lucro ou prejuízo.</p>
                    </div>
                    <a href="{{ route('financeiro.analise-veiculos') }}" class="btn btn-primary w-100 font-weight-bold mt-3 py-2" style="border-radius: 8px;">
                        Acessar DRE por Carro
                    </a>
                </div>
            </div>
        </div>

        <!-- Cupons de Desconto -->
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card h-100 border-0 shadow-sm portal-card" style="border-radius: 16px; transition: transform 0.2s, box-shadow 0.2s;">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <div>
                        <div class="portal-icon bg-warning-light text-warning mb-3">
                            <i class="fas fa-percentage fa-2x"></i>
                        </div>
                        <h4 class="font-weight-bold mb-2" style="color: #1e293b;">Cupons de Desconto</h4>
                        <p class="text-secondary small">Cadastre, edite e configure regras de validade de cupons promocionais para aluguel de veículos.</p>
                    </div>
                    <a href="{{ route('financeiro.cupom_desconto.index') }}" class="btn btn-warning w-100 font-weight-bold mt-3 py-2 text-dark" style="border-radius: 8px;">
                        Acessar Cupons
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .portal-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
        }
        .portal-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bg-primary-light { background-color: #eff6ff; }
        .bg-success-light { background-color: #ecfdf5; }
        .bg-info-light { background-color: #e0f2fe; }
        .bg-danger-light { background-color: #fef2f2; }
        .bg-warning-light { background-color: #fffbeb; }
    </style>
@stop
