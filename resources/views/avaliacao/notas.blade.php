@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Notas — '.$avaliacao->nome" :subtitle="$avaliacao->turma->nome.' · '.$avaliacao->disciplina->nome.' · máx. '.number_format($avaliacao->nota_maxima, 1, ',', '.')" />
@stop

@section('content')
<div class="card">
    <div class="card-header font-weight-bold">
        Alunos matriculados ({{ $matriculas->count() }})
        <small class="text-muted d-block font-weight-normal">
            {{ \App\Models\Avaliacao::TIPOS[$avaliacao->tipo] ?? $avaliacao->tipo }}
            @if($avaliacao->data) · {{ $avaliacao->data->format('d/m/Y') }} @endif
            · {{ $avaliacao->professor->nome ?? '' }}
        </small>
    </div>
    <div class="card-body p-0">
        <form method="POST" action="{{ route('avaliacao.notas.salvar', $avaliacao->id) }}">
            @csrf
            @method('PUT')
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:60px;">Nº</th>
                            <th>Aluno</th>
                            <th style="width:130px;">Nota (máx. {{ number_format($avaliacao->nota_maxima, 1, ',', '.') }})</th>
                            <th>Média na disciplina</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matriculas as $matricula)
                            @php $nota = $notas->get($matricula->id); @endphp
                            <tr>
                                <td class="text-muted">{{ $matricula->numero_chamada ?? '—' }}</td>
                                <td class="font-weight-bold">{{ $matricula->aluno->pessoa->nome ?? '—' }}</td>
                                <td>
                                    <input type="number" name="notas[{{ $loop->index }}][nota]" class="form-control form-control-sm"
                                        min="0" max="{{ $avaliacao->nota_maxima }}" step="0.1"
                                        placeholder="—" value="{{ old("notas.$loop->index.nota", $nota?->nota) }}">
                                    <input type="hidden" name="notas[{{ $loop->index }}][matricula_id]" value="{{ $matricula->id }}">
                                </td>
                                <td>
                                    @php $media = $medias[$matricula->id] ?? null; @endphp
                                    @if($media !== null)
                                        <span class="badge badge-secondary">{{ number_format($media, 1, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <input type="text" name="notas[{{ $loop->index }}][observacao]" class="form-control form-control-sm" maxlength="2000"
                                        placeholder="Opcional" value="{{ old("notas.$loop->index.observacao", $nota?->observacao ?? '') }}">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Nenhum aluno ativo nesta turma.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($matriculas->isNotEmpty())
                <div class="card-footer d-flex" style="gap:.5rem;">
                    <button type="submit" class="btn btn-primary">Salvar notas</button>
                    <a href="{{ route('avaliacao.index') }}" class="btn btn-secondary">Voltar</a>
                </div>
            @endif
        </form>
    </div>
</div>
@stop
