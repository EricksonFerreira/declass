@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-12 col-xs-12 card p-2">
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                <div class="col-4 pl-0">
                    <div class="form-group">
                        <x-form-select
                            :options="$tipoChecklist"
                            name="tipo_checklist_id" label="Tipo de Checklist" placeholder="Selecione um valor"
                            class="form-control form-control-sm" required />
                    </div>
                </div>
                <div class="col-12 pl-0">
                    <div class="form-group">
                        <label for="item_checklist_id" class="mb-2">
                            Item do Checklist<span style="color: red;">*</span><br>
                            <a onclick="alterarTodosItens(true)" class="btn btn-xs btn-secondary mb-2"><i class="fa fa-check"></i> Selecionar Todos</a>
                            <a onclick="alterarTodosItens(false)" class="btn btn-xs btn-white border border-secondary mb-2"><i class="fa fa-times"></i> Remover Todos</a>
                        </label>
                        @foreach ($itemChecklist as $key => $value)
                            @if ($loop->iteration % 4 == 1)
                                <div class="row">
                            @endif
                                <div class="col-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="{{ $key }}" name="item_checklist_id[]" value="{{ $key }}" {{ $loop->iteration % 4 == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="{{ $key }}">{{ $value }}</label>
                                    </div>
                                </div>
                            @if ($loop->iteration % 4 == 0 || $loop->last)
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- <div class="col pr-0">
                    <div class="form-group">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                            :selected="isset($configuracaochecklist->ativo) && $configuracaochecklist->ativo ? $configuracaochecklist->ativo : null"
                            class="form-control form-control-sm" required />
                    </div>
                </div> --}}
                <button type="submit" class="btn btn-primary btn-xs col-12">
                    Salvar
                </button>
            </x-form>
        </div>
    </div>
@stop

@section('js')
    <script>
        function alterarTodosItens(checked = true){
            $('input[type="checkbox"]').prop('checked', checked);
        }
    </script>
@stop
