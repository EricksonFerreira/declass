@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
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
                <a href="{{ route('portal.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        @foreach($disciplinas as $disciplina)
            @php $avals = $avaliacoes->get($disciplina->id, collect()); @endphp
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold">{{ $disciplina->nome }}</span>
                    @php $media = $medias[$disciplina->id] ?? null; @endphp
                    @if($media !== null)
                        <span class="badge badge-{{ $media >= 7 ? 'success' : ($media >= 5 ? 'warning' : 'danger') }}">Média {{ number_format($media, 1, ',', '.') }}</span>
                    @endif
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
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
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>
@stop
