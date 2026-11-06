@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="($ficha ? 'Editar' : 'Nova').' Ficha de Saúde — '.($aluno->pessoa->nome ?? 'Aluno')" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="alert alert-info py-2">
            <i class="fa fa-info-circle"></i>
            Dados sensíveis: alergias, medicamentos e condições crônicas ficam visíveis para a equipe pedagógica.
        </div>

        <form method="POST" action="{{ route('saude.update', $aluno->id) }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-3">
                    <x-form-select name="tipo_sanguineo" label="Tipo sanguíneo" :options="['' => '—'] + ($tiposSanguineos ?? [])" :selected="old('tipo_sanguineo', $ficha->tipo_sanguineo ?? '')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Alergias</label>
                    <textarea name="alergias" class="form-control" rows="3" maxlength="4000"
                        placeholder="Ex.: amendoim, poeira, medicamentos...">{{ old('alergias', $ficha->alergias ?? '') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Medicamentos em uso</label>
                    <textarea name="medicamentos" class="form-control" rows="3" maxlength="4000"
                        placeholder="Ex.: bombinha de asma, insulina...">{{ old('medicamentos', $ficha->medicamentos ?? '') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Condições crônicas</label>
                    <textarea name="condicoes_cronicas" class="form-control" rows="3" maxlength="4000"
                        placeholder="Ex.: asma, diabetes, epilepsia, TEA...">{{ old('condicoes_cronicas', $ficha->condicoes_cronicas ?? '') }}</textarea>
                </div>
            </div>

            <hr>
            <h6 class="font-weight-bold">Contato de emergência</h6>
            <div class="row">
                <div class="col-md-5">
                    <x-form-input name="contato_emergencia_nome" label="Nome" :value="old('contato_emergencia_nome', $ficha->contato_emergencia_nome ?? '')" maxlength="190" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="contato_emergencia_parentesco" label="Parentesco" :value="old('contato_emergencia_parentesco', $ficha->contato_emergencia_parentesco ?? '')" maxlength="60" placeholder="Ex.: mãe, tio" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="contato_emergencia_telefone" label="Telefone" :value="old('contato_emergencia_telefone', $ficha->contato_emergencia_telefone ?? '')" maxlength="30" placeholder="(00) 00000-0000" />
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2" maxlength="4000"
                        placeholder="Informações complementares para a equipe...">{{ old('observacoes', $ficha->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ $ficha ? route('saude.show', $aluno->id) : route('saude.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar ficha</button>
            </div>
        </form>
    </div>
</div>
@stop
