@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('usuario.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $usuarios->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('usuario.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $usuarios->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Celular</th>
                        <th>Regra de Usuário</th>
                        <th>Cidade</th>
                        <th>Data de Criação</th>
                        <th style="width: 250px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->nome }}</td>
                            <td>{{ $usuario->email }}</td>
                            <td>{{ $usuario->celular }}</td>
                            <td>{{ $usuario->tipoUsuario->name ?? '' }}</td>
                            <td>{{ $usuario->cidade }}</td>
                            <td>{{ $usuario->created_at->format('d/m/Y') }}</td>
                            <td style="width: 180px;">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-xs btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-cog mr-2"></i> Ações
                                    </button>
                                    <div class="dropdown-menu" style="font-size:12px !important; overflow:scroll">
                                        <a class="dropdown-item bg-success" href="{{ route('usuario.metodoPagamento', $usuario->id) }}">
                                            <i class="fas fa-credit-card mr-2"></i> Métodos de Pagamento
                                        </a>
                                        <a href="{{ route('usuario.edit', $usuario->id) }}" class="dropdown-item bg-warning">
                                            <i class="fa fa-edit"></i> Editar
                                        </a>
                                        <a href="{{ route("usuario.alterarSenha",$usuario->id)}}" class="dropdown-item bg-info">
                                            <i class="fa fa-key"></i> Alterar Senhar
                                        </a>
                                        <x-delete-button :action="route('usuario.destroy', $usuario->id)" />
                                    </div>
                                </div>
                                {{-- <a href="{{ route('usuario.metodoPagamento', $usuario->id) }}" class="btn btn-xs btn-primary"><i class="fa fa-credit-card"></i> Métodos de Pagamento</a>
                                <a href="{{ route('usuario.edit', $usuario->id) }}" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <a href="{{ route("usuario.alterarSenha",$usuario->id)}}" class="btn btn-xs btn-info"><i class="fa fa-key"></i> Alterar Senhar</a>
                                <x-delete-button :action="route('usuario.destroy', $usuario->id)" /> --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script></script>
@stop
