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
                <div class="col pl-0">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $frota->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col pr-0">
                    <div class="form-group">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                            :selected="isset($frota->ativo) && $frota->ativo ? $frota->ativo : null"
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
<x-page-tour tour-id="frota-add-edit" :steps="[
    [
        'element' => '.page-title',
        'popover' => [
            'title' => 'Cadastro de Veículo',
            'description' => 'Aqui você cria ou edita um grupo de veículos. Grupos organizam modelos similares para facilitar a disponibilidade nas reservas.',
        ],
    ],
    [
        'element' => '#nome',
        'popover' => [
            'title' => 'Nome do grupo',
            'description' => 'Informe um nome descritivo para o grupo, como "Econômico", "SUV" ou "Premium". Este nome aparecerá na listagem de reservas.',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => '[name=\"ativo\"]',
        'popover' => [
            'title' => 'Status',
            'description' => 'Defina se o grupo estará <b>Ativo</b> (disponível para reservas) ou <b>Inativo</b> (oculto no processo de reserva).',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => 'button[type=\"submit\"]',
        'popover' => [
            'title' => 'Salvar',
            'description' => 'Clique em Salvar para gravar o grupo de veículos. Você será redirecionado para a lista após a confirmação.',
            'side' => 'top',
        ],
    ],
]" />
@stop

@section('css')
@stop