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
            <div class="card-footer">
                <a href="{{ route('boletim.aluno.pdf', array_merge(['aluno' => $aluno->id], $anoLetivoId ? ['ano_letivo_id' => $anoLetivoId] : [])) }}" class="btn btn-danger btn-sm" target="_blank"><i class="fa fa-file-pdf"></i> PDF</a>
                <a href="{{ route('boletim.turma', $turma->id) }}" class="btn btn-info btn-sm">Boletim da turma</a>
                <a href="{{ route('boletim.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        @foreach($disciplinas as $disciplina)
            @php $avals = $avaliacoes->get($disciplina->id, collect()); @endphp
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold">{{ $disciplina->nome }}</span>
                    <span>
                        @php $media = $medias[$disciplina->id] ?? null; @endphp
                        @if($media !== null)
                            @php $corte = $disciplina->mediaAprovacaoNaTurma(); @endphp
                            <span class="badge badge-{{ $media >= $corte ? 'success' : 'danger' }}" title="Aprovação a partir de {{ number_format($corte, 1, ',', '.') }}">Média {{ number_format($media, 1, ',', '.') }}</span>
                        @endif
                    </span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Avaliação</th>
                                <th>Tipo</th>
                                <th>Data</th>
                                <th class="text-center">Nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($avals as $av)
                                @php $nota = $av->notas->first()?->nota; @endphp
                                <tr>
                                    <td>{{ $av->nome }}</td>
                                    <td><span class="badge badge-info">{{ \App\Models\Avaliacao::TIPOS[$av->tipo] ?? $av->tipo }}</span></td>
                                    <td>{{ $av->data?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="text-center font-weight-bold">
                                        {{ $nota !== null ? number_format($nota, 1, ',', '.').' / '.number_format($av->nota_maxima, 1, ',', '.') : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Nenhuma avaliação registrada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">Ocorrências disciplinares</span>
                <span>
                    @foreach(\App\Models\Ocorrencia::TIPOS as $tipo => $label)
                        @if(($ocorrencias_resumo[$tipo] ?? 0) > 0)
                            <span class="badge badge-light border mr-1">{{ $label }}: {{ $ocorrencias_resumo[$tipo] }}</span>
                        @endif
                    @endforeach
                    @if($ocorrencias->isEmpty())
                        <span class="text-muted small">Nenhuma no período</span>
                    @endif
                </span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Gravidade</th>
                            <th>Descrição</th>
                            <th>Medida adotada</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ocorrencias as $oc)
                            @php
                                $corGrav = ['leve' => 'success', 'media' => 'warning', 'grave' => 'danger'][$oc->gravidade] ?? 'secondary';
                            @endphp
                            <tr>
                                <td class="text-nowrap">{{ $oc->data?->format('d/m/Y') }}</td>
                                <td><span class="badge badge-info">{{ $oc->tipoLabel() }}</span></td>
                                <td><span class="badge badge-{{ $corGrav }}">{{ $oc->gravidadeLabel() }}</span></td>
                                <td><small>{{ \Illuminate\Support\Str::limit($oc->descricao, 120) }}</small></td>
                                <td><small class="text-muted">{{ \Illuminate\Support\Str::limit($oc->medida_adotada, 80) ?: '—' }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Nenhuma ocorrência registrada no período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
