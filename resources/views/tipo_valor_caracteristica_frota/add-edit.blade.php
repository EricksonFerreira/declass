@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <!-- Exibir erros de validação -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8 col-xs-12 card p-2">
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row flex-grow">
                <div class="col-md-8 pl-0">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $tipo_valor_caracteristica_frota->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col-md-4 pr-0">
                    <div class="form-group">
                        <x-form-select name="ativo" label="Ativo" :options="$status" placeholder="Selecione..."
                            :selected="old(
                                'ativo',
                                isset($tipo_valor_caracteristica_frota->ativo)
                                    ? $tipo_valor_caracteristica_frota->ativo
                                    : null,
                            )" class="form-control form-control-sm" required />
                    </div>
                </div>
                <div class="col-md-12 pl-0">
                    <div class="form-group">
                        <label for="configuracao" data-toggle="tooltip" data-placement="top"
                            title="O codigo desse campo deve ser JS">Configuração</label>
                        <textarea class="form-control text-sm" name="configuracao" id="configuracao" cols="30" rows="3">{{ old('configuracao', $tipo_valor_caracteristica_frota->configuracao ?? '') }}</textarea>
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
