@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-10 col-xs-12 card p-4">
        <div class="">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                <div class="col-md-4 pl-0">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="codigo" name="codigo"
                            label="Código" value="{{ $tipo_frota->codigo ?? '' }}" required />
                    </div>
                </div>
                <div class="col-md-4 pl-0">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $tipo_frota->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col-md-4 pr-0">
                    <div class="form-group">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                            :selected="old('ativo',isset($tipo_frota->ativo) && $tipo_frota->ativo ? $tipo_frota->ativo : null)" class="form-control form-control-sm" required />
                    </div>
                </div>
                <x-section-division-header-form class="px-0">Características</x-section-division-header-form><br>
                <div class="d-flex justify-content-center my-2">
                    @foreach ($caracteristicas as $c)
                        <div class="btn btn-outline-secondary btn-xs mx-1">

                            <input type="checkbox" name="caracteristicas[]" value="{{ $c->id }}" class="btn-check"
                                id="btn-check-{{ $c->id }}-outlined" autocomplete="off"
                                {{ isset($caracteristicasTipoFrota) && in_array($c->id, $caracteristicasTipoFrota) ? 'checked' : '' }}>
                            <label class="m-0"
                                for="btn-check-{{ $c->id }}-outlined">{{ $c->nome }}</label><br>
                        </div>
                    @endforeach
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
