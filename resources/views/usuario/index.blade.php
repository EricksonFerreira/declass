@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('usuario.create')" :createCan="'usuario.create'" />
@stop

@section('content')

    @if (!empty($filters))
        <x-filter
            :filters="$filters"
            :additionalFilters="$additionalFilters"
            :itensFiltered="$itensFiltered"
            :nomeFiltro="$nomeFiltro"
        />
    @endif

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header px-3 py-2">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                <h3 class="card-title mb-0 font-weight-bold">
                    <i class="fas fa-users mr-1 text-primary"></i>
                    REGISTROS
                    <span class="badge badge-success ml-2">{{ $usuarios->total() }}</span>
                </h3>

                <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                    <form action="{{ route('usuario.setPerPage') }}" method="POST" class="mb-0">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>

                    {{ $usuarios->links() }}
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm mb-0 tabela-usuarios">
                    <thead class="thead-light">
                        <tr>
                            <th class="pl-3" style="min-width: 220px;">Usuário</th>
                            <th style="min-width: 220px;">Contato</th>
                            <th style="width: 140px;">Regra</th>
                            <th>Cidade</th>
                            <th >Cadastro</th>
                            <th class="text-center" style="min-width: 300px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usuarios as $usuario)
                            @php
                                $canMetodoPagamento = auth()->user()->hasPermissao('usuario.metodoPagamento');
                                $canEditar = auth()->user()->hasPermissao('usuario.edit');
                                $canAlterarSenha = auth()->user()->hasPermissao('usuario.alterarSenha');
                                $canExcluir = auth()->user()->hasPermissao('usuario.destroy');
                                $temAlgumaAcao = $canMetodoPagamento || $canEditar || $canAlterarSenha || $canExcluir;
                            @endphp

                            <tr>
                                <td class="pl-3 align-middle">
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold text-dark">{{ $usuario->nome }}</span>
                                        <small class="text-muted">ID: #{{ $usuario->id }}</small>
                                    </div>
                                </td>

                                <td class="align-middle">
                                    <div class="d-flex flex-column">
                                        <span>{{ $usuario->email ?: '—' }}</span>
                                        <small class="text-muted">
                                            <i class="fas fa-phone-alt mr-1"></i>{{ $usuario->celular ?: 'Sem celular' }}
                                        </small>
                                    </div>
                                </td>

                                <td class="align-middle">
                                    @if ($usuario->tipoUsuario)
                                        <span class="badge badge-info px-2 py-1">{{ $usuario->tipoUsuario->name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="align-middle">
                                    {{ $usuario->cidade ?: '—' }}
                                </td>

                                <td class="align-middle">
                                    <span class="text-muted">{{ $usuario->created_at ? $usuario->created_at->format('d/m/Y') : '—' }}</span>
                                </td>

                                <td class="align-middle text-center">
                                    @if ($temAlgumaAcao)
                                        <div class="acoes-usuario">
                                            @if ($canMetodoPagamento)
                                                <a class="btn btn-xs btn-success"
                                                    href="{{ route('usuario.metodoPagamento', $usuario->id) }}">
                                                    <i class="fas fa-credit-card mr-1"></i> Pagamento
                                                </a>
                                            @endif

                                            @if ($canEditar)
                                                <a class="btn btn-xs btn-warning"
                                                    href="{{ route('usuario.edit', $usuario->id) }}">
                                                    <i class="fas fa-edit mr-1"></i> Editar
                                                </a>
                                            @endif

                                            @if ($canAlterarSenha)
                                                <a class="btn btn-xs btn-info"
                                                    href="{{ route('usuario.alterarSenha', $usuario->id) }}">
                                                    <i class="fas fa-key mr-1"></i> Senha
                                                </a>
                                            @endif

                                            @if ($canExcluir)
                                                <form action="{{ route('usuario.destroy', $usuario->id) }}"
                                                    method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger">
                                                        <i class="fas fa-trash mr-1"></i> Excluir
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                    Nenhum usuário encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($usuarios->hasPages())
            <div class="card-footer py-2 d-flex justify-content-end">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>

@stop

@section('css')
    <style>
        .tabela-usuarios thead th {
            vertical-align: middle;
            white-space: nowrap;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .tabela-usuarios tbody td {
            vertical-align: middle;
        }

        .tabela-usuarios tbody tr:hover {
            background-color: #f8fbff;
        }

        .acoes-usuario {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 6px;
        }

        .acoes-usuario .btn {
            min-width: 95px;
        }

        .tabela-usuarios .badge {
            font-size: 11px;
        }
    </style>
@stop

@section('js')
<script></script>
@stop