@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8 col-xs-12 card p-2">
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" >

                    <div class="alert alert-danger">
                        <ul class="m-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>

                <div class="form-row">

                    <div class="col-md-12">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                        label="Nome" value="{{ $dano->nome ?? '' }}" required />
                    </div>

                    <div class="col-md-12">
                        <x-form-select name="categoria_id" label="Categorias" id="categoria" :options="$categorias"
                            placeholder="Selecione uma categoria"
                            class="form-control form-control-sm" required />
                    </div>
                    <div class="col-md-12">
                        <x-form-select name="localizacao" label="Localização" id="localizacao" :options="['interior'=>'Interior','exterior'=>'Exterior']"
                            placeholder="Selecione uma localizacao"
                            class="form-control form-control-sm" required />
                    </div>

                    <div class="col-md-12">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                        :selected="isset($dano->ativo) && $dano->ativo ? $dano->ativo : 'S'"
                        class="form-control form-control-sm" required />
                    </div>
                    <div class="col-md-12 mt-2">
                        <button type="submit" class="btn btn-primary btn-xs col-12">
                            Salvar
                        </button>
                    </div>
                </div>
            </x-form>
        </div>
    </div>
@stop

@section('js')
    <script></script>
@stop
