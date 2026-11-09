@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Boletim — '.$turma->nome" :subtitle="$turma->escola->nome ?? ''.' · '.($turma->anoLetivo->ano ?? '').' · '.($turma->etapa->nome ?? '')" />
@stop

@section('content')
@php $corteGeral = $disciplinas->avg(fn ($d) => $d->mediaAprovacaoNaTurma()); @endphp
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="font-weight-bold">Médias por disciplina ({{ $disciplinas->count() }}) · {{ $total_aulas }} aula(s) registrada(s)</span>
        <div>
            <a href="{{ route('boletim.turma.pdf', $turma->id) }}" class="btn btn-sm btn-danger" target="_blank"><i class="fa fa-file-pdf"></i> PDF</a>
            <a href="{{ route('boletim.index') }}" class="btn btn-sm btn-secondary">Voltar</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0" style="font-size:.85rem;">
                <thead class="thead-light">
                    <tr>
                        <th rowspan="2" class="align-middle" style="min-width:180px;">Aluno</th>
                        @foreach($disciplinas as $disc)
                            <th class="text-center" style="min-width:80px;" title="{{ $disc->nome }}">{{ \Illuminate\Support\Str::limit($disc->sigla ?: $disc->nome, 12) }}</th>
                        @endforeach
                        <th class="text-center align-middle" style="min-width:90px;">Média geral</th>
                        <th class="text-center align-middle" style="min-width:110px;">Frequência</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($matriculas as $matricula)
                        @php
                            $mediasAluno = [];
                            foreach ($disciplinas as $disc) {
                                $mediasAluno[$disc->id] = $medias["{$matricula->id}:{$disc->id}"] ?? null;
                            }
                            $freq = $frequencias->get($matricula->id);
                            $mediaGeral = count(array_filter($mediasAluno)) > 0
                                ? round(array_sum(array_filter($mediasAluno)) / count(array_filter($mediasAluno)), 1)
                                : null;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('boletim.aluno', $matricula->aluno_id) }}" class="font-weight-bold">{{ $matricula->aluno->pessoa->nome ?? '—' }}</a>
                                <small class="text-muted d-block">Nº {{ $matricula->numero_chamada ?? '—' }}</small>
                            </td>
                            @foreach($disciplinas as $disc)
                                <td class="text-center">
                                    @php
                                        $m = $mediasAluno[$disc->id];
                                        $corteDisc = $disc->mediaAprovacaoNaTurma();
                                    @endphp
                                    @if($m !== null)
                                        <span class="badge badge-{{ $m >= $corteDisc ? 'success' : 'danger' }}" title="Aprovação a partir de {{ number_format($corteDisc, 1, ',', '.') }}">{{ number_format($m, 1, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center font-weight-bold">
                                @if($mediaGeral !== null)
                                    <span class="badge badge-{{ $mediaGeral >= $corteGeral ? 'success' : 'danger' }}" title="Média geral — corte médio {{ number_format($corteGeral, 1, ',', '.') }}">{{ number_format($mediaGeral, 1, ',', '.') }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-center">
                                @if($freq && $freq['pct'] !== null)
                                    <span class="badge badge-{{ $freq['pct'] >= 75 ? 'success' : 'danger' }}">{{ $freq['pct'] }}%</span>
                                    <small class="text-muted d-block">{{ $freq['presente'] }}/{{ $total_aulas }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $disciplinas->count() + 3 }}" class="text-center text-muted py-4">Nenhum aluno ativo nesta turma.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
