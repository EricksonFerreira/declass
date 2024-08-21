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
                        <x-form-input type="number" class="form-control form-control-sm" min="0" max="7" id="dia" name="dia"
                            label="Dia" value="{{ $dia_util->dia ?? '' }}" required />
                    </div>
                </div>
                <div class="col pl-0">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $dia_util->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col pr-0">
                    <div class="form-group">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                            :selected="isset($dia_util->ativo) && $dia_util->ativo ? $dia_util->ativo : null"
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
