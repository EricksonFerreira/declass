@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <!-- Exibir erros de validação -->
    @if ($errors->any())
        <div class="alert alert-danger col-xl-8 col-lg-10 col-md-12 col-sm-12 col-xs-12 ">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

<div class="col-xl-6 col-lg-8 col-md-8 col-sm-10 col-xs-12 card p-4">
    <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                <div class="col-md-2 pl-0">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="codigo" name="codigo"
                            label="Código" value="{{ $caracteristicaFrota->codigo ?? '' }}" required />
                    </div>
                </div>
                <div class="col-md-4 pl-0">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                        label="Nome" value="{{ $caracteristicaFrota->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col-md-3 pl-0">
                    <div class="form-group">
                        <x-form-select name="tipo_valor_caracteristica_frota_id" label="Tipo do valor" :options="$valorCaracteristicaTipoFrota" placeholder="Selecione..."
                            :selected="old('tipo_valor_caracteristica_frota_id',isset($caracteristicaFrota->tipo_valor_caracteristica_frota_id) && $caracteristicaFrota->tipo_valor_caracteristica_frota_id ? $caracteristicaFrota->tipo_valor_caracteristica_frota_id : null)"
                            class="form-control form-control-sm" required />
                    </div>
                </div>
                <div class="col-md-3 pr-0">
                    <div class="form-group">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione..."
                            :selected="old('ativo',isset($caracteristicaFrota->ativo) && $caracteristicaFrota->ativo ? $caracteristicaFrota->ativo : null)" class="form-control form-control-sm" required />
                    </div>
                </div>
                <div class="col-md-12 pl-0">
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea class="form-control text-sm" name="descricao" id="descricao" cols="30" rows="10"
                            >{{old('descricao',$caracteristicaFrota->descricao ?? null)}}</textarea>
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
