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
                <div class="col-md-6 pl-0">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $modelo->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col-md-3 pr-0">
                    <div class="form-group">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione..."
                            :selected="isset($modelo->ativo) && $modelo->ativo ? $modelo->ativo : null"
                            class="form-control form-control-sm" required />
                    </div>
                </div>
                <div class="col-md-3 pr-0">
                    <div class="form-group">
                        <x-form-select name="marca_id" label="Marcas" :options="$marcas" placeholder="Selecione..."
                            :selected="isset($modelo->marca_id) && $modelo->marca_id ? $modelo->marca_id : null"
                            class="form-control form-control-sm" required />
                    </div>
                </div>
                <div class="col-md-3 pr-0">
                    <div class="form-group">
                        <x-form-select name="segmento_id" label="Segmentos" :options="$segmentos" placeholder="Selecione..."
                            :selected="isset($modelo->segmento_id) && $modelo->segmento_id ? $modelo->segmento_id : null"
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
