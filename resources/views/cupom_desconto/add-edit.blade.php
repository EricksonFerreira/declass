@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    @include('financeiro.fluxo_caixa._styles')

    @php
        $financeiro = str_starts_with((string) Route::currentRouteName(), 'financeiro.');
        $routePrefix = $financeiro ? 'financeiro.' : '';
    @endphp

    <div class="fc-page">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                    @foreach (array_unique($errors->all()) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card fc-card fc-form-card">
            <div class="card-header py-3">
                <div class="fc-head">
                    <h5 class="fc-title">
                        <i class="fas fa-percentage"></i>
                        <span>{{ $titulo }}<span class="fc-subtitle">Configure codigo, percentual e status do cupom</span></span>
                    </h5>
                    <a href="{{ route($routePrefix.'cupom_desconto.index') }}" class="btn btn-outline-secondary fc-btn">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form id="formulario-cupom" method="POST" action="{{ $rota }}">
                    @if (strtoupper($metodo ?? '') === 'PUT')
                        @method('PUT')
                    @endif
                    @csrf

                    <div class="row">
                        <div class="col-lg-5 col-md-6 mb-3">
                            <label for="nome" class="fc-label">Nome</label>
                            <input type="text" name="nome" id="nome" class="form-control" value="{{ old('nome', $cupom_desconto->nome ?? '') }}" required>
                        </div>
                        <div class="col-lg-2 col-md-6 mb-3">
                            <label for="codigo" class="fc-label">Codigo</label>
                            <input type="text" name="codigo" id="codigo" maxlength="7" class="form-control" value="{{ old('codigo', $cupom_desconto->codigo ?? '') }}" required>
                        </div>
                        <div class="col-lg-2 col-md-6 mb-3">
                            <label for="valor" class="fc-label">Valor</label>
                            <input type="text" name="valor" id="valor" class="form-control" value="{{ old('valor', $cupom_desconto->valor ?? '') }}" required>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label for="ativo" class="fc-label">Status</label>
                            <select name="ativo" id="ativo" class="form-control" required>
                                @foreach($status as $value => $label)
                                    <option value="{{ $value }}" {{ old('ativo', $cupom_desconto->ativo ?? 'S') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="fc-actions justify-content-end">
                        <a href="{{ route($routePrefix.'cupom_desconto.index') }}" class="btn btn-outline-secondary fc-btn">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary fc-btn">
                            <i class="fas fa-save"></i> Salvar cupom
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(function() {
        $('#valor').mask('##0,00%', { reverse: true });

        $('#formulario-cupom').on('submit', function() {
            const valorAtual = $('#valor').val() || '';
            $('#valor').val(valorAtual.replace('%', ''));
        });
    });
</script>
@stop
