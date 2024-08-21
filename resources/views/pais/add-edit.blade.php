@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-10 col-xs-12 card p-2">
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                <div class="col pl-0">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $pais->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col-md-2 pl-0">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm text-uppercase" id="sigla" name="sigla"
                            label="Sigla" max-length="2" value="{{ $pais->sigla ?? '' }}" required />
                    </div>
                </div>
                <div class="col pr-0">
                    <div class="form-group">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                            :selected="isset($pais->ativo) && $pais->ativo ? $pais->ativo : null"
                            class="form-control form-control-sm" required />
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-xs col-12">
                    Salvar
                </button>
            </x-form>
        </div>
    </div>
@stop

@section('js')
    <script></script>
@stop
