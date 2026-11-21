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

<div class="card mb-2 filter-card">
    <div class="card-body p-2">
        <h6 class="filter-heading text-secondary text-xs mb-1"><i class="fa fa-filter mr-1"></i> Filtros</h6>
        <hr class="filter-heading m-0 mb-2">
        <form method="GET" action="{{ route('espelhamento.index') }}">
            <div class="row align-items-end">
                <div class="col-auto flex-grow-1 filter-field">
                    <label class="text-xs mb-0">Nome / usuário</label>
                    <input type="text" name="q" value="{{ $filtros['q'] }}" class="form-control form-control-sm" placeholder="Nome ou login...">
                </div>
                <div class="col-auto flex-grow-1 filter-field">
                    <label class="text-xs mb-0">E-mail</label>
                    <input type="text" name="email" value="{{ $filtros['email'] }}" class="form-control form-control-sm" placeholder="email@...">
                </div>
                <div class="col-auto flex-grow-1 filter-field">
                    <label class="text-xs mb-0">Empresa</label>
                    <select name="empresa_id" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach($empresas as $eid => $enome)
                            <option value="{{ $eid }}" {{ $filtros['empresa_id'] == $eid ? 'selected' : '' }}>{{ $enome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto filter-field">
                    <label class="text-xs mb-0">Tipo de conta</label>
                    <select name="tipo_conta" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($tiposConta as $valor => $rotulo)
                            <option value="{{ $valor }}" {{ $filtros['tipo_conta'] === $valor ? 'selected' : '' }}>{{ $rotulo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto filter-field">
                    <label class="text-xs mb-0">Perfil</label>
                    <select name="role_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($regras as $rid => $rnome)
                            <option value="{{ $rid }}" {{ $filtros['role_id'] == $rid ? 'selected' : '' }}>{{ $rnome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto filter-field">
                    <label class="text-xs mb-0">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        <option value="ativo" {{ $filtros['status'] === 'ativo' ? 'selected' : '' }}>Ativo</option>
                        <option value="inativo" {{ $filtros['status'] === 'inativo' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
                <div class="col-auto d-flex align-items-end filter-actions" style="gap:6px;">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-search mr-1"></i> Filtrar</button>
                </div>
            </div>
        </form>
        @if(count($chips) > 0)
            <div class="mt-2 d-flex flex-wrap align-items-center" style="gap:6px;">
                <span class="text-xs text-secondary text-bold">Aplicados:</span>
                @foreach($chips as $chip)
                    <a href="{{ route('espelhamento.index', request()->except($chip['param'])) }}"
                       class="badge badge-secondary font-weight-normal" title="Remover filtro">
                        <i class="fa fa-times mr-1"></i>{{ $chip['label'] }}: <b>{{ $chip['valor'] }}</b>
                    </a>
                @endforeach
                <a href="{{ route('espelhamento.index') }}" class="badge badge-danger font-weight-normal" title="Limpar todos">
                    <i class="fa fa-times mr-1"></i> Limpar tudo
                </a>
            </div>
        @endif
    </div>
</div>

<div class="card">
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
