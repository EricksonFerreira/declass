@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8 col-xs-12 card p-2">
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $tipo_movimentacao->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                            :selected="isset($tipo_movimentacao->ativo) && $tipo_movimentacao->ativo ? $tipo_movimentacao->ativo : null"
                            class="form-control form-control-sm" required />
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea name="descricao" id="descricao" cols="10" class="form-control text-xs" rows="5">{{isset($tipo_movimentacao->descricao) && $tipo_movimentacao->descricao ? $tipo_movimentacao->descricao : ''}}</textarea>
                        <x-form-errors name="descricao" />
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
