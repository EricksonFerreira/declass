@extends('adminlte::page')

@section('title', 'Classificações Fiscais (NCM)')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Classificações Fiscais (NCM)" />
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
                    <h5 class="font-weight-bold text-dark mb-1">Classificações Fiscais (NCM do Bem Móvel)</h5>
                    <p class="text-secondary mb-0 text-sm">
                        De acordo com as especificações da NFS-e Nacional para o grupo <strong>gLocBensMoveis</strong>, cada veículo cadastrado no sistema deve possuir um código de <strong>NCM (Nomenclatura Comum do Mercosul)</strong> associado. 
                        Esta tabela centraliza as classificações fiscais que serão compartilhadas por sua frota de veículos (ex: automóveis de passageiros, vans, furgões) para emissão correta da declaração fiscal.
                    </p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success shadow-sm mb-3" style="border-radius: 8px;">{{ session('success') }}</div>
        @endif

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head d-flex justify-content-between align-items-center">
                    <h5 class="fc-title mb-0">
                        <i class="fas fa-tags"></i>
                        <div>
                            <span>Listagem de NCMs Cadastrados</span>
                            <span class="fc-subtitle">Classificações fiscais associadas à frota de veículos</span>
                        </div>
                    </h5>
                    <a href="{{ route('fiscal.classificacoes.criar') }}" class="btn btn-primary fc-btn">
                        <i class="fas fa-plus mr-1"></i> Nova Classificação
                    </a>
                </div>
            </div>
            
            <div class="card-body p-0">
                <table class="table fc-table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descrição</th>
                            <th>NCM</th>
                            <th>Tipo do Bem</th>
                            <th>Qtd. Veículos Associados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classificacoes as $classificacao)
                            <tr>
                                <td>{{ $classificacao->id }}</td>
                                <td><strong>{{ $classificacao->descricao }}</strong></td>
                                <td><code>{{ $classificacao->ncm }}</code></td>
                                <td>{{ $classificacao->tipo_bem }}</td>
                                <td>{{ $classificacao->veiculos()->count() }} veiculo(s)</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Nenhuma classificação fiscal configurada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($classificacoes->hasPages())
                <div class="card-footer py-3">
                    {{ $classificacoes->links() }}
                </div>
            @endif
        </div>
    </div>
@stop
