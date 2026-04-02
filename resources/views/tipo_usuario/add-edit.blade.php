@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger col-xl-8 col-lg-10 col-md-12 col-sm-12 col-xs-12 ">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12 card p-2">
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                <div class="col-md-6 pl-0">
                    <div class="form-group">
                        <x-form-input
                            type="text"
                            class="form-control form-control-sm"
                            id="name"
                            name="name"
                            label="Nome"
                            value="{{ $tipoUsuario->name ?? '' }}"
                            required
                        />
                    </div>
                </div>
                <div class="col-md-6 pr-0">
                    <div class="form-group">
                        <x-form-input
                            type="text"
                            class="form-control form-control-sm"
                            id="guard_name"
                            name="guard_name"
                            label="Guard Name"
                            value="{{ $tipoUsuario->guard_name ?? '' }}"
                            required
                        />
                    </div>
                </div>

                <div class="col-12 px-0">
                    <div class="card p-2">
                        <h5 class="text-sm text-bold mb-2">Permissoes</h5>
                        <div class="row">
                            @foreach ($permissoes as $permissao)
                                <div class="col-md-4">
                                    <div class="form-check mb-1">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="permissions[]"
                                            id="perm_{{ $permissao->id }}"
                                            value="{{ $permissao->id }}"
                                            {{ $permissao->checked ?? '' }}
                                        >
                                        <label class="form-check-label text-xs" for="perm_{{ $permissao->id }}">
                                            {{ $permissao->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-xs col-12 mt-2">
                    Salvar
                </button>
            </x-form>
        </div>
    </div>
@stop

@section('js')
    <script></script>
@stop
