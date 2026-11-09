@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Médias por disciplina e frequência" />
@stop

@push('css')
<style>
    .bol-card { border: none; border-radius: 14px; box-shadow: 0 1px 6px rgba(11,18,32,.07); height: 100%; }
    .bol-card .card-body { padding: 1.4rem 1.4rem 1.2rem; }
    .bol-ico {
        width: 54px; height: 54px; border-radius: 14px; display: flex;
        align-items: center; justify-content: center; font-size: 1.35rem;
        color: #fff; margin-bottom: .9rem; box-shadow: 0 4px 12px rgba(0,0,0,.15);
    }
    .bol-ico--turma { background: linear-gradient(135deg,#2563eb,#0ea5e9); }
    .bol-ico--aluno { background: linear-gradient(135deg,#7c3aed,#a855f7); }
    .bol-tit { font-size: 1.02rem; font-weight: 800; color: #1f2937; }
    .bol-sub { font-size: .8rem; color: #64748b; margin-bottom: 1rem; }
    .bol-sel { display: flex; align-items: center; gap: .6rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; padding: .65rem .8rem; margin-bottom: .8rem; cursor: pointer; transition: border-color .15s, background .15s; }
    .bol-sel:hover { border-color: #2563eb; background: #eff6ff; }
    .bol-sel i { color: #94a3b8; font-size: 1.05rem; }
    .bol-sel .txt { font-size: .85rem; color: #64748b; }
    .bol-sel .txt strong { color: #1f2937; display: block; font-size: .88rem; }
    .bol-hint { font-size: .7rem; color: #94a3b8; }
    .bol-hint i { margin-right: 3px; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card bol-card">
            <div class="card-body">
                <div class="bol-ico bol-ico--turma"><i class="fas fa-users"></i></div>
                <div class="bol-tit">Boletim por turma</div>
                <div class="bol-sub">Grade completa: todos os alunos da turma × disciplinas, com médias e frequência.</div>
                <div class="bol-sel" id="selTurmaBox">
                    <i class="fas fa-search"></i>
                    <div class="txt"><strong>Buscar turma</strong>filtre por escola e ano letivo</div>
                </div>
                <div class="bol-hint"><i class="fas fa-info-circle"></i>Depois de escolher a turma, o boletim da grade abre direto.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card bol-card">
            <div class="card-body">
                <div class="bol-ico bol-ico--aluno"><i class="fas fa-user-graduate"></i></div>
                <div class="bol-tit">Boletim individual</div>
                <div class="bol-sub">Boletim detalhado de um aluno: notas, médias e frequência por disciplina.</div>
                <div class="bol-sel" id="selAlunoBox">
                    <i class="fas fa-search"></i>
                    <div class="txt"><strong>Buscar aluno</strong>filtre por escola e turma</div>
                </div>
                <div class="bol-hint"><i class="fas fa-info-circle"></i>Somente alunos com matrícula ativa aparecem na busca.</div>
            </div>
        </div>
    </div>
</div>

@include('partials.entity-picker')
@stop

@push('js')
<script>
$(function () {
    if (!window.EntityPicker) return;

    EntityPicker.registrar('turma', {
        titulo: 'Buscar turma',
        dados: @json($turmasPicker ?? []),
        filtros: [
            {campo: 'escola_id', campoRotulo: 'escola', label: 'Escola'},
            {campo: 'ano_letivo_id', campoRotulo: 'ano', label: 'Ano letivo'},
        ],
        onSelect: function (d) {
            window.location.href = "{{ route('boletim.turma', ['turma' => '__ID__']) }}".replace('__ID__', d.id);
        },
    });

    EntityPicker.registrar('aluno', {
        titulo: 'Buscar aluno',
        dados: @json($alunosPicker ?? []),
        filtros: [
            {campo: 'escola_id', campoRotulo: 'escola', label: 'Escola'},
            {campo: 'turma_id', campoRotulo: 'turma', label: 'Turma'},
        ],
        onSelect: function (d) {
            window.location.href = "{{ route('boletim.aluno', ['aluno' => '__ID__']) }}".replace('__ID__', d.id);
        },
    });

    document.getElementById('selTurmaBox').addEventListener('click', function () {
        EntityPicker.abrir('turma');
    });
    document.getElementById('selAlunoBox').addEventListener('click', function () {
        EntityPicker.abrir('aluno');
    });
});
</script>
@endpush
