@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="PEI, PDI e planos de atendimento educacional especializado" :create-url="auth()->user()->hasPermissao('plano_atendimento.create') ? route('plano_atendimento.create') : null" :create-can="'plano_atendimento.create'" />
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
                        <th>Aluno</th>
                        <th>Escola</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Elaboração</th>
                        <th>Próx. revisão</th>
                        <th>Profissional</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planos as $plano)
                        <tr>
                            <td class="font-weight-bold">{{ $plano->aluno->pessoa->nome ?? '—' }}</td>
                            <td>{{ $plano->escola->nome ?? '—' }}</td>
                            <td><span class="badge badge-primary">{{ $plano->tipoLabel() }}</span></td>
                            <td>
                                @php($cor = ['ativo' => 'success', 'elaboracao' => 'info', 'suspenso' => 'warning', 'encerrado' => 'secondary'][$plano->status] ?? 'secondary')
                                <span class="badge badge-{{ $cor }}">{{ $plano->statusLabel() }}</span>
                                @if($plano->revisaoVencida())
                                    <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> revisão vencida</small>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ $plano->data_elaboracao?->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-nowrap">
                                {{ $plano->proxima_revisao?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td><small>{{ $plano->profissional->name ?? '—' }}</small></td>
                            <td class="text-right">
                                @can('plano_atendimento.edit')
                                    <a href="{{ route('plano_atendimento.edit', $plano->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('plano_atendimento.destroy')
                                    <x-delete-button :action="route('plano_atendimento.destroy', $plano->id)" titulo="Excluir" mensagem="Excluir o plano de {{ $plano->aluno->pessoa->nome ?? 'aluno' }}?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-hands-helping fa-2x mb-2 d-block"></i>
                                Nenhum plano de atendimento cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($planos->hasPages())
        <div class="card-footer">
            {{ $planos->links() }}
        </div>
    @endif
</div>
@stop
