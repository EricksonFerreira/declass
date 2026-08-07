@extends('adminlte::page')

@section('title', 'Operações Fiscais')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Operações Fiscais" />
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
                    <h5 class="font-weight-bold text-dark mb-1">O que são as Operações Fiscais?</h5>
                    <p class="text-secondary mb-0 text-sm">
                        Cada locação finalizada gera uma <strong>operação fiscal</strong> (fato gerador de tributação). 
                        O motor tributário calcula automaticamente os valores devidos com base na data da operação e nas alíquotas configuradas. 
                        A partir dessa operação, o sistema gera a <strong>DPS (Declaração de Prestação de Serviço)</strong> para emissão da NFS-e Nacional sob o código tributário <code>99.04.01</code> (Locação de Bens Móveis).
                    </p>
                </div>
            </div>
        </div>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head">
                    <h5 class="fc-title">
                        <i class="fas fa-receipt"></i>
                        <div>
                            <span>Listagem de Fatos Geradores</span>
                            <span class="fc-subtitle">Histórico de operações tributadas sob o regime de IBS/CBS</span>
                        </div>
                    </h5>
                </div>
            </div>
            
            <div class="card-body p-0">
                <table class="table fc-table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Locação / Reserva ID</th>
                            <th>Operação</th>
                            <th>Data Fato Gerador</th>
                            <th>Valor Operação</th>
                            <th>Base Cálculo</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($operacoes as $operacao)
                            <tr>
                                <td>{{ $operacao->id }}</td>
                                <td>
                                    @if($operacao->locacao_id)
                                        <a href="{{ route('reserva.show', $operacao->locacao_id) }}" class="text-blue">
                                            #{{ $operacao->locacao_id }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $operacao->tipo }}</td>
                                <td>{{ $operacao->data_fato_gerador->format('d/m/Y H:i') }}</td>
                                <td><strong>R$ {{ number_format($operacao->valor_operacao, 2, ',', '.') }}</strong></td>
                                <td>R$ {{ number_format($operacao->valor_base_calculo, 2, ',', '.') }}</td>
                                <td>
                                    @if($operacao->status === 'AUTORIZADA')
                                        <span class="fc-badge fc-badge--pago">
                                            <i class="fas fa-check-circle"></i> {{ $operacao->status }}
                                        </span>
                                    @elseif($operacao->status === 'CALCULADA')
                                        <span class="fc-badge fc-badge--receita">
                                            <i class="fas fa-calculator"></i> {{ $operacao->status }}
                                        </span>
                                    @else
                                        <span class="fc-badge fc-badge--pendente">
                                            <i class="fas fa-clock"></i> {{ $operacao->status }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    Nenhuma operação fiscal registrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($operacoes->hasPages())
                <div class="card-footer py-3">
                    {{ $operacoes->links() }}
                </div>
            @endif
        </div>
    </div>
@stop
