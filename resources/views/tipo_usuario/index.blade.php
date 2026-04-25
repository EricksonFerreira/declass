@extends('adminlte::page')

@section('title', 'Perfis de Acesso')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Perfis de Acesso" subtitle="Defina quais funcionalidades cada tipo de colaborador pode acessar.">
        <a href="{{ route('tipo_usuario.create') }}" class="btn btn-xs btn-primary">
            <i class="fas fa-plus mr-1"></i> Novo Perfil
        </a>
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

    <div class="alert alert-light border mb-3" style="font-size:.84rem;">
        <i class="fas fa-info-circle text-primary mr-1"></i>
        Os <strong>Perfis de Acesso</strong> determinam o que cada colaborador pode ver e fazer no sistema.
        Ao criar um usuário você escolhe qual perfil ele terá.
    </div>

    <div class="card shadow-sm border-0" style="border-radius:12px;overflow:hidden;">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" style="font-size:.875rem;">
                <thead style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                    <tr>
                        <th class="pl-4 py-3" style="font-weight:700;color:#374151;">Perfil</th>
                        <th class="py-3" style="font-weight:700;color:#374151;">Permissões</th>
                        <th class="py-3 text-right pr-4" style="width:140px;font-weight:700;color:#374151;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tiposUsuario as $tipoUsuario)
                        <tr>
                            <td class="pl-4 py-3">
                                <div class="d-flex align-items-center" style="gap:.65rem;">
                                    <div style="width:34px;height:34px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-user-tag" style="color:#0a1628;font-size:.85rem;"></i>
                                    </div>
                                    <div>
                                        <div class="font-weight-bold" style="color:#0f172a;">{{ $tipoUsuario->name }}</div>
                                        <div class="text-muted" style="font-size:.75rem;">{{ $tipoUsuario->permissoes->count() }} permissão(ões) configurada(s)</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                @php
                                    $grupos = $tipoUsuario->permissoes->groupBy(fn($p) => explode('.', $p->name)[0]);
                                @endphp
                                <div class="d-flex flex-wrap" style="gap:.3rem;">
                                    @foreach ($grupos->keys()->take(6) as $g)
                                        <span class="badge badge-light border" style="font-size:.7rem;">{{ $g }}</span>
                                    @endforeach
                                    @if ($grupos->count() > 6)
                                        <span class="badge badge-secondary" style="font-size:.7rem;">+{{ $grupos->count() - 6 }} módulos</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 text-right pr-4">
                                <a href="{{ route('tipo_usuario.edit', $tipoUsuario->id) }}"
                                   class="btn btn-xs btn-outline-secondary mr-1">
                                    <i class="fas fa-edit mr-1"></i> Editar
                                </a>
                                <x-delete-button :action="route('tipo_usuario.destroy', $tipoUsuario->id)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="fas fa-user-tag fa-2x mb-2 d-block"></i>
                                Nenhum perfil criado ainda.
                                <a href="{{ route('tipo_usuario.create') }}" class="d-block mt-2">Criar primeiro perfil</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tiposUsuario->hasPages())
            <div class="card-footer py-2 d-flex justify-content-end" style="background:#f8fafc;">
                {{ $tiposUsuario->links() }}
            </div>
        @endif
    </div>
@stop
