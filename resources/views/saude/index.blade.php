@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Alertas de saúde, alergias e contatos de emergência dos alunos" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .saude-cards.ux-cards { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 10px; padding: 8px 6px 12px; }
        .saude-cards .ux-card { border-radius: 12px; box-shadow: 0 1px 4px rgba(11,18,32,.05); }
        .saude-cards .ux-card:hover { transform: none; box-shadow: 0 4px 14px rgba(11,18,32,.10); }
        .saude-cards .ux-card__accent { display: none; }
        .saude-cards .ux-card__head { padding: 9px 11px 7px; gap: 8px; }
        .saude-cards .ux-card__avatar { width: 32px; height: 32px; border-radius: 8px; font-size: .68rem; }
        .saude-cards .ux-card__title { font-size: .8rem; white-space: normal; }
        .saude-cards .ux-card__title a { color: inherit; }
        .saude-cards .ux-card__sub { font-size: .68rem; }
        .saude-cards .ux-card__badges { flex-direction: row; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
        .saude-cards .ux-card__badge { font-size: .6rem; padding: 3px 6px; }
        .saude-cards .ux-card__meta { padding: 7px 11px 5px; gap: 5px 10px; }
        .saude-cards .ux-card__meta-label { font-size: .6rem; }
        .saude-cards .ux-card__meta-value { font-size: .74rem; white-space: normal; }
        .saude-cards .ux-card__foot { padding: 7px 11px 9px; }
        .saude-cards .ux-card__foot .btn { padding: .15rem .45rem; font-size: .72rem; }
    </style>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
        @include('partials.ux-view-toggle', ['viewKey' => 'saude_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
        <div class="ux-cards saude-cards">
            @forelse($fichas as $ficha)
                @php
                    $aluno = $ficha->aluno;
                    $nome = $aluno->pessoa->nome ?? '?';
                    $iniciais = collect(explode(' ', trim($nome)))->filter()->take(2)
                        ->map(fn ($s) => mb_strtoupper(mb_substr($s, 0, 1)))->implode('') ?: 'A';
                @endphp
                <article class="ux-card">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar">{{ $iniciais }}</div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">
                                <a href="{{ route('saude.show', $aluno->id) }}">{{ $nome }}</a>
                            </div>
                            <div class="ux-card__sub">{{ $aluno->escola->nome ?? '—' }}</div>
                        </div>
                        <div class="ux-card__badges">
                            @if($ficha->temAlertas())
                                @if($ficha->alergias)<span class="ux-card__badge ux-card__badge--red" title="{{ $ficha->alergias }}">Alergias</span>@endif
                                @if($ficha->condicoes_cronicas)<span class="ux-card__badge ux-card__badge--amber" title="{{ $ficha->condicoes_cronicas }}">Condições</span>@endif
                                @if($ficha->medicamentos)<span class="ux-card__badge ux-card__badge--blue" title="{{ $ficha->medicamentos }}">Medicam.</span>@endif
                            @else
                                <span class="ux-card__badge ux-card__badge--gray">Sem alertas</span>
                            @endif
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Tipo sanguíneo</span>
                            <span class="ux-card__meta-value">{{ $ficha->tipoSanguineoLabel() }}</span>
                        </div>
                        <div class="ux-card__meta-row ux-card__meta-row--full">
                            <span class="ux-card__meta-label">Contato de emergência</span>
                            <span class="ux-card__meta-value">
                                @if($ficha->contato_emergencia_nome)
                                    {{ $ficha->contato_emergencia_nome }}
                                    @if($ficha->contato_emergencia_parentesco) ({{ $ficha->contato_emergencia_parentesco }})@endif
                                    — {{ $ficha->contato_emergencia_telefone }}
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        <a href="{{ route('saude.show', $aluno->id) }}" class="btn btn-xs btn-info" title="Ver ficha"><i class="fa fa-eye"></i></a>
                        @can('saude.edit')
                            <a href="{{ route('saude.edit', $aluno->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                        @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-notes-medical fa-2x mb-2 d-block"></i>
                    Nenhuma ficha de saúde cadastrada.
                    <br><small>Acesse o cadastro do aluno e use o botão "Ficha de Saúde".</small>
                </div>
            @endforelse
        </div>

        <div class="ux-table table-responsive">
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
