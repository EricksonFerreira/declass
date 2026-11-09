@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Notas — '.$avaliacao->nome" :subtitle="$avaliacao->turma->nome.' · '.$avaliacao->disciplina->nome.' · máx. '.number_format($avaliacao->nota_maxima, 1, ',', '.')" />
@stop

@push('css')
<style>
    .nt-head { background:linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff; border-radius:.3rem .3rem 0 0; padding:.9rem 1.1rem; }
    .nt-head h5 { margin:0; font-weight:700; }
    .nt-chip { display:inline-flex; align-items:center; gap:.3rem; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.25); color:#fff; border-radius:999px; padding:.15rem .6rem; font-size:.74rem; margin:.15rem .25rem .15rem 0; }
    .nt-count { border:1px solid #eef1f6; border-radius:8px; padding:.35rem .7rem; background:#fff; font-size:.8rem; }
    .nt-count b { display:block; font-size:1.05rem; color:#1e3a8a; }
    .nt-count label { font-size:.62rem; text-transform:uppercase; color:#9ca3af; margin:0; font-weight:700; }
    .nt-table thead th { font-size:.68rem; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; position:sticky; top:0; background:#f8f9fa; z-index:2; }
    .nt-table tbody tr:hover { background:#f4f7ff; }
    .nt-nota { width:110px; text-align:center; font-weight:600; }
    .nt-num { width:34px; height:34px; border-radius:50%; background:#eef2ff; color:#2563eb; font-weight:700; font-size:.8rem; display:inline-flex; align-items:center; justify-content:center; }
</style>
@endpush

@section('content')
<div class="card">
    <div class="nt-head d-flex flex-wrap align-items-center">
        <div class="flex-grow-1">
            <h5>{{ $avaliacao->nome }}</h5>
            <div class="mt-1">
                <span class="nt-chip"><i class="fa fa-tag"></i> {{ \App\Models\Avaliacao::TIPOS[$avaliacao->tipo] ?? $avaliacao->tipo }}</span>
                <span class="nt-chip"><i class="fa fa-users"></i> {{ $avaliacao->turma->nome }}</span>
                <span class="nt-chip"><i class="fa fa-book"></i> {{ $avaliacao->disciplina->nome }}</span>
                @if($avaliacao->data)<span class="nt-chip"><i class="fa fa-calendar"></i> {{ $avaliacao->data->format('d/m/Y') }}</span>@endif
                @if($avaliacao->periodo)<span class="nt-chip"><i class="fa fa-flag"></i> {{ $avaliacao->periodo }}</span>@endif
                <span class="nt-chip"><i class="fa fa-user"></i> {{ $avaliacao->professor->nome ?? '' }}</span>
                <span class="nt-chip"><i class="fa fa-star"></i> Nota máx. {{ number_format($avaliacao->nota_maxima, 1, ',', '.') }}</span>
            </div>
        </div>
        <div class="d-flex" style="gap:.4rem;">
            <div class="nt-count"><b>{{ $matriculas->count() }}</b><label>Alunos</label></div>
            <div class="nt-count"><b>{{ $notas->filter(fn ($n) => $n->nota !== null)->count() }}</b><label>Lançadas</label></div>
            <div class="nt-count"><b>{{ $matriculas->count() - $notas->filter(fn ($n) => $n->nota !== null)->count() }}</b><label>Pendentes</label></div>
        </div>
    </div>

    <div class="card-body p-0">
        <form method="POST" action="{{ route('avaliacao.notas.salvar', $avaliacao->id) }}">
            @csrf
            @method('PUT')
            <div class="table-responsive">
                <table class="table table-sm mb-0 nt-table">
                    <thead>
                        <tr>
                            <th style="width:60px;" class="text-center">Nº</th>
                            <th>Aluno</th>
                            <th style="width:140px;">Nota (máx. {{ number_format($avaliacao->nota_maxima, 1, ',', '.') }})</th>
                            <th>Média na disciplina</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matriculas as $matricula)
                            @php $nota = $notas->get($matricula->id); @endphp
                            <tr>
                                <td class="text-center align-middle">
                                    <span class="nt-num">{{ $matricula->numero_chamada ?? '—' }}</span>
                                </td>
                                <td class="align-middle font-weight-bold">{{ $matricula->aluno->pessoa->nome ?? '—' }}</td>
                                <td class="align-middle">
                                    <input type="number" name="notas[{{ $loop->index }}][nota]" class="form-control form-control-sm nt-nota"
                                        min="0" max="{{ $avaliacao->nota_maxima }}" step="0.1"
                                        placeholder="—" value="{{ old("notas.$loop->index.nota", $nota?->nota) }}">
                                    <input type="hidden" name="notas[{{ $loop->index }}][matricula_id]" value="{{ $matricula->id }}">
                                </td>
                                <td class="align-middle">
                                    @php $media = $medias[$matricula->id] ?? null; @endphp
                                    @if($media !== null)
                                        <span class="badge badge-secondary">{{ number_format($media, 1, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="align-middle">
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
                <div class="card-footer d-flex" style="gap:.5rem; position:sticky; bottom:0; background:#fff; border-top:1px solid #eef1f6;">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check mr-1"></i> Salvar notas</button>
                    <a href="{{ route('avaliacao.index') }}" class="btn btn-secondary">Voltar</a>
                </div>
            @endif
        </form>
    </div>
</div>
@stop
