@extends('adminlte::page')

@section('title', 'Regras Tributárias')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Regras Tributárias (Parâmetros)" />
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
                    <h5 class="font-weight-bold text-dark mb-1">Como funcionam as regras de alíquota na Transição (2026-2033)?</h5>
                    <p class="text-secondary mb-0 text-sm">
                        O motor tributário utiliza esta tabela parametrizada para encontrar as alíquotas correspondentes no momento em que a locação é fechada.
                        Isso evita regras rígidas (*hardcoded*) no código. Em 2026, iniciamos com o período de testes trazendo <strong>0,9% de CBS</strong> e <strong>0,1% de IBS</strong>.
                        A partir de 2027, as alíquotas plenas passam a vigorar conforme regulamentação regional/federal, e o sistema fará a transição automática com base nas datas de início e fim cadastradas.
                    </p>
                </div>
            </div>
        </div>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head">
                    <h5 class="fc-title">
                        <i class="fas fa-cogs"></i>
                        <div>
                            <span>Listagem de Regras Cadastradas</span>
                            <span class="fc-subtitle">Configurações de vigência e percentuais das alíquotas de IBS/CBS</span>
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
                            <th>Operação</th>
                            <th>Alíquota</th>
                            <th>Início Vigência</th>
                            <th>Fim Vigência</th>
                            <th>Gera Crédito</th>
                            <th>Ativo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($regras as $regra)
                            <tr>
                                <td>{{ $regra->id }}</td>
                                <td><span class="badge badge-info">{{ $regra->tributo }}</span></td>
                                <td><code>{{ $regra->tipo_operacao }}</code></td>
                                <td><strong>{{ number_format($regra->aliquota, 2, ',', '.') }}%</strong></td>
                                <td>{{ $regra->data_inicio->format('d/m/Y') }}</td>
                                <td>{{ $regra->data_fim ? $regra->data_fim->format('d/m/Y') : 'Vigência Indeterminada' }}</td>
                                <td>
                                    <span class="badge {{ $regra->gera_credito ? 'badge-success' : 'badge-light' }}">
                                        {{ $regra->gera_credito ? 'Sim' : 'Não' }}
                                    </span>
                                </td>
                                <td>
                                    @if($regra->ativo)
                                        <span class="fc-badge fc-badge--pago">
                                            <i class="fas fa-check"></i> Ativo
                                        </span>
                                    @else
                                        <span class="fc-badge fc-badge--cancelado">
                                            <i class="fas fa-times"></i> Inativo
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Nenhuma regra tributária configurada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($regras->hasPages())
                <div class="card-footer py-3">
                    {{ $regras->links() }}
                </div>
            @endif
        </div>
    </div>
@stop
