@extends('adminlte::page')

@section('title', 'Gestão Tributária')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Gestão Tributária (Reforma Tributária - LC 214/2025)" />
@stop

@section('content')
    @include('financeiro.fluxo_caixa._styles')

    <div class="fc-page">
        <!-- Explanatory Box -->
        <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 8px; background-color: #ebf5ff;">
            <div class="d-flex">
                <div class="mr-3 text-blue" style="font-size: 1.5rem;">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h5 class="font-weight-bold text-dark mb-1">Como funciona a Reforma Tributária na Locação de Veículos?</h5>
                    <p class="text-secondary mb-0 text-sm">
                        De acordo com a <strong>Lei Complementar 214/2025</strong>, a locação de bens móveis (incluindo veículos) passa a ser fato gerador do <strong>IBS (Imposto sobre Bens e Serviços)</strong> e da <strong>CBS (Contribuição sobre Bens e Serviços)</strong>. 
                        Este painel consolida seus débitos gerados nas locações finalizadas e os créditos fiscais gerados nas compras de frota ou manutenção, garantindo a neutralidade tributária (não cumulatividade) até a apuração final.
                    </p>
                </div>
            </div>
        </div>

        <!-- KPIs Row -->
        <div class="row mb-4">
            <!-- Débitos IBS -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="fc-kpi fc-kpi--despesa">
                    <span>Débitos IBS</span>
                    <strong>R$ {{ number_format($debitosIbs, 2, ',', '.') }}</strong>
                    <small>Imposto estadual/municipal recolhido nas locações</small>
                </div>
            </div>

            <!-- Débitos CBS -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="fc-kpi fc-kpi--despesa" style="border-left: 4px solid var(--fc-amber);">
                    <span>Débitos CBS</span>
                    <strong>R$ {{ number_format($debitosCbs, 2, ',', '.') }}</strong>
                    <small>Contribuição federal recolhida nas locações</small>
                </div>
            </div>

            <!-- Créditos Disponíveis -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="fc-kpi fc-kpi--receita">
                    <span>Créditos Disponíveis</span>
                    <strong>R$ {{ number_format($creditosDisponiveis, 2, ',', '.') }}</strong>
                    <small>Créditos acumulados na compra de veículos/peças</small>
                </div>
            </div>

            <!-- Saldo Estimado -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="fc-kpi fc-kpi--saldo">
                    <span>Saldo Estimado a Pagar</span>
                    <strong>R$ {{ number_format($saldoEstimado, 2, ',', '.') }}</strong>
                    <small>Saldo a recolher na apuração periódica</small>
                </div>
            </div>
        </div>

        <!-- Links Cards -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card fc-card">
                    <div class="card-header py-3">
                        <div class="fc-head">
                            <h5 class="fc-title">
                                <i class="fas fa-receipt"></i>
                                <span>Operações Fiscais</span>
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted text-sm">Monitore os fatos geradores tributados e acompanhe o status da emissão da DPS/NFS-e Nacional.</p>
                        <a href="{{ route('fiscal.operacoes') }}" class="btn btn-primary fc-btn btn-block">
                            <i class="fas fa-eye"></i> Acompanhar Fatos Geradores
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card fc-card">
                    <div class="card-header py-3">
                        <div class="fc-head">
                            <h5 class="fc-title">
                                <i class="fas fa-cogs"></i>
                                <span>Regras Tributárias</span>
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted text-sm">Parametrizador dinâmico de alíquotas por período, garantindo que o sistema funcione na transição (2026-2033).</p>
                        <a href="{{ route('fiscal.regras') }}" class="btn btn-primary fc-btn btn-block">
                            <i class="fas fa-sliders-h"></i> Configurar Vigências e Alíquotas
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card fc-card">
                    <div class="card-header py-3">
                        <div class="fc-head">
                            <h5 class="fc-title">
                                <i class="fas fa-piggy-bank"></i>
                                <span>Créditos Tributários</span>
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted text-sm">Registre créditos de IBS/CBS documentados na compra de frota e custos operacionais.</p>
                        <a href="{{ route('fiscal.creditos') }}" class="btn btn-primary fc-btn btn-block">
                            <i class="fas fa-wallet"></i> Gerenciar Saldo de Créditos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
