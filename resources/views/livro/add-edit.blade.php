@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($livro) ? 'Editar Livro' : 'Novo Livro'" />
@stop

@push('css')
<style>
    .frm-sec { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; margin: 1.1rem 0 .6rem; }
    .frm-sec:first-child { margin-top: 0; }
    .frm-sec i { margin-right: .3rem; }
    .liv-capa-preview { width: 84px; height: 116px; border-radius: 8px; object-fit: cover; border: 1px solid #e5e9f0; }
    .liv-capa-drop { border: 1px dashed #cbd5e1; border-radius: 10px; padding: .8rem; background: #fbfcfe; }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($livro) ? route('livro.update', $livro->id) : route('livro.store') }}" enctype="multipart/form-data">
            @csrf
            @if(isset($livro))
                @method('PUT')
            @endif

            <div class="frm-sec"><i class="fa fa-book"></i>Identificação da obra</div>
            <div class="row">
                <div class="col-md-7">
                    <x-form-input name="titulo" label="Título" :value="old('titulo', $livro->titulo ?? '')" required maxlength="180" placeholder="Ex.: Dom Casmurro" />
                </div>
                <div class="col-md-5">
                    <x-form-input name="autor" label="Autor" :value="old('autor', $livro->autor ?? '')" maxlength="140" placeholder="Ex.: Machado de Assis" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="editora" label="Editora" :value="old('editora', $livro->editora ?? '')" maxlength="140" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="isbn" label="ISBN" :value="old('isbn', $livro->isbn ?? '')" maxlength="20" placeholder="978-85-359-0277-5" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="ano_publicacao" type="number" label="Ano de publicação" :value="old('ano_publicacao', $livro->ano_publicacao ?? '')" min="1400" max="2100" />
                </div>
            </div>

            <div class="frm-sec"><i class="fa fa-archive"></i>Acervo</div>
            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="escola_id" label="Escola" :options="$escolas ?? []" :selected="old('escola_id', $livro->escola_id ?? '')" required />
                </div>
                <div class="col-md-4">
                    <x-form-select name="categoria" label="Categoria" :options="['' => 'Selecionar'] + ($categorias ?? [])" :selected="old('categoria', $livro->categoria ?? 'outro')" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="localizacao" label="Localização na biblioteca" :value="old('localizacao', $livro->localizacao ?? '')" maxlength="60" placeholder="Ex.: Estante A3 · Prateleira 2" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <x-form-input name="exemplares" type="number" label="Exemplares" :value="old('exemplares', $livro->exemplares ?? 1)" required min="1" max="500" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $livro->ativo ?? 'S')" />
                </div>
            </div>

            <div class="frm-sec"><i class="fa fa-image"></i>Capa do livro</div>
            <div class="row">
                <div class="col-md-12">
                    <div class="liv-capa-drop d-flex align-items-center" style="gap:1rem;">
                        <img id="capaPreview" src="{{ !empty($livro) ? ($livro->capaUrl() ?? '') : '' }}" alt=""
                            class="liv-capa-preview" style="{{ (!empty($livro) && $livro->capaUrl()) ? '' : 'display:none;' }}">
                        <div class="flex-grow-1">
                            <input type="file" name="capa" id="capaInput" class="form-control-file" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted d-block mt-1">JPG, PNG ou WEBP — máx. 2 MB. A foto aparece nos cards e no detalhe do livro.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="frm-sec"><i class="fa fa-align-left"></i>Sinopse</div>
            <div class="row">
                <div class="col-12">
                    <textarea name="descricao" rows="4" class="form-control" maxlength="2000"
                        placeholder="Resumo da obra, observações sobre o estado do exemplar...">{{ old('descricao', $livro->descricao ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('livro.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop

@push('js')
<script>
$(function () {
    // Preview instantaneo da capa escolhida.
    document.getElementById('capaInput').addEventListener('change', function () {
        var img = document.getElementById('capaPreview');
        if (this.files && this.files[0]) {
            img.src = URL.createObjectURL(this.files[0]);
            img.style.display = '';
        }
    });
});
</script>
@endpush
