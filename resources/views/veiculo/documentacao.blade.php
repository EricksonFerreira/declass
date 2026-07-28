@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[[
        'name' => 'Veículos',
        'url' => route('veiculo.index')
    ], [
        'name' => 'Documentação'
    ]]" />
    <x-page-title :title="$titulo" :createUrl="route('veiculo.documentacao.create')" />
@stop

@section('content')
    @php
        $tipos = [
            'ipva' => 'IPVA',
            'licenciamento' => 'Licenciamento',
            'seguro' => 'Seguros',
        ];
    @endphp

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0 text-sm text-bold">
                REGISTROS <span class="badge badge-success">{{ $documentacoes->count() }}</span>
            </h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Veículo</th>
                        <th>Parcelas</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th class="text-center">Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documentacoes as $doc)
                        @php
                            $collapseId = 'doc-' . $doc->id;
                            $statusCount = $doc->parcelas->countBy('status');
                        @endphp
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $tipos[$doc->tipo] ?? strtoupper((string) $doc->tipo) }}</div>
                                <div class="text-muted text-xs">{{ $doc->descricao ?: 'Sem descrição' }}</div>
                            </td>
                            <td>{{ $doc->veiculo?->nome ?? '—' }}</td>
                            <td>{{ $doc->parcelas_total }}</td>
                            <td>R$ {{ number_format((float) $doc->valor_parcela, 2, ',', '.') }}</td>
                            <td>
                                <span class="badge badge-{{ $doc->status === 'ativo' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($doc->status) }}
                                </span>
                                @if($statusCount->get('pago'))
                                    <span class="badge badge-success ml-1">{{ $statusCount->get('pago') }} pagas</span>
                                @endif
                                @if($statusCount->get('pendente'))
                                    <span class="badge badge-warning ml-1">{{ $statusCount->get('pendente') }} pendentes</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary" type="button" data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="collapse" id="{{ $collapseId }}">
                            <td colspan="6" class="bg-light">
                                <div class="p-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mb-0 bg-white">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Parcela</th>
                                                    <th>Mês</th>
                                                    <th>Vencimento</th>
                                                    <th>Pagamento</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($doc->parcelas as $parcela)
                                                    <tr>
                                                        <td>{{ $parcela->numero_parcela }}/{{ $doc->parcelas_total }}</td>
                                                        <td>{{ optional($parcela->data_vencimento)->format('m/Y') ?? '-' }}</td>
                                                        <td>{{ optional($parcela->data_vencimento)->format('d/m/Y') ?? '-' }}</td>
                                                        <td>{{ $parcela->data_pagamento ? $parcela->data_pagamento->format('d/m/Y') : '-' }}</td>
                                                        <td>
                                                            <span class="badge badge-{{ $parcela->status === 'pago' ? 'success' : ($parcela->status === 'atrasado' ? 'danger' : 'warning') }}">
                                                                {{ ucfirst($parcela->status) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">Nenhuma parcela gerada.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhuma documentação cadastrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
