@extends('adminlte::page')

@section('title', 'Créditos Tributários')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Créditos Tributários" />
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
                    <h5 class="font-weight-bold text-dark mb-1">Como funciona a não cumulatividade e os créditos fiscais?</h5>
                    <p class="text-secondary mb-0 text-sm">
                        Sob o regime da Lei Complementar 214/2025, os impostos pagos na aquisição de insumos (como veículos para a frota, peças de reposição e contratação de serviços de manutenção) geram <strong>créditos tributários de IBS e CBS</strong>.
                        Esses créditos documentados podem ser integralmente compensados com os débitos gerados no faturamento das locações. 
                        Este painel controla o saldo acumulado e a apropriação dos créditos fiscais com base nos documentos fiscais de entrada.
                    </p>
                </div>
            </div>
        </div>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head">
                    <h5 class="fc-title">
                        <i class="fas fa-piggy-bank"></i>
                        <div>
                            <span>Listagem de Créditos Apropriados</span>
                            <span class="fc-subtitle">Créditos de não cumulatividade gerados por aquisição de frota, insumos e serviços</span>
                        </div>
                    </h5>
                </div>
            </div>
            
            <div class="card-body p-0">
                <table class="table fc-table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tributo</th>
                            <th>Valor Original</th>
                            <th>Valor Utilizado</th>
                            <th>Saldo</th>
                            <th>Origem</th>
                            <th>Data Apropriação</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($creditos as $credito)
                            <tr>
                                <td>{{ $refId = $credito->id }}</td>
                                <td><span class="badge badge-success">{{ $credito->tributo }}</span></td>
                                <td><strong>R$ {{ number_format($credito->valor_original, 2, ',', '.') }}</strong></td>
                                <td>R$ {{ number_format($credito->valor_utilizado, 2, ',', '.') }}</td>
                                <td><strong class="text-blue">R$ {{ number_format($credito->saldo, 2, ',', '.') }}</strong></td>
                                <td>{{ $credito->origem }}</td>
                                <td>{{ $credito->data_apropriacao->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($credito->status === 'DISPONIVEL')
                                        <span class="fc-badge fc-badge--pago">
                                            <i class="fas fa-check-circle"></i> {{ $credito->status }}
                                        </span>
                                    @else
                                        <span class="fc-badge fc-badge--cancelado">
                                            {{ $credito->status }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Nenhum crédito tributário apropriado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($creditos->hasPages())
                <div class="card-footer py-3">
                    {{ $creditos->links() }}
                </div>
            @endif
        </div>
    </div>
@stop
