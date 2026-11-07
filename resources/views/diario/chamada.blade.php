@extends('adminlte::page')

@section('title', $titulo)

@php $isProfessor = auth()->user()->isProfessor(); @endphp

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Chamada — '.\Carbon\Carbon::parse($data)->format('d/m/Y')" :subtitle="$turma->nome.($disciplina ? ' · '.$disciplina->nome : '')" />
@stop

@section('content')
@php
    $dataAtual = old('data', $data);
    $qtdDisciplinas = $disciplinas->count();
    $disciplinaAtualId = $disciplina?->id;
    $disciplinaNome = $disciplina?->nome ?? $disciplinas->first()?->nome;
@endphp

{{-- Contexto da chamada: turma + disciplina + data. Mudancas recarregam via GET. --}}
<div class="card chamada-ctx">
    <div class="card-body">
        <form method="GET" action="{{ route('diario.chamada') }}" id="form-contexto-chamada">
            <input type="hidden" name="turma_id" value="{{ $turma->id }}">
            <div class="ctx-grid">
                <div class="ctx-item">
                    <label>Turma</label>
                    <div class="ctx-chip" title="{{ $turma->nome }}"><i class="fas fa-chalkboard-teacher"></i><span class="ctx-truncate">{{ $turma->nome }}</span></div>
                </div>
                <div class="ctx-item">
                    <label>Disciplina</label>
                    @if($qtdDisciplinas > 1)
                        <select name="disciplina_id" class="form-control" onchange="this.form.submit()">
                            <option value="" disabled {{ $disciplinaAtualId ? '' : 'selected' }}>Selecione a disciplina...</option>
                            @foreach($disciplinas as $d)
                                <option value="{{ $d->id }}" {{ $disciplinaAtualId === $d->id ? 'selected' : '' }}>{{ $d->nome }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="ctx-chip ctx-chip-primary"><i class="fas fa-book-open"></i><span class="ctx-truncate">{{ $disciplinaNome ?? '—' }}</span></div>
                    @endif
                </div>
                <div class="ctx-item">
                    <label>Data da aula</label>
                    <input type="date" name="data" class="form-control" value="{{ $dataAtual }}" onchange="this.form.submit()">
                </div>
            </div>
            <noscript><button type="submit" class="btn btn-sm btn-outline-primary mt-2">Aplicar</button></noscript>
        </form>
    </div>
</div>

@if(!$disciplina)
    <div class="card chamada-vazio">
        <div class="card-body text-center py-5">
            <div class="chamada-vazio-icone"><i class="fas fa-book-open"></i></div>
            @if($qtdDisciplinas > 1)
                <div class="font-weight-bold">Escolha a disciplina</div>
                <div class="text-muted small">Selecione a disciplina acima para listar os alunos e fazer a chamada.</div>
            @else
                <div class="font-weight-bold">Sem disciplina para chamada</div>
                <div class="text-muted small">Esta turma não tem disciplina disponível para você neste momento.</div>
            @endif
        </div>
    </div>
@else
    @unless($diario)
        <div class="alert alert-info chamada-aviso d-flex align-items-center">
            <i class="fas fa-info-circle mr-2"></i>
            <span>Aula ainda não registrada nesta data — ela será <strong>criada automaticamente</strong> ao salvar a chamada.</span>
        </div>
    @endunless

    <form method="POST" action="{{ route('diario.chamada.salvar') }}">
        @csrf
        <input type="hidden" name="turma_id" value="{{ $turma->id }}">
        <input type="hidden" name="disciplina_id" value="{{ $disciplina->id }}">
        <input type="hidden" name="data" value="{{ $dataAtual }}">

        <div class="card chamada-card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:.4rem;">
                <span class="font-weight-bold"><i class="fas fa-user-check text-primary mr-1"></i> Presença</span>
                <span class="text-muted small">{{ $matriculas->count() }} {{ $matriculas->count() === 1 ? 'aluno matriculado' : 'alunos matriculados' }}</span>
            </div>
            <div class="card-body p-0">
                @include('diario.partials.tabela-chamada')

                <div class="chamada-meta">
                    <div class="chamada-meta-titulo"><i class="fas fa-pen"></i> Sobre a aula</div>
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label class="chamada-label">Conteúdo ministrado <span class="text-danger">*</span></label>
                            <textarea name="conteudo" class="form-control" rows="3" required maxlength="10000" placeholder="Ex.: Equações do 2º grau — resolução e exercícios">{{ old('conteudo', $diario->conteudo ?? '') }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="chamada-label">Observações</label>
                            <textarea name="observacoes" class="form-control" rows="2" maxlength="2000" placeholder="Ocorrências, avisos ou detalhes da aula (opcional)">{{ old('observacoes', $diario->observacoes ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer chamada-actions">
                <button type="submit" class="btn btn-primary"><i class="fa fa-clipboard-check mr-1"></i> Salvar chamada</button>
                <a href="{{ route('turma.show', $turma->id).'#tab-registros' }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </div>
    </form>
@endif

<style>
    /* Contexto (turma / disciplina / data) */
    .chamada-ctx .card-body { padding: .65rem .9rem; }
    .ctx-grid { display: grid; grid-template-columns: minmax(0,1.4fr) minmax(0,1.2fr) minmax(150px,.7fr); gap: .6rem; align-items: end; }
    .ctx-item > label { display: block; font-size: .68rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #8a94a1; margin-bottom: .2rem; }
    .ctx-chip { display: flex; align-items: center; gap: .5rem; min-height: 38px; padding: .45rem .7rem; background: #f4f6f9; border: 1px solid #e3e8ee; border-radius: .45rem; font-size: .88rem; font-weight: 600; color: #39434f; }
    .ctx-chip i { color: #98a2ae; font-size: .82rem; flex-shrink: 0; }
    .ctx-chip-primary { background: #eaf1fd; border-color: #cddffa; color: #174ea6; }
    .ctx-chip-primary i { color: #2e6fda; }
    .ctx-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ctx-item .form-control { min-height: 38px; padding: .45rem .6rem; font-size: .88rem; border-radius: .45rem; }

    /* Aviso / estado vazio */
    .chamada-aviso { font-size: .85rem; }
    .chamada-vazio-icone { width: 56px; height: 56px; margin: 0 auto .75rem; border-radius: 50%; background: #eaf1fd; color: #2e6fda; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }

    /* Card principal */
    .chamada-card .card-header { padding: .6rem .9rem; background: #fff; }
    .chamada-meta { padding: 1rem .9rem .4rem; border-top: 1px solid #eef1f5; margin-top: .5rem; }
    .chamada-meta-titulo { font-size: .8rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #8a94a1; margin-bottom: .6rem; }
    .chamada-meta-titulo i { margin-right: .35rem; }
    .chamada-label { font-size: .82rem; font-weight: 600; color: #495057; margin-bottom: .25rem; }
    .chamada-actions { display: flex; gap: .5rem; padding: .75rem .9rem; background: #fbfcfd; }
    .chamada-actions .btn { font-weight: 600; }

    /* Mobile: campos empilham e botoes ocupam a largura */
    @media (max-width: 576px) {
        .ctx-grid { grid-template-columns: 1fr; gap: .5rem; }
        .chamada-actions { flex-direction: column; }
        .chamada-actions .btn { width: 100%; }
        .chamada-actions .btn-outline-secondary { order: 2; }
    }
</style>

@once
<script>
document.addEventListener('DOMContentLoaded', function () {
    var contexto = document.getElementById('form-contexto-chamada');
    var sujo = false;
    document.querySelectorAll('.freq-check, input[name^="frequencias"], textarea[name="conteudo"], textarea[name="observacoes"]').forEach(function (el) {
        el.addEventListener('change', function () { sujo = true; });
        el.addEventListener('input', function () { sujo = true; });
    });
    if (contexto) {
        contexto.addEventListener('submit', function (e) {
            if (sujo && !confirm('Trocar disciplina ou data recarrega a tela e descarta o que já foi marcado. Continuar?')) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endonce
@stop
