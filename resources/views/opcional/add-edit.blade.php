@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Atenção!</h5>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8 col-xs-12 card p-2">
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $opcional->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-select name="tipo_opcional_id" label="Tipo Opcional" :options="$tipoOpcional"
                            placeholder="Selecione..." :selected="isset($opcional->tipo_opcional_id) && $opcional->tipo_opcional_id
                                ? $opcional->tipo_opcional_id
                                : null" class="form-control form-control-sm" required />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-select id="tipo_cobranca" name="tipo_cobranca" label="Tipo de Cobrança" :options="['por_dia' => 'Por Dia', 'valor_integral' => 'Integral']"
                            :selected="old(
                                'tipo_cobranca',
                                isset($opcional->tipo_cobranca) && $opcional->tipo_cobranca
                                    ? $opcional->tipo_cobranca
                                    : null,
                            )" placeholder="Selecione..." class="form-control form-control-sm" required
                            onchange="handleTipoCobrancaChange()" />
                    </div>
                </div>
                <div class="col-md-4" id="preco_por_dia_div">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="preco_por_dia"
                            name="preco_por_dia" label="Preço por dia"
                            value="{{ old('preco_por_dia', $opcional->preco_por_dia ?? '') }}" required />
                    </div>
                </div>
                <div class="col-md-4" id="preco_integral_div">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="preco_integral"
                            name="preco_integral" label="Preço Integral"
                            value="{{ old('preco_integral', $opcional->preco_integral ?? '') }}" required />
                    </div>
                </div>
                @if (isset($opcional))
                    <div class="col-md-4">
                        <div class="form-group">
                            <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                                :selected="old(
                                    'ativo',
                                    isset($opcional->ativo) && $opcional->ativo ? $opcional->ativo : null,
                                )" class="form-control form-control-sm" required />
                        </div>
                    </div>
                @else
                    <input type="hidden" name="ativo" value="S">
                @endif
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="">Descrição</label>
                        <textarea class="form-control" name="descricao" id="" rows="3">{{ old('descricao', $opcional->descricao ?? '') }}</textarea>
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
        $(document).ready(function() {
            @if (!isset($opcional) || empty($opcional))
                $('#preco_por_dia_div').hide();
                $('#preco_integral_div').hide();
            @else
                tipoCobranca = '{{ $opcional->tipo_cobranca }}';

                if (tipoCobranca == 'por_dia') {
                    $('#preco_por_dia_div').show();
                    $('#preco_integral_div').hide();
                } else {
                    $('#preco_por_dia_div').hide();
                    $('#preco_integral_div').show();
                }
            @endif



            $('#preco_por_dia').mask('0000000000,00', {
                reverse: true
            });
            $('#preco_integral').mask('0000000000,00', {
                reverse: true
            });

        });


        function handleTipoCobrancaChange() {
            removeObrigatorios();
            if ($('#tipo_cobranca').val() == 'por_dia') {
                $('#preco_por_dia_div').show();
                $('#preco_integral_div').hide();
                $('#preco_integral').val('');
                $('#preco_por_dia').addAttr('required');
            } else {
                $('#preco_por_dia_div').hide();
                $('#preco_integral_div').show();
                $('#preco_por_dia').val('');
                $('#preco_integral').addAttr('required');
            }
        }


        function removeObrigatorios() {
            $('#preco_integral').removeAttr('required');
            $('#preco_por_dia').removeAttr('required');
        }
    </script>
@stop
