@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Veja o sistema com as permissões de outro usuário — suas ações continuam registradas no seu nome" />
@stop

@section('content')
@if($alvo)
    <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap" style="gap:.5rem;">
        <span>
            <i class="fas fa-user-secret mr-1"></i>
            Espelhando o acesso de <strong>{{ $alvo->name }}</strong>
            ({{ $alvo->regra?->name ?? 'sem perfil' }} · {{ $alvo->empresa?->nome ?? 'sem empresa' }}).
        </span>
        <form method="POST" action="{{ route('espelhamento.destroy') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-dark">
                <i class="fas fa-times mr-1"></i> Parar espelhamento
            </button>
        </form>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('espelhamento.index') }}" class="form-inline">
            <div class="input-group input-group-sm" style="max-width:320px;">
                <input type="text" name="q" value="{{ $busca }}" class="form-control" placeholder="Buscar por nome ou e-mail...">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Usuário</th>
                        <th>Perfil</th>
                        <th>Empresa</th>
                        <th>Status</th>
                        <th class="text-right">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                        @php
                            $ehAlvo = $alvo && $alvo->id === $usuario->id;
                            $ehEu = $usuario->id === auth()->id();
                        @endphp
                        <tr class="{{ $ehAlvo ? 'table-warning' : '' }}">
                            <td>
                                <span class="font-weight-bold">{{ $usuario->name }}</span>
                                <div class="text-muted" style="font-size:.75rem;">{{ $usuario->email }}</div>
                            </td>
                            <td>
                                @if($usuario->is_super_admin)
                                    <span class="badge badge-danger">Superadmin</span>
                                @elseif($usuario->regra)
                                    <span class="badge badge-info">{{ $usuario->regra->name }}</span>
                                @else
                                    <span class="badge badge-secondary">Sem perfil</span>
                                @endif
                            </td>
                            <td>{{ $usuario->empresa?->nome ?? '—' }}</td>
                            <td>
                                @if($usuario->ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($ehAlvo)
                                    <span class="badge badge-warning mr-1"><i class="fas fa-eye mr-1"></i>Espelhando</span>
                                    <form method="POST" action="{{ route('espelhamento.destroy') }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-dark">Parar</button>
                                    </form>
                                @elseif($usuario->is_super_admin || $ehEu)
                                    <span class="text-muted small">—</span>
                                @elseif(!$usuario->ativo || !$usuario->empresa_id)
                                    <span class="text-muted small" title="{{ !$usuario->ativo ? 'Usuário inativo' : 'Sem empresa vinculada' }}">
                                        <i class="fas fa-ban"></i>
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('espelhamento.store', $usuario) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-outline-primary">
                                            <i class="fas fa-user-secret mr-1"></i> Espelhar acesso
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Nenhum usuário encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($usuarios->hasPages())
        <div class="card-footer">
            {{ $usuarios->links() }}
        </div>
    @endif
</div>
@stop
