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
                            label="Nome" value="{{ $opcional_grupo_veiculo->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col pr-0">
                    <div class="form-group">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                            :selected="isset($opcional_grupo_veiculo->ativo) && $opcional_grupo_veiculo->ativo ? $opcional_grupo_veiculo->ativo : null"
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
    <x-page-tour tour-id="opcional-grupo-veiculo-add-edit" :steps="[
        [
            'element' => '.page-title',
            'popover' => [
                'title' => 'Cadastro do vínculo',
                'description' => 'Nesta tela você cria ou edita o vínculo de opcional para organização por grupos de veículos.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => 'form',
            'popover' => [
                'title' => 'Formulário principal',
                'description' => 'Preencha o nome do vínculo e defina o status para disponibilizar esse cadastro no sistema.',
                'side' => 'top',
            ],
        ],
        [
            'element' => '#nome',
            'popover' => [
                'title' => 'Nome do vínculo',
                'description' => 'Use um nome claro para identificar a relação entre opcional e grupo de veículo.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => 'select[name=\"ativo\"]',
            'popover' => [
                'title' => 'Status do vínculo',
                'description' => 'Defina se o vínculo ficará ativo e disponível para uso nas configurações relacionadas.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => 'button[type=\"submit\"]',
            'popover' => [
                'title' => 'Salvar',
                'description' => 'Clique aqui para gravar o vínculo após concluir o preenchimento.',
                'side' => 'top',
            ],
        ],
    ]" />
@stop
