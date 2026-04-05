@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">{{ $titulo }}</h1>
        @if(auth()->user()?->hasPermissao('nfse.importarPendentes') || auth()->user()?->isAdministrador())
            <div class="d-flex flex-column align-items-end">
                <form action="{{ route('nfse.importarPendentes') }}" method="POST" class="d-flex align-items-center">
                @csrf
                <button class="btn btn-sm btn-outline-primary" type="submit" {{ $podeImportar ? '' : 'disabled' }}>
                    <i class="fas fa-sync-alt mr-1"></i> Importar pendentes
                </button>
                </form>
                <small class="text-muted mt-1">
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
                </small>
            </div>
        @endif
    </div>
@stop

@section('content')
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

    <div class="card">
        <div class="card-header">
            <strong>Notas fiscais emitidas</strong>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
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
                        <tr>
                            <td>{{ $nfse->id }}</td>
                            <td>{{ $nfse->reserva_id ?? '-' }}</td>
                            <td>{{ $nfse->empresa?->nome ?? '-' }}</td>
                            <td>{{ ucfirst($nfse->status ?? '-') }}</td>
                            <td>{{ $nfse->numero ?? '-' }}</td>
                            <td>{{ $nfse->ambiente ?? '-' }}</td>
                            <td>{{ $nfse->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="text-center">
                                @if(!empty($nfse->nfse_id))
                                    <a class="btn btn-xs btn-outline-primary" href="{{ route('nfse.download', ['id' => $nfse->id, 'tipo' => 'xml']) }}">
                                        XML
                                    </a>
                                    <a class="btn btn-xs btn-outline-secondary" href="{{ route('nfse.download', ['id' => $nfse->id, 'tipo' => 'dps']) }}">
                                        DPS
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Nenhuma nota fiscal encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $nfseList->links() }}
        </div>
    </div>
@stop
