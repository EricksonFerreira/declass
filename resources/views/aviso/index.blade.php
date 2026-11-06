@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Comunicados da escola para as famílias" :create-url="auth()->user()->hasPermissao('aviso.create') ? route('aviso.create') : null" :create-can="'aviso.create'" />
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Publicação</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Escola</th>
                        <th>Público</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($avisos as $aviso)
                        <tr>
                            <td class="text-nowrap">{{ optional($aviso->data_publicacao)->format('d/m/Y') }}</td>
                            <td>
                                <div class="font-weight-bold">{{ $aviso->titulo }}</div>
                                <div class="text-muted" style="font-size:.8rem; max-width:340px;">{{ \Illuminate\Support\Str::limit($aviso->mensagem, 90) }}</div>
                            </td>
                            <td>
                                @php
                                    $badgeTipo = ['info' => 'primary', 'urgente' => 'danger', 'evento' => 'success'][$aviso->tipo] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $badgeTipo }}">{{ \App\Models\Aviso::TIPOS[$aviso->tipo] ?? $aviso->tipo }}</span>
                                @if(($aviso->destino ?? 'familias') === 'staff')
                                    <span class="badge badge-dark" title="Mural interno — não aparece no portal das famílias"><i class="fa fa-users"></i> Equipe</span>
                                @endif
                            </td>
                            <td>{{ $aviso->escola->nome ?? '—' }}</td>
                            <td>
                                @if($aviso->ehGeral())
                                    <span class="badge badge-light border">Escola toda</span>
                                @else
                                    <span class="badge badge-info">{{ $aviso->turma->nome ?? 'Turma #'.$aviso->turma_id }}</span>
                                @endif
                            </td>
                            <td>
                                @if($aviso->estaAtivo())
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('aviso.edit')
                                    <a href="{{ route('aviso.edit', $aviso->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('aviso.destroy')
                                    <x-delete-button :action="route('aviso.destroy', $aviso->id)" titulo="Excluir" mensagem="Excluir o aviso &quot;{{ $aviso->titulo }}&quot;?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-bullhorn fa-2x mb-2 d-block"></i>
                                Nenhum aviso encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($avisos->hasPages())
        <div class="card-footer">
            {{ $avisos->links() }}
        </div>
    @endif
</div>
@stop
