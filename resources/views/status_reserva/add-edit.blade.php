@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8 col-xs-12 card p-2">
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $status_reserva->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                            :selected="isset($status_reserva->ativo) && $status_reserva->ativo
                                ? $status_reserva->ativo
                                : null" class="form-control form-control-sm" required />
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label for="cor">Cor</label>
                        <select name="cor" id="cor" class="form-control form-control-sm">
                            <option {{ isset($status_reserva->cor) && $status_reserva->cor == 'warning' ? 'selected' : '' }} value="warning">
                                Amarelo
                            </option>
                            <option {{ isset($status_reserva->cor) && $status_reserva->cor == 'primary' ? 'selected' : '' }} value="primary">
                                Azul
                            </option>
                            <option {{ isset($status_reserva->cor) && $status_reserva->cor == 'info' ? 'selected' : '' }} value="info">
                                Azul Claro
                            </option>
                            <option {{ isset($status_reserva->cor) && $status_reserva->cor == 'cyan' ? 'selected' : '' }} value="cyan">
                                Ciano
                            </option>
                            <option {{ isset($status_reserva->cor) && $status_reserva->cor == 'secondary' ? 'selected' : '' }} value="secondary">
                                Cinza
                            </option>
                            <option {{ isset($status_reserva->cor) && $status_reserva->cor == 'indigo' ? 'selected' : '' }} value="indigo">
                                Índigo
                            </option>
                            <option {{ isset($status_reserva->cor) && $status_reserva->cor == 'orange' ? 'selected' : '' }} value="orange">
                                Laranja
                            </option>
                            <option {{ isset($status_reserva->cor) && $status_reserva->cor == 'black' ? 'selected' : '' }} value="black">
                                Preto
                            </option>
                            <option {{ isset($status_reserva->cor) && $status_reserva->cor == 'purple' ? 'selected' : '' }} value="purple">
                                Roxo
                            </option>
                            <option {{ isset($status_reserva->cor) && $status_reserva->cor == 'pink' ? 'selected' : '' }} value="pink">
                                Rosa
                            </option>
                            <option {{ isset($status_reserva->cor) && $status_reserva->cor == 'success' ? 'selected' : '' }} value="success">
                                Verde
                            </option>
                            <option {{ isset($status_reserva->cor) && $status_reserva->cor == 'teal' ? 'selected' : '' }} value="teal">
                                Verde-azulado
                            </option>
                            <option {{ isset($status_reserva->cor) && $status_reserva->cor == 'lime' ? 'selected' : '' }} value="lime">
                                Verde Limão
                            </option>
                            <option {{ isset($status_reserva->cor) && $status_reserva->cor == 'danger' ? 'selected' : '' }} value="danger">
                                Vermelho
                            </option>
                        </select>

                        <x-form-errors name="cor" />
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea name="descricao" id="descricao" cols="10" class="form-control text-xs" rows="5">{{isset($status_reserva->descricao) && $status_reserva->descricao ? $status_reserva->descricao : ''}}</textarea>
                        <x-form-errors name="descricao" />
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
