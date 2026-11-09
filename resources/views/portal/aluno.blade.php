@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$aluno->pessoa->nome ?? 'Aluno'" :subtitle="'Boletim — '.$turma->nome.' · '.($turma->anoLetivo->ano ?? '')" />
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header font-weight-bold">Resumo</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Turma</dt><dd class="col-sm-7">{{ $turma->nome }}</dd>
                    <dt class="col-sm-5">Escola</dt><dd class="col-sm-7">{{ $turma->escola->nome ?? '—' }}</dd>
                    <dt class="col-sm-5">Ano letivo</dt><dd class="col-sm-7">{{ $turma->anoLetivo->ano ?? '—' }}</dd>
                    <dt class="col-sm-5">Etapa</dt><dd class="col-sm-7">{{ $turma->etapa->nome ?? '—' }}</dd>
                    <dt class="col-sm-5">Nº chamada</dt><dd class="col-sm-7">{{ $matricula->numero_chamada ?? '—' }}</dd>
                    <dt class="col-sm-5">Aulas registradas</dt><dd class="col-sm-7">{{ $total_aulas }}</dd>
                    <dt class="col-sm-5">Frequência</dt>
                    <dd class="col-sm-7">
                        @if($frequencia['pct'] !== null)
                            <span class="badge badge-{{ $frequencia['pct'] >= 75 ? 'success' : 'danger' }}">{{ $frequencia['pct'] }}%</span>
                            <small class="text-muted">({{ $frequencia['presente'] }}P · {{ $frequencia['justificada'] }}J · {{ $frequencia['falta'] }}F)</small>
                        @else
                            —
                        @endif
                    </dd>
                </dl>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('portal.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
                <a href="{{ route('portal.aluno.pdf', ['aluno' => $aluno->id, 'ano_letivo_id' => $turma->ano_letivo_id]) }}"
                   class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-file-pdf mr-1"></i>Baixar boletim em PDF
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        @foreach($disciplinas as $disciplina)
            @php
                $avals = $avaliacoes->get($disciplina->id, collect());
                $collapseId = 'bol-disc-'.$disciplina->id;
            @endphp
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center collapse-toggle"
                     data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="false">
                    <span class="font-weight-bold">
                        <i class="fas fa-book-open mr-1 text-primary"></i>{{ $disciplina->nome }}
                        <small class="text-muted font-weight-normal">· {{ $avals->count() }} {{ $avals->count() === 1 ? 'avaliação' : 'avaliações' }}</small>
                    </span>
                    <div class="d-flex align-items-center" style="gap:.5rem;">
                        @php $media = $medias[$disciplina->id] ?? null; @endphp
                        @if($media !== null)
                            <span class="badge badge-{{ $media >= 7 ? 'success' : 'danger' }}">Média {{ number_format($media, 1, ',', '.') }}</span>
                        @endif
                        <i class="fas fa-chevron-down collapse-icon text-muted"></i>
                    </div>
                </div>
                <div id="{{ $collapseId }}" class="collapse">
                    <div class="card-body p-0">
                        <div class="table-responsive"><table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Avaliação</th>
                                    <th>Tipo</th>
                                    <th>Data</th>
                                    <th>Nota</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($avals as $av)
                                    @php $nota = $av->notas->first()?->nota; @endphp
                                    <tr>
                                        <td>{{ $av->nome }}</td>
                                        <td><span class="badge badge-info">{{ \App\Models\Avaliacao::TIPOS[$av->tipo] ?? $av->tipo }}</span></td>
                                        <td>{{ $av->data?->format('d/m/Y') ?? '—' }}</td>
                                        <td>
                                            {{ $nota !== null ? number_format($nota, 1, ',', '.').' / '.number_format($av->nota_maxima, 1, ',', '.') : '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Sem avaliações registradas.</td></tr>
                                @endforelse
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
    .collapse-toggle { cursor: pointer; }
    .collapse-toggle .collapse-icon { transition: transform .2s ease; display: inline-block; }
    .collapse-toggle[aria-expanded="true"] .collapse-icon { transform: rotate(180deg); }
</style>
@stop
