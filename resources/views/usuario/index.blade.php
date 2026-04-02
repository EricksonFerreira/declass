@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('usuario.create')" :createCan="'usuario.create'" />
@stop

@section('content')

    {{-- Filtros --}}
    @if (!empty($filters))
        <x-filter
            :filters="$filters"
            :additionalFilters="$additionalFilters"
            :itensFiltered="$itensFiltered"
            :nomeFiltro="$nomeFiltro"
        />
    @endif

    <div class="card">
        <div class="card-header px-3 py-2">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                <h3 class="card-title mb-0 text-sm font-weight-bold">
                    Registros
                    <span class="badge badge-success ml-1">{{ $usuarios->total() }}</span>
                </h3>
                <div class="d-flex align-items-center" style="gap:8px;">
                    <form action="{{ route('usuario.setPerPage') }}" method="POST" class="mb-0">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    {{ $usuarios->links() }}
                </div>
            </div>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-sm mb-0" style="font-size:13px;">
                <thead class="thead-light">
                    <tr>
                        <th class="pl-3">Nome</th>
                        <th>E-mail</th>
                        <th>Celular</th>
                        <th>Regra</th>
                        <th>Cidade</th>
                        <th>Cadastro</th>
                        <th class="text-center" style="width:120px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $usuario)
                        <tr>
                            <td class="pl-3 align-middle">{{ $usuario->nome }}</td>
                            <td class="align-middle">{{ $usuario->email }}</td>
                            <td class="align-middle">{{ $usuario->celular }}</td>
                            <td class="align-middle">
                                @if ($usuario->tipoUsuario)
                                    <span class="badge badge-info">{{ $usuario->tipoUsuario->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="align-middle">{{ $usuario->cidade ?? '—' }}</td>
                            <td class="align-middle">{{ $usuario->created_at->format('d/m/Y') }}</td>
                            <td class="text-center align-middle">
                                @php
                                    $canMetodoPagamento = auth()->user()->hasPermissao('usuario.metodoPagamento');
                                    $canEditar = auth()->user()->hasPermissao('usuario.edit');
                                    $canAlterarSenha = auth()->user()->hasPermissao('usuario.alterarSenha');
                                    $canExcluir = auth()->user()->hasPermissao('usuario.destroy');
                                    $temAlgumaAcao = $canMetodoPagamento || $canEditar || $canAlterarSenha || $canExcluir;
                                @endphp

                                @if ($temAlgumaAcao)
                                    <div class="btn-group">
                                        <button type="button"
                                            class="btn btn-xs btn-outline-secondary dropdown-toggle"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-cog"></i> Ações
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                            @if ($canMetodoPagamento)
                                                <a class="dropdown-item"
                                                    href="{{ route('usuario.metodoPagamento', $usuario->id) }}">
                                                    <i class="fas fa-credit-card text-success mr-2"></i>Métodos de Pagamento
                                                </a>
                                            @endif

                                            @if ($canEditar)
                                                <a class="dropdown-item"
                                                    href="{{ route('usuario.edit', $usuario->id) }}">
                                                    <i class="fas fa-edit text-warning mr-2"></i>Editar
                                                </a>
                                            @endif

                                            @if ($canAlterarSenha)
                                                <a class="dropdown-item"
                                                    href="{{ route('usuario.alterarSenha', $usuario->id) }}">
                                                    <i class="fas fa-key text-info mr-2"></i>Alterar Senha
                                                </a>
                                            @endif

                                            @if ($canExcluir)
                                                <div class="dropdown-divider"></div>
                                                <x-delete-button :action="route('usuario.destroy', $usuario->id)" />
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                Nenhum usuário encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($usuarios->hasPages())
            <div class="card-footer py-2 d-flex justify-content-end">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>

@stop

@section('js')
<script></script>
@stop
