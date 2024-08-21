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
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ old('nome', $grupoFrota->nome ?? '') }}" required />
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="codigo" name="codigo"
                            label="Código" value="{{ old('codigo', $grupoFrota->codigo ?? '') }}" required />
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
                            )" class="form-control form-control-sm" required />
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
                <div class="col-md-12">
                    <div class="form-group">
                        <x-form-select name="marca_id" label="Marca | Modelo | Segmento" :options="$marcas"
                            placeholder="Selecione um valor" :selected="old(
                                'marca_id',
                                isset($grupoFrota->marca_id) && $grupoFrota->marca_id ? $grupoFrota->marca_id : null,
                            )" class="form-control form-control-sm"
                            required />
                    </div>
                </div>

                <x-section-division-header-form>Caracteristicas</x-section-division-header-form><br>
                <div class="d-flex justify-content-center my-2" style="margin:0 auto">
                    <table class="table table-striped table-bordered table-sm rounded text-xs m-0">
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
                                        <input type="text" name="caracteristicas[{{ $k }}]"
                                            value="{{ isset($caracteristicasGrupoFrota[$k]) ? $caracteristicasGrupoFrota[$k] : '' }}"
                                            class="form-control form-control-sm">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
