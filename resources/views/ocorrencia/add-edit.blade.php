@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($ocorrencia) ? 'Editar Ocorrência' : 'Nova Ocorrência'" />
@stop

@push('css')
<style>
    .frm-sec { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; margin: 1.1rem 0 .6rem; }
    .frm-sec:first-child { margin-top: 0; }
    .frm-sec i { margin-right: .3rem; }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($ocorrencia) ? route('ocorrencia.update', $ocorrencia->id) : route('ocorrencia.store') }}">
            @csrf
            @if(isset($ocorrencia))
                @method('PUT')
            @endif

            <div class="frm-sec"><i class="fa fa-user-graduate"></i>Identificação</div>
            <div class="row">
                <div class="col-md-6">
                    @if(isset($ocorrencia))
                        <x-form-input name="aluno_nome" label="Aluno" :value="$ocorrencia->aluno->pessoa->nome ?? ''" disabled />
                    @else
                        @php($alunoSel = collect($alunosPicker ?? [])->firstWhere('id', (int) old('aluno_id')))
                        <label class="form-label" for="aluno_display">Aluno <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" id="aluno_display" class="form-control @error('aluno_id') is-invalid @enderror"
                                placeholder="Selecionar aluno..." value="{{ $alunoSel['rotulo'] ?? '' }}" readonly tabindex="-1">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary" id="btnBuscarAluno"><i class="fa fa-search mr-1"></i>Buscar</button>
                            </div>
                        </div>
                        <input type="hidden" name="aluno_id" id="aluno_id" value="{{ old('aluno_id') }}">
                        @error('aluno_id')<small class="text-danger">{{ $message }}</small>@enderror
                    @endif
                </div>
                <div class="col-md-6">
                    @if(isset($ocorrencia))
                        <x-form-input name="escola_nome" label="Escola" :value="$ocorrencia->escola->nome ?? ''" disabled />
                    @else
                        <x-form-select name="escola_id" label="Escola" :options="['' => 'Selecionar'] + ($escolas ?? [])" :selected="old('escola_id')" required />
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <x-form-input name="data" type="date" label="Data" :value="old('data', isset($ocorrencia) ? $ocorrencia->data->format('Y-m-d') : now()->toDateString())" required />
                </div>
                <div class="col-md-9">
                    <x-form-input name="local" label="Local" :value="old('local', $ocorrencia->local ?? '')" maxlength="100" placeholder="Ex.: Pátio, sala de aula, laboratório" />
                </div>
            </div>

            <div class="frm-sec"><i class="fa fa-tags"></i>Classificação</div>
            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="tipo" label="Tipo" :options="$tipos ?? []" :selected="old('tipo', $ocorrencia->tipo ?? '')" required />
                </div>
                <div class="col-md-4" id="wrap-gravidade">
                    <x-form-select name="gravidade" label="Gravidade" :options="['' => '—'] + ($gravidades ?? [])" :selected="old('gravidade', $ocorrencia->gravidade ?? 'leve')" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $ocorrencia->ativo ?? 'S')" />
                </div>
            </div>

            <div class="frm-sec"><i class="fa fa-align-left"></i>Detalhes</div>
            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Descrição <span class="text-danger">*</span></label>
                    <textarea name="descricao" class="form-control" rows="4" required maxlength="4000"
                        placeholder="Descreva o ocorrido com detalhes: horário, envolvidos, circunstâncias.">{{ old('descricao', $ocorrencia->descricao ?? '') }}</textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Medida adotada</label>
                    <textarea name="medida_adotada" class="form-control" rows="2" maxlength="2000"
                        placeholder="Ex.: Advertência verbal, convocação dos pais, afastamento das atividades.">{{ old('medida_adotada', $ocorrencia->medida_adotada ?? '') }}</textarea>
                </div>
            </div>

            <div class="frm-sec"><i class="fa fa-bell"></i>Notificação</div>
            <div class="border rounded p-3" style="background:#f8fafc;">
                <div class="d-flex align-items-center" style="gap:.75rem;">
                    <i class="fa fa-bell text-info fa-lg"></i>
                    <div class="form-check mb-0">
                        <input type="hidden" name="notifica_responsavel" value="0">
                        <input type="checkbox" name="notifica_responsavel" value="1" class="form-check-input" id="notifica_resp"
                            {{ old('notifica_responsavel', $ocorrencia->notifica_responsavel ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label font-weight-bold" for="notifica_resp">Notificar o responsável</label>
                    </div>
                </div>
                <small class="text-muted d-block mt-1" style="margin-left:2rem;">O responsável recebe um aviso sobre este registro.</small>
            </div>

            <div class="mt-3">
                <a href="{{ route('ocorrencia.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

@if(!isset($ocorrencia))
    @include('partials.entity-picker')
@endif
@stop

@push('js')
<script>
(function () {
    // Elogio nao tem gravidade: esconde o campo e grava 'leve'
    var tipo = document.querySelector('select[name="tipo"]');
    var wrapGrav = document.getElementById('wrap-gravidade');
    if (tipo && wrapGrav) {
        var selGrav = wrapGrav.querySelector('select');
        function sincronizarTipo() {
            var elogio = tipo.value === 'elogio';
            wrapGrav.style.display = elogio ? 'none' : '';
            if (elogio && selGrav) selGrav.value = 'leve';
        }
        tipo.addEventListener('change', sincronizarTipo);
        sincronizarTipo();
    }

    var input = document.getElementById('aluno_id');
    if (!input || !window.EntityPicker) return;
    var display = document.getElementById('aluno_display');
    var escola = document.querySelector('select[name="escola_id"]');

    EntityPicker.registrar('aluno', {
        titulo: 'Buscar aluno',
        dados: @json($alunosPicker ?? []),
        filtros: [{campo: 'escola_id', campoRotulo: 'escola', label: 'Escola'}],
        onSelect: function (d) {
            input.value = d.id;
            display.value = d.rotulo;
            display.classList.remove('is-invalid');
            // preenche a escola do aluno selecionado
            if (escola && d.escola_id) escola.value = String(d.escola_id);
        },
    });
    document.getElementById('btnBuscarAluno').addEventListener('click', function () {
        EntityPicker.abrir('aluno');
    });
    input.closest('form').addEventListener('submit', function (e) {
        if (!input.value) {
            e.preventDefault();
            display.classList.add('is-invalid');
            EntityPicker.abrir('aluno');
        }
    });
})();
</script>
@endpush
