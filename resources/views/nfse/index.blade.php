@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    @include('financeiro.fluxo_caixa._styles')

    @php
        $financeiro = str_starts_with((string) Route::currentRouteName(), 'financeiro.');
        $routePrefix = $financeiro ? 'financeiro.' : '';
        $totalPagina = $nfseList->count();
        $emitidasPagina = $nfseList->getCollection()->filter(fn($n) => !empty($n->nfse_id))->count();
        $errosPagina = $nfseList->getCollection()->filter(fn($n) => strtolower((string) $n->status) === 'erro')->count();
    @endphp

    <div class="fc-page">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card fc-card fc-filter mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route($routePrefix.'nfse.index') }}" class="row align-items-end">
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="fc-label">Reserva</label>
                        <input type="number" name="reserva_id" class="form-control" value="{{ $filters['reserva_id'] ?? '' }}">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="fc-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Todos</option>
                            @foreach($statusDisponiveis as $status)
                                <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="fc-label">Numero</label>
                        <input type="text" name="numero" class="form-control" value="{{ $filters['numero'] ?? '' }}">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="fc-label">Ambiente</label>
                        <select name="ambiente" class="form-control">
                            <option value="">Todos</option>
                            @foreach($ambientesDisponiveis as $ambiente)
                                <option value="{{ $ambiente }}" {{ ($filters['ambiente'] ?? '') === $ambiente ? 'selected' : '' }}>{{ $ambiente }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="fc-label">Data inicio</label>
                        <input type="date" name="data_inicio" class="form-control" value="{{ $filters['data_inicio'] ?? '' }}">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="fc-label">Data fim</label>
                        <input type="date" name="data_fim" class="form-control" value="{{ $filters['data_fim'] ?? '' }}">
                    </div>
                    @if(auth()->user()?->isAdministrador() && $empresasDisponiveis->isNotEmpty())
                        <div class="col-lg-4 col-md-6 mb-2">
                            <label class="fc-label">Empresa</label>
                            <select name="empresa_id" class="form-control">
                                <option value="">Todas</option>
                                @foreach($empresasDisponiveis as $empresa)
                                    <option value="{{ $empresa->id }}" {{ (string) ($filters['empresa_id'] ?? '') === (string) $empresa->id ? 'selected' : '' }}>{{ $empresa->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-lg-8 col-md-6 mb-2 fc-actions justify-content-end">
                        <button class="btn btn-primary fc-btn" type="submit">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="{{ route($routePrefix.'nfse.index') }}" class="btn btn-outline-secondary fc-btn">
                            <i class="fas fa-times"></i>
                        </a>
                        @if(auth()->user()?->hasPermissao('nfse.importarPendentes') || auth()->user()?->isAdministrador())
                            <button class="btn btn-outline-primary fc-btn" type="submit" form="nfse-importar-pendentes-form" {{ $podeImportar ? '' : 'disabled' }}>
                                <i class="fas fa-sync-alt"></i> Importar pendentes
                            </button>
                        @endif
                    </div>
                </form>
                @if(auth()->user()?->hasPermissao('nfse.importarPendentes') || auth()->user()?->isAdministrador())
                    <form id="nfse-importar-pendentes-form" action="{{ route($routePrefix.'nfse.importarPendentes') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                @endif
            </div>
        </div>

        @if(auth()->user()?->hasPermissao('nfse.importarPendentes') || auth()->user()?->isAdministrador())
            <div class="card fc-card mb-4">
                <div class="card-body py-3 text-muted">
                    @if(!$podeImportar && !$ultimaImportacao && !$proximaImportacao)
                        Selecione uma empresa para importar notas fiscais.
                    @elseif($ultimaImportacao)
                        Ultima importacao: {{ $ultimaImportacao->format('d/m/Y H:i') }}
                        @if(!$podeImportar && $proximaImportacao)
                            · Proxima liberada: {{ $proximaImportacao->format('d/m/Y H:i') }}
                        @endif
                    @else
                        Nenhuma importacao registrada.
                    @endif
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-4 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--projetado h-100">
                    <div>
                        <span>Total listado</span>
                        <strong class="text-primary">{{ $nfseList->total() }}</strong>
                    </div>
                    <small>{{ $totalPagina }} nesta pagina</small>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--receita h-100">
                    <div>
                        <span>Emitidas nesta pagina</span>
                        <strong class="text-success">{{ $emitidasPagina }}</strong>
                    </div>
                    <small>Com XML/DPS disponivel</small>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--despesa h-100">
                    <div>
                        <span>Erros nesta pagina</span>
                        <strong class="text-danger">{{ $errosPagina }}</strong>
                    </div>
                    <small>Requerem revisao operacional</small>
                </div>
            </div>
        </div>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head">
                    <h5 class="fc-title">
                        <i class="fas fa-file-invoice"></i>
                        <span>Notas fiscais emitidas<span class="fc-subtitle">Documentos fiscais vinculados às reservas</span></span>
                    </h5>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table fc-table">
                    <thead>
                        <tr>
                            <th class="px-3">#</th>
                            <th>Reserva</th>
                            <th>Empresa</th>
                            <th>Status</th>
                            <th>Numero</th>
                            <th>Ambiente</th>
                            <th>Emissao</th>
                            <th class="text-center">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($nfseList as $nfse)
                            @php
                                $status = strtolower((string) ($nfse->status ?? ''));
                                $badgeClass = $status === 'erro'
                                    ? 'fc-badge--despesa'
                                    : (!empty($nfse->nfse_id) ? 'fc-badge--pago' : 'fc-badge--pendente');
                            @endphp
                            <tr>
                                <td class="px-3 font-weight-bold">#{{ $nfse->id }}</td>
                                <td>
                                    <span class="fc-badge bg-success-light">
                                        <i class="fas fa-hashtag"></i>{{ $nfse->reserva_id ?? '-' }}
                                    </span>
                                </td>
                                <td class="font-weight-bold">{{ $nfse->empresa?->nome ?? '-' }}</td>
                                <td><span class="fc-badge {{ $badgeClass }}">{{ ucfirst($nfse->status ?? '-') }}</span></td>
                                <td>{{ $nfse->numero ?? '-' }}</td>
                                <td>{{ $nfse->ambiente ?? '-' }}</td>
                                <td>{{ $nfse->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="text-center">
                                    @if(!empty($nfse->nfse_id))
                                        <a class="btn btn-sm btn-outline-primary fc-btn" href="{{ route($routePrefix.'nfse.download', ['id' => $nfse->id, 'tipo' => 'xml']) }}">
                                            XML
                                        </a>
                                        <a class="btn btn-sm btn-outline-secondary fc-btn" href="{{ route($routePrefix.'nfse.download', ['id' => $nfse->id, 'tipo' => 'dps']) }}">
                                            DPS
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fas fa-file-invoice d-block mb-3" style="font-size: 2.4rem; opacity: .18;"></i>
                                    Nenhuma nota fiscal encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end">
                {{ $nfseList->links() }}
            </div>
        </div>
    </div>
@stop
