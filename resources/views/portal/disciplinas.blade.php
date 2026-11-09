@extends('adminlte::page')
@section('title', $titulo)
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Disciplinas das turmas em que seus alunos estão matriculados" />
@stop

@section('content')
<x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />

<div class="row">
    @forelse($itens as $item)
        @php
            $disciplina = $item['disciplina'];
            $turma = $item['turma'];
            $matricula = $item['matricula'];
            $emAndamento = $item['emAndamento'];
            $podeAbrir = in_array($matricula->status, ['ativa', 'concluida'], true);
        @endphp
        @php $cor = \App\Services\TurmaGradeService::corDaDisciplina($disciplina->id); @endphp
        <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
            <div class="card card-outline h-100 position-relative"
                 style="border-top-color:{{ $cor['tx'] }};{{ $podeAbrir ? ' cursor:pointer;' : '' }}">
                @if($podeAbrir)
                    <a href="{{ route('portal.turma.disciplina', [$turma->id, $disciplina->id]) }}"
                       class="stretched-link" title="Abrir disciplina"></a>
                @endif
                <div class="card-header d-flex justify-content-between align-items-center py-1 px-2"
                     style="background:{{ $cor['bg'] }}; border-bottom:0">
                    <span class="font-weight-bold text-truncate" style="color:{{ $cor['tx'] }}; min-width:0" title="{{ $disciplina->nome }}">
                        {{ $disciplina->nome }}
                        @if($disciplina->sigla)
                            <span class="badge align-middle" style="background:{{ $cor['tx'] }}; color:{{ $cor['bg'] }}">{{ $disciplina->sigla }}</span>
                        @endif
                    </span>
                    <span class="badge badge-{{ $emAndamento ? 'success' : 'secondary' }} ml-1">
                        {{ $emAndamento ? 'Em andamento' : ($matricula->status === 'ativa' ? 'Encerrada' : (\App\Models\Matricula::STATUS[$matricula->status] ?? $matricula->status)) }}
                    </span>
                </div>
                <div class="card-body py-2 px-2">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-1">
                            <i class="fas fa-chalkboard-teacher fa-fw text-muted mr-1"></i>{{ $item['professor'] ?? 'Sem professor atribuído' }}
                        </li>
                        <li class="mb-1">
                            <i class="fas fa-users fa-fw text-muted mr-1"></i>{{ $turma->nome }}@if($turma->anoLetivo) — {{ $turma->anoLetivo->ano }}@endif
                            @if(($turma->anoLetivo?->status ?? 'letivo') !== 'letivo')
                                <span class="badge badge-dark">{{ \App\Models\AnoLetivo::STATUS[$turma->anoLetivo?->status ?? ''] ?? 'Encerrado' }}</span>
                            @endif
                        </li>
                        <li class="mb-1">
                            <i class="fas fa-user-graduate fa-fw text-muted mr-1"></i>{{ $matricula->aluno->pessoa->nome ?? $matricula->aluno->nome }}
                        </li>
                        @if($disciplina->pivot?->carga_horaria_semanal)
                            <li>
                                <i class="fas fa-clock fa-fw text-muted mr-1"></i>{{ $disciplina->pivot->carga_horaria_semanal }}h/semana
                            </li>
                        @endif
                    </ul>
                </div>
                @if(! $podeAbrir)
                    <div class="card-footer py-1 px-2 text-right">
                        <span class="text-muted small">Registro histórico</span>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info">Nenhuma disciplina encontrada para os filtros selecionados.</div>
        </div>
    @endforelse
</div>
@stop
