@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($diario) ? 'Editar Aula' : 'Registrar Aula'" />
@stop

@section('content')
@if(! isset($diario) && request('chamada'))
    <div class="alert alert-info">
        <i class="fas fa-clipboard-check"></i>
        Nenhuma aula registrada hoje nesta turma. Registre a aula e a chamada abrirá automaticamente.
    </div>
@endif
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($diario) ? route('diario.update', $diario->id) : route('diario.store') }}">
            @csrf
            @if(isset($diario))
                @method('PUT')
            @elseif(request('chamada'))
                <input type="hidden" name="ir_para_chamada" value="1">
            @endif

            <div class="row">
                <div class="col-md-4">
                    @if(isset($diario))
                        <x-form-input name="turma_nome" label="Turma" :value="$diario->turma->nome ?? ''" disabled />
                    @elseif($turmaFixa ?? null)
                        <label class="form-label">Turma</label>
                        <div class="input-group input-group-sm">
                            <div class="form-control bg-light">
                                {{ $turmaFixa->nome }} — {{ $turmaFixa->escola->nome ?? '' }} — {{ $turmaFixa->anoLetivo->ano ?? '' }}
                            </div>
                            <div class="input-group-append">
                                <a class="btn btn-outline-secondary" title="Escolher outra turma"
                                    href="{{ route('diario.create', array_filter([
                                        'chamada' => request('chamada'),
                                        'data' => request('data'),
                                    ])) }}">
                                    <i class="fa fa-exchange-alt"></i> Trocar
                                </a>
                            </div>
                        </div>
                        <input type="hidden" name="turma_id" value="{{ $turmaFixa->id }}">
                        <small class="text-muted"><i class="fa fa-info-circle"></i> Turma definida pelo contexto</small>
                    @else
                        <x-form-select name="turma_id" label="Turma" :options="$turmas ?? []" :selected="old('turma_id', request('turma_id'))" placeholder="Selecionar" required />
                    @endif
                </div>
                <div class="col-md-4">
                    @if(isset($diario))
                        <x-form-input name="disciplina_nome" label="Disciplina" :value="$diario->disciplina->nome ?? ''" disabled />
                    @else
                        <x-form-select name="disciplina_id" label="Disciplina" :options="$disciplinas ?? []"
                            :selected="old('disciplina_id', request('disciplina_id'))"
                            :placeholder="($turmaFixa ?? null) ? 'Selecionar' : 'Selecione a turma primeiro'" required />
                    @endif
                </div>
                <div class="col-md-4">
                    <x-form-input name="data" type="date" label="Data da aula" :value="old('data', request('data') ?: (isset($diario) && $diario->data ? $diario->data->format('Y-m-d') : now()->format('Y-m-d')))" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Conteúdo ministrado <span class="text-danger">*</span></label>
                    <textarea name="conteudo" class="form-control" rows="6" required maxlength="10000">{{ old('conteudo', $diario->conteudo ?? '') }}</textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2" maxlength="2000">{{ old('observacoes', $diario->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('diario.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

@if(! isset($diario) && ! ($turmaFixa ?? null))
@push('js')
<script>
(function () {
    var selectTurma = document.querySelector('select[name="turma_id"]');
    var selectDisc = document.querySelector('select[name="disciplina_id"]');
    if (!selectTurma || !selectDisc) return;

    var urlDisciplinas = @json(route('diario.disciplinas'));
    var disciplinaAtual = @json((string) old('disciplina_id', request('disciplina_id')));

    function placeholderDisc(texto) {
        selectDisc.innerHTML = '';
        var op = document.createElement('option');
        op.value = '';
        op.textContent = texto;
        op.disabled = true;
        op.selected = true;
        selectDisc.appendChild(op);
    }

    function carregarDisciplinas(turmaId) {
        if (!turmaId) {
            placeholderDisc('Selecione a turma primeiro');
            return;
        }
        placeholderDisc('Carregando...');
        fetch(urlDisciplinas + '?turma_id=' + encodeURIComponent(turmaId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        }).then(function (map) {
            selectDisc.innerHTML = '';
            var op = document.createElement('option');
            op.value = '';
            op.textContent = 'Selecionar';
            op.disabled = true;
            selectDisc.appendChild(op);
            Object.keys(map).forEach(function (id) {
                var o = document.createElement('option');
                o.value = id;
                o.textContent = map[id];
                if (String(id) === String(disciplinaAtual)) o.selected = true;
                selectDisc.appendChild(o);
            });
        }).catch(function () {
            placeholderDisc('Erro ao carregar disciplinas');
        });
    }

    selectTurma.addEventListener('change', function () { carregarDisciplinas(this.value); });
    selectDisc.addEventListener('change', function () { disciplinaAtual = this.value; });

    // volta de validacao com turma preenchida: recarrega a lista dela
    if (selectTurma.value) carregarDisciplinas(selectTurma.value);
})();
</script>
@endpush
@endif
@stop
