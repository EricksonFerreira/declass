@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :subtitle="$subtitulo">
        <a href="{{ route('planos.hub') }}" class="btn btn-xs btn-outline-secondary">
            <i class="fas fa-th-large mr-1"></i> Central
        </a>
        <a href="{{ route('plano.index') }}" class="btn btn-xs btn-outline-secondary">
            <i class="fas fa-tags mr-1"></i> Catalogo
        </a>
        <a href="{{ route('assinatura.ajuda') }}" class="btn btn-xs btn-outline-secondary">
            <i class="fas fa-question-circle mr-1"></i> Ajuda
        </a>
        <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modalAtribuir">
            <i class="fa fa-plus" style="font-size:10px"></i> Atribuir
        </button>
    </x-page-title>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <x-page-header-card :showHeader="false">
        {{-- Filtros --}}
        <div class="card card-outline card-secondary mb-3">
            <div class="card-body py-2">
                <form method="GET" class="form-inline flex-wrap" style="gap:8px;">
                    <input type="text" name="empresa" value="{{ request('empresa') }}"
                           class="form-control form-control-sm" placeholder="Nome da empresa">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Todos os status</option>
                        @foreach (['ativo','trial','inadimplente','aguardando_pagamento','cancelado'] as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>
                    <select name="plano_id" class="form-control form-control-sm">
                        <option value="">Todos os planos</option>
                        @foreach ($planos as $p)
                            <option value="{{ $p->id }}" {{ request('plano_id') == $p->id ? 'selected' : '' }}>{{ $p->nome }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search mr-1"></i>Filtrar</button>
                    <a href="{{ route('assinatura.index') }}" class="btn btn-outline-secondary btn-sm">Limpar</a>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header py-2">
                <h3 class="card-title mb-0 text-sm">
                    Assinaturas
                    <span class="badge badge-primary ml-1">{{ $assinaturas->total() }}</span>
                </h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-sm mb-0" style="font-size:13px;">
                    <thead class="thead-light">
                        <tr>
                            <th class="pl-3">Empresa</th>
                            <th>Plano</th>
                            <th>Periodicidade</th>
                            <th>Valor</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th>Cobranca</th>
                            <th class="text-center">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assinaturas as $ass)
                            @php
                                $badge = $ass->status_badge;
                                $dias  = $ass->diasParaVencer();
                            @endphp
                            <tr>
                                <td class="pl-3 align-middle font-weight-bold">{{ $ass->empresa->nome }}</td>
                                <td class="align-middle">
                                    {{ $ass->plano->nome }}
                                    @if ($ass->plano->destaque)
                                        <i class="fas fa-star text-warning ml-1" style="font-size:.7rem;" title="Destaque"></i>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-light border">{{ $ass->periodicidade_texto }}</span>
                                </td>
                                <td class="align-middle">R$ {{ number_format($ass->valor_cobrado, 2, ',', '.') }}</td>
                                <td class="align-middle">
                                    {{ $ass->data_vencimento->format('d/m/Y') }}
                                    @if ($dias <= 10 && $dias >= 0 && $ass->estaAtivo())
                                        <span class="badge badge-warning ml-1">{{ $dias }}d</span>
                                    @elseif ($dias < 0 && !in_array($ass->status, ['cancelado']))
                                        <span class="badge badge-danger ml-1">{{ abs($dias) }}d atraso</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                                </td>
                                <td class="align-middle text-capitalize" style="font-size:.8rem;">
                                    {{ str_replace('_', ' ', $ass->metodo_cobranca) }}
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center" style="gap:4px;">
                                        <a href="{{ route('assinatura.show', $ass) }}"
                                           class="btn btn-xs btn-outline-secondary" title="Ver detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">Nenhuma assinatura encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($assinaturas->hasPages())
                <div class="card-footer py-2">{{ $assinaturas->links() }}</div>
            @endif
        </div>
    </x-page-header-card>
@stop

@section('footer')
{{-- Modal Atribuir Plano --}}
<div class="modal fade" id="modalAtribuir" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('assinatura.atribuir') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-link mr-1"></i> Atribuir Plano a Empresa</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Empresa <span class="text-danger">*</span></label>
                                <select name="empresa_id" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    @foreach (\App\Models\Empresa::orderBy('nome')->get() as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Plano <span class="text-danger">*</span></label>
                                <select name="plano_id" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    @foreach ($planos as $p)
                                        <option value="{{ $p->id }}">{{ $p->nome }} — R$ {{ number_format($p->valor_mensal, 2, ',', '.') }}/mes</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Periodicidade <span class="text-danger">*</span></label>
                                <select name="periodicidade" class="form-control" required>
                                    <option value="mensal">Mensal</option>
                                    <option value="anual">Anual (com desconto)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Metodo de Cobranca <span class="text-danger">*</span></label>
                                <select name="metodo_cobranca" class="form-control" required>
                                    <option value="boleto">Boleto Bancario</option>
                                    <option value="automatico">Automatico (debito)</option>
                                    <option value="manual">Manual</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Dia de Vencimento</label>
                                <input type="number" name="dia_vencimento" class="form-control"
                                       min="1" max="28" placeholder="Ex.: 10">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Data de Inicio <span class="text-danger">*</span></label>
                                <input type="date" name="data_inicio" class="form-control"
                                       value="{{ now()->toDateString() }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Data de Vencimento <span class="text-danger">*</span></label>
                                <input type="date" name="data_vencimento" class="form-control"
                                       value="{{ now()->addMonth()->toDateString() }}" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label>Observacao</label>
                                <input type="text" name="observacao" class="form-control" maxlength="500"
                                       placeholder="Opcional">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Atribuir Plano
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
