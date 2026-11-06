@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Registros disciplinares e pedagógicos dos alunos" :create-url="auth()->user()->hasPermissao('ocorrencia.create') ? route('ocorrencia.create') : null" :create-can="'ocorrencia.create'" />
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
                        <th>Data</th>
                        <th>Aluno</th>
                        <th>Escola</th>
                        <th>Tipo</th>
                        <th>Gravidade</th>
                        <th>Descrição</th>
                        <th>Registrado por</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ocorrencias as $ocorrencia)
                        <tr>
                            <td class="text-nowrap">{{ $ocorrencia->data->format('d/m/Y') }}</td>
                            <td class="font-weight-bold">{{ $ocorrencia->aluno->pessoa->nome ?? '—' }}</td>
                            <td>{{ $ocorrencia->escola->nome ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $ocorrencia->tipo === 'elogio' ? 'success' : ($ocorrencia->tipo === 'suspensao' ? 'danger' : 'warning') }}">
                                    {{ $ocorrencia->tipoLabel() }}
                                </span>
                            </td>
                            <td>
                                @php($grav = ['leve' => 'secondary', 'media' => 'warning', 'grave' => 'danger'][$ocorrencia->gravidade] ?? 'secondary')
                                <span class="badge badge-{{ $grav }}">{{ $ocorrencia->gravidadeLabel() }}</span>
                            </td>
                            <td style="max-width:280px;">
                                <small>{{ \Illuminate\Support\Str::limit($ocorrencia->descricao, 100) }}</small>
                                @if($ocorrencia->notifica_responsavel)
                                    <br><small class="text-info"><i class="fas fa-bell"></i> responsável notificado</small>
                                @endif
                            </td>
                            <td><small>{{ $ocorrencia->responsavel->name ?? '—' }}</small></td>
                            <td class="text-right">
                                @can('ocorrencia.edit')
                                    <a href="{{ route('ocorrencia.edit', $ocorrencia->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('ocorrencia.destroy')
                                    <x-delete-button :action="route('ocorrencia.destroy', $ocorrencia->id)" titulo="Excluir" mensagem="Excluir a ocorrência de {{ $ocorrencia->aluno->pessoa->nome ?? 'aluno' }}?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>
                                Nenhuma ocorrência registrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($ocorrencias->hasPages())
        <div class="card-footer">
            {{ $ocorrencias->links() }}
        </div>
    @endif
</div>
@stop
