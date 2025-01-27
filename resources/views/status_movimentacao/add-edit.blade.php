@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12 col-xs-12 card p-2">
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                <div class="col pl-0">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $status_movimentacao->nome ?? '' }}" required />
                    </div>
                </div>

                <div class="col">
                    <div class="form-group">
                        <label for="cor">Cor</label>
                        <select name="cor" id="cor" class="form-control form-control-sm">
                            <option {{ isset($status_movimentacao->cor) && $status_movimentacao->cor == 'warning' ? 'selected' : '' }} value="warning">
                                Amarelo
                            </option>
                            <option {{ isset($status_movimentacao->cor) && $status_movimentacao->cor == 'primary' ? 'selected' : '' }} value="primary">
                                Azul
                            </option>
                            <option {{ isset($status_movimentacao->cor) && $status_movimentacao->cor == 'info' ? 'selected' : '' }} value="info">
                                Azul Claro
                            </option>
                            <option {{ isset($status_movimentacao->cor) && $status_movimentacao->cor == 'cyan' ? 'selected' : '' }} value="cyan">
                                Ciano
                            </option>
                            <option {{ isset($status_movimentacao->cor) && $status_movimentacao->cor == 'secondary' ? 'selected' : '' }} value="secondary">
                                Cinza
                            </option>
                            <option {{ isset($status_movimentacao->cor) && $status_movimentacao->cor == 'indigo' ? 'selected' : '' }} value="indigo">
                                Índigo
                            </option>
                            <option {{ isset($status_movimentacao->cor) && $status_movimentacao->cor == 'orange' ? 'selected' : '' }} value="orange">
                                Laranja
                            </option>
                            <option {{ isset($status_movimentacao->cor) && $status_movimentacao->cor == 'black' ? 'selected' : '' }} value="black">
                                Preto
                            </option>
                            <option {{ isset($status_movimentacao->cor) && $status_movimentacao->cor == 'purple' ? 'selected' : '' }} value="purple">
                                Roxo
                            </option>
                            <option {{ isset($status_movimentacao->cor) && $status_movimentacao->cor == 'pink' ? 'selected' : '' }} value="pink">
                                Rosa
                            </option>
                            <option {{ isset($status_movimentacao->cor) && $status_movimentacao->cor == 'success' ? 'selected' : '' }} value="success">
                                Verde
                            </option>
                            <option {{ isset($status_movimentacao->cor) && $status_movimentacao->cor == 'teal' ? 'selected' : '' }} value="teal">
                                Verde-azulado
                            </option>
                            <option {{ isset($status_movimentacao->cor) && $status_movimentacao->cor == 'lime' ? 'selected' : '' }} value="lime">
                                Verde Limão
                            </option>
                            <option {{ isset($status_movimentacao->cor) && $status_movimentacao->cor == 'danger' ? 'selected' : '' }} value="danger">
                                Vermelho
                            </option>
                        </select>

                        <x-form-errors name="cor" />
                    </div>
                </div>
                <div class="col pr-0">
                    <div class="form-group">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                            :selected="isset($status_movimentacao->ativo) && $status_movimentacao->ativo ? $status_movimentacao->ativo : null"
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
