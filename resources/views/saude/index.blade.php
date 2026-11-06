@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Alertas de saúde, alergias e contatos de emergência dos alunos" />
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
                        <th>Tipo sanguíneo</th>
                        <th>Alertas</th>
                        <th>Contato de emergência</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fichas as $ficha)
                        @php($aluno = $ficha->aluno)
                        <tr>
                            <td class="font-weight-bold">{{ $aluno->pessoa->nome ?? '—' }}</td>
                            <td>{{ $aluno->escola->nome ?? '—' }}</td>
                            <td>{{ $ficha->tipoSanguineoLabel() }}</td>
                            <td>
                                @if($ficha->temAlertas())
                                    @if($ficha->alergias)<span class="badge badge-danger mr-1" title="{{ $ficha->alergias }}">Alergias</span>@endif
                                    @if($ficha->condicoes_cronicas)<span class="badge badge-warning mr-1" title="{{ $ficha->condicoes_cronicas }}">Condições</span>@endif
                                    @if($ficha->medicamentos)<span class="badge badge-info" title="{{ $ficha->medicamentos }}">Medicamentos</span>@endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($ficha->contato_emergencia_nome)
                                    <small>
                                        {{ $ficha->contato_emergencia_nome }}
                                        @if($ficha->contato_emergencia_parentesco)({{ $ficha->contato_emergencia_parentesco }})@endif
                                        <br>{{ $ficha->contato_emergencia_telefone }}
                                    </small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('saude.show', $aluno->id) }}" class="btn btn-xs btn-info" title="Ver ficha"><i class="fa fa-eye"></i></a>
                                @can('saude.edit')
                                    <a href="{{ route('saude.edit', $aluno->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-notes-medical fa-2x mb-2 d-block"></i>
                                Nenhuma ficha de saúde cadastrada.
                                <br><small>Acesse o cadastro do aluno e use o botão "Ficha de Saúde".</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($fichas->hasPages())
        <div class="card-footer">
            {{ $fichas->links() }}
        </div>
    @endif
</div>
@stop
