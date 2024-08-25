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
    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-10 col-xs-12 card p-2">
        <div class="card-header col-12" style="border-bottom: 0px">

            <ul class="nav nav-tabs text-sm" id="custom-tabs-three-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="geral-tab" data-toggle="pill" href="#geral" role="tab"
                        aria-controls="geral" aria-selected="true"><i class="fa fa-info-circle text-xs"></i> Geral</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="caracteristicas-tab" data-toggle="pill" href="#caracteristicas" role="tab"
                        aria-controls="caracteristicas" aria-selected="false"><i class="fa fa-list-ul text-xs"></i>
                        Caracteristicas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="opcionais-tab" data-toggle="pill" href="#opcionais" role="tab"
                        aria-controls="opcionais" aria-selected="false"><i class="fa fa-plus-circle text-xs"></i>
                        Opcionais</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="modelos-tab" data-toggle="pill" href="#modelos" role="tab"
                        aria-controls="modelos" aria-selected="false"><i class="fa fa-tags text-xs"></i>
                        Modelos</a>
                </li>
            </ul>
        </div>
        <div class="card-body pt-0">
            <form id="formulario" method="POST" action="{{ $rota }}">
                @if ($metodo == 'PUT')
                    @method('PUT')
                @endif
                @csrf
                <div id="geral-content" class="content-tab  ">
                    <div class="row mx-0">

                        <div class="col-md-8">
                            <div class="form-group">
                                <x-form-input type="text" class="form-control form-control-sm" id="nome"
                                    name="nome" label="Nome" value="{{ old('nome', $grupoFrota->nome ?? '') }}"
                                    required />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <x-form-input type="text" class="form-control form-control-sm" id="codigo"
                                    name="codigo" label="Código" value="{{ old('codigo', $grupoFrota->codigo ?? '') }}"
                                    required />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <x-form-input type="text" class="form-control form-control-sm" id="codigo_sipp"
                                    name="codigo_sipp" label="Código SIPP"
                                    value="{{ old('codigo_sipp', $grupoFrota->codigo_sipp ?? '') }}" required />
                            </div>
                        </div>
                        <div class="col-md-3 ">
                            <div class="form-group">
                                <x-form-select name="tipo_frota_id" label="Tipo de Frota" :options="$tipoFrotas"
                                    placeholder="Selecione..." :selected="old(
                                        'tipo_frota_id',
                                        isset($grupoFrota->tipo_frota_id) && $grupoFrota->tipo_frota_id
                                            ? $grupoFrota->tipo_frota_id
                                            : null,
                                    )" class="form-control form-control-sm"
                                    required />
                            </div>
                        </div>
                        <div class="col-md-3 ">
                            <div class="form-group">
                                <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione..."
                                    :selected="old(
                                        'ativo',
                                        isset($grupoFrota->ativo) && $grupoFrota->ativo ? $grupoFrota->ativo : null,
                                    )" class="form-control form-control-sm" required />
                            </div>
                        </div>
                        <div class="col-md-6 mt-4">
                            <div class="form-group">
                                <input type="file" name="imagem" id="imagem" class="custom-file-input"
                                    accept=".jpeg,.png,.jpg,.gif,.svg" {{ isset($grupoFrota) ? '' : 'required' }}>
                                <label for="imagem" class="custom-file-label">Selecione a imagem</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="caracteristicas-content" class="content-tab" style="display: none;">

                    <div class="d-flex justify-content-center my-2" style="margin:0 auto">
                        <table class="table table-striped table-bordered table-condensed table-sm rounded text-xs m-0">
                            <thead>
                                <tr>
                                    <th>Característica</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($caracteristicas as $k => $caracteristica)
                                    <tr>
                                        <td>{{ $caracteristica }}</td>
                                        <td>
                                            <input type="hidden" name="caracteristicas[{{ $k }}]"
                                                value="{{ $k }}">
                                            <input type="text" name="caracteristicaValor[{{ $k }}]"
                                            class="form-control form-control-sm"
                                            value="{{ isset($grupoFrota) && collect($grupoFrota->caracteristicas)->contains('id', $k) ? collect($grupoFrota->caracteristicas)->firstWhere('id', $k)['valor'] : '' }}"                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="opcionais-content" class="content-tab" style="display: none;">

                    @foreach ($tiposOpcionais as $tipoOpcional)
                        @if (count($tipoOpcional->opcionais) > 0)
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title text-sm text-bold">{{ $tipoOpcional->nome }}</h3>
                                </div>
                                <div class="card-body d-flex">
                                    @foreach ($tipoOpcional->opcionais as $opcional)
                                        <div class="form-check col-md-4">
                                            <input class="form-check-input" type="checkbox"
                                                id="opcional_{{ $opcional->id }}" name="opcionais[]"
                                                {{ isset($grupoFrota) && in_array($opcional->id, $grupoFrota->opcionais) ? 'checked' : '' }}
                                                value="{{ $opcional->id }}">
                                            <label class="form-check-label" for="opcional_{{ $opcional->id }}">
                                                {{ $opcional->nome }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                </div>
                <div id="modelos-content" class="content-tab" style="display: none;">
                    <div class="card">
                        <div class="card-header mb-1">
                            <h3 class="card-title text-sm text-bold">Lista dos Modelos:</h3>
                        </div>
                        <div class="card-body d-flex p-0">
                            <div class="row mx-0">
                                @foreach ($modelos as $id_modelo => $modelo)
                                    <a class="col-md-4 text-dark border rounded">
                                        <div style="cursor:pointer" class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox"
                                                id="modelo_{{ $id_modelo }}" name="modelos[]"
                                                value="{{ $id_modelo }}"
                                                {{ isset($grupoFrota) && in_array($id_modelo, $grupoFrota->modelos) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="modelo_{{ $id_modelo }}">
                                                {{ $modelo }}
                                            </label>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-xs col-12">
                    Salvar
                </button>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.nav-link').on('click', function() {
                $('.nav-link').removeClass('active');
                $(this).addClass('active');

                $('.content-tab').hide();
                $($(this).attr('href') + '-content').show();
            });
        });
    </script>
@stop
