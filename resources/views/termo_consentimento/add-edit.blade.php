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

    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8 col-xs-12 card p-2">
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                <div class="col pl-0">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $termo_consentimento->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col pr-0">
                    <div class="form-group">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                            :selected="old(
                                'ativo',
                                isset($termo_consentimento->ativo) && $termo_consentimento->ativo
                                    ? $termo_consentimento->ativo
                                    : null,
                            )" class="form-control form-control-sm" required />
                    </div>
                </div>
                <div class="col-md-12 pr-0">
                    <div class="form-group">
                        <label for="texto">Texto</label>
                        <textarea name="texto" id="texto" class="form-control text-xs" cols="30" rows="10"
                            >{{ old('texto', isset($termo_consentimento->texto) && $termo_consentimento->texto ? $termo_consentimento->texto : null) }}</textarea>
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
