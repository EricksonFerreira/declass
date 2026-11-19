@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Empresas da plataforma (escolas e redes)" :create-url="route('empresa.create')" create-can="gerenciar-empresa" />
@stop

@push('css')
    @include('partials.ux-index-styles')
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
        @include('partials.ux-view-toggle', ['viewKey' => 'empresa_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
                <div class="ux-cards">
            @forelse($empresas as $empresa)
                <article class="ux-card">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar"><i class="fas fa-building"></i></div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title"><a href="{{ route('empresa.show', $empresa->id) }}" class="d-inline-flex align-items-center font-weight-bold">
                                    @if($empresa->logo_url)
                                        <img src="{{ $empresa->logo_url }}" alt="" class="mr-2 rounded" style="width:34px;height:34px;object-fit:contain;background:#f8f9fa;border:1px solid #e9ecef;">
                                    @else
                                        <span class="mr-2 rounded d-inline-flex align-items-center justify-content-center text-muted" style="width:34px;height:34px;background:#f8f9fa;border:1px solid #e9ecef;"><i class="fas fa-building"></i></span>
                                    @endif
                                    {{ $empresa->nome }}
                                </a></div>
                            <div class="ux-card__sub"><code>{{ $empresa->slug }}</code>
                                @if($empresa->dominio)<div class="text-muted" style="font-size:.75rem;">{{ $empresa->dominio }}</div>@endif</div>
                        </div>
                        <div class="ux-card__badges">
                            @if($empresa->esta_ativa)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">CNPJ</span>
                            <span class="ux-card__meta-value">{{ $empresa->cnpj ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Escolas</span>
                            <span class="ux-card__meta-value">{{ $empresa->escolas->count() }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Usuários</span>
                            <span class="ux-card__meta-value">{{ $empresa->users->count() }}</span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        <a href="{{ route('empresa.show', $empresa->id) }}" class="btn btn-xs btn-info" title="Ver"><i class="fa fa-eye"></i></a>
                                <a href="{{ route('empresa.edit', $empresa->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                <a href="{{ route('empresa.site.edit', $empresa->id) }}" class="btn btn-xs btn-secondary" title="Site público"><i class="fa fa-globe"></i></a>
                                <x-delete-button :action="route('empresa.destroy', $empresa->id)" titulo="Excluir" mensagem="Excluir a empresa &quot;{{ $empresa->nome }}&quot;? Os dados são preservados (exclusão lógica)." />
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-building fa-2x mb-2 d-block"></i>
                    Nenhuma empresa encontrada.
                </div>
            @endforelse
        </div>
        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nome</th>
                        <th>Slug / Domínio</th>
                        <th>CNPJ</th>
                        <th>Escolas</th>
                        <th>Usuários</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($empresas as $empresa)
                        <tr>
                            <td>
                                <a href="{{ route('empresa.show', $empresa->id) }}" class="d-inline-flex align-items-center font-weight-bold">
                                    @if($empresa->logo_url)
                                        <img src="{{ $empresa->logo_url }}" alt="" class="mr-2 rounded" style="width:34px;height:34px;object-fit:contain;background:#f8f9fa;border:1px solid #e9ecef;">
                                    @else
                                        <span class="mr-2 rounded d-inline-flex align-items-center justify-content-center text-muted" style="width:34px;height:34px;background:#f8f9fa;border:1px solid #e9ecef;"><i class="fas fa-building"></i></span>
                                    @endif
                                    {{ $empresa->nome }}
                                </a>
                            </td>
                            <td>
                                <code>{{ $empresa->slug }}</code>
                                @if($empresa->dominio)<div class="text-muted" style="font-size:.75rem;">{{ $empresa->dominio }}</div>@endif
                            </td>
                            <td>{{ $empresa->cnpj ?? '—' }}</td>
                            <td>{{ $empresa->escolas->count() }}</td>
                            <td>{{ $empresa->users->count() }}</td>
                            <td>
                                @if($empresa->esta_ativa)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('empresa.show', $empresa->id) }}" class="btn btn-xs btn-info" title="Ver"><i class="fa fa-eye"></i></a>
                                <a href="{{ route('empresa.edit', $empresa->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                <a href="{{ route('empresa.site.edit', $empresa->id) }}" class="btn btn-xs btn-secondary" title="Site público"><i class="fa fa-globe"></i></a>
                                <x-delete-button :action="route('empresa.destroy', $empresa->id)" titulo="Excluir" mensagem="Excluir a empresa &quot;{{ $empresa->nome }}&quot;? Os dados são preservados (exclusão lógica)." />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-building fa-2x mb-2 d-block"></i>
                                Nenhuma empresa encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($empresas->hasPages())
        <div class="card-footer">
            {{ $empresas->links() }}
        </div>
    @endif
</div>
@stop
