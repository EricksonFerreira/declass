@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="card col-md-6 p-4">
        <x-form method="POST" action="{{ route('empresa_context.update') }}" rowClass="row">
            <div class="col-12">
                <div class="form-group">
                    <x-form-select
                        name="empresa_id"
                        label="Tenant ativo"
                        :options="$empresas"
                        placeholder="Selecione uma empresa"
                        :selected="old('empresa_id', $empresaAtual)"
                        class="form-control form-control-sm"
                        required
                    />
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-xs col-12">
                    Alterar empresa
                </button>
            </div>
        </x-form>
    </div>
@stop

