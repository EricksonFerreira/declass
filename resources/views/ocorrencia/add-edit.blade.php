@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($ocorrencia) ? 'Editar Ocorrência' : 'Nova Ocorrência'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($ocorrencia) ? route('ocorrencia.update', $ocorrencia->id) : route('ocorrencia.store') }}">
            @csrf
            @if(isset($ocorrencia))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    @if(isset($ocorrencia))
                        <x-form-input name="aluno_nome" label="Aluno" :value="$ocorrencia->aluno->pessoa->nome ?? ''" disabled />
                    @else
                        <x-form-select name="aluno_id" label="Aluno" :options="['' => 'Selecionar'] + ($alunos ?? [])" :selected="old('aluno_id')" required />
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
                <div class="col-md-3">
                    <x-form-select name="tipo" label="Tipo" :options="$tipos ?? []" :selected="old('tipo', $ocorrencia->tipo ?? '')" required />
                </div>
                <div class="col-md-3">
                    <x-form-select name="gravidade" label="Gravidade" :options="['' => '—'] + ($gravidades ?? [])" :selected="old('gravidade', $ocorrencia->gravidade ?? 'leve')" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $ocorrencia->ativo ?? 'S')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <x-form-input name="local" label="Local" :value="old('local', $ocorrencia->local ?? '')" maxlength="100" placeholder="Ex.: Pátio, sala de aula, laboratório" />
                </div>
            </div>

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

            <div class="row">
                <div class="col-md-12">
                    <div class="form-check">
                        <input type="checkbox" name="notifica_responsavel" value="1" class="form-check-input" id="notifica_resp"
                            {{ old('notifica_responsavel', $ocorrencia->notifica_responsavel ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notifica_resp">Notificar o responsável</label>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('ocorrencia.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
