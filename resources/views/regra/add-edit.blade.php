@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="col-12 card p-2">
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                <div class="col pl-0">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="name"
                            label="Nome" value="{{ $regra->name ?? '' }}" required />
                    </div>
                </div>
                <div class="col pr-0">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="guard_name" name="guard_name"
                            label="Nome da Política de Seg." value="{{ $regra->guard_name ?? '' }}" required />
                    </div>
                </div>
                <div class="col-12 pb-3">
                    <x-form-label label="Permissões" for="permissions" class="pl-0" error="permissions" />
                    <div class="d-flex">
                        @foreach($permissoes as $permissao)
                        <div class="form-check col-3 col-sm-6">
                            <input class="form-check-input" type="checkbox" name="permissions[]" {{$permissao->checked}} value="{{ $permissao->id }}" id="permission-{{ $permissao->id }}">
                            <label class="form-check-label" for="permission-{{ $permissao->id }}">
                                {{ $permissao->name }}
                            </label>
                        </div>
                        @endforeach
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
