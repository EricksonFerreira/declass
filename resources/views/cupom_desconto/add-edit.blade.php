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
                <div class="col-md-12">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $cupom_desconto->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="codigo" name="codigo"
                        maxlength="7"
                            label="Código" value="{{ $cupom_desconto->codigo ?? '' }}" required />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="valor" name="valor"
                            label="Valor" value="{{ $cupom_desconto->valor ?? '' }}" required />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                            :selected="isset($cupom_desconto->ativo) && $cupom_desconto->ativo ? $cupom_desconto->ativo : null"
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
    $(document).ready(function(){
        // Adicione a máscara de percentual ao campo de entrada
        $('#valor').mask('##0,00%', {reverse: true});

        // Remova o símbolo de percentual antes de enviar o formulário
        $('form').on('submit', function() {
            var valor = $('#valor').val();
            valor = valor.replace('%', ''); // Remove o símbolo de percentual
            $('#valor').val(valor);
        });
    });
</script>@stop
