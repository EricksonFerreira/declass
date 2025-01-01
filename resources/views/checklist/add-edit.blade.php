@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="col-xl-8 col-lg-10 col-md-12 card p-4">
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <x-form-input type="text" class="form-control" id="nome" name="nome"
                                label="Nome do Checklist" value="{{ $checklist->nome ?? '' }}" required />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um status"
                                :selected="isset($checklist->ativo) ? $checklist->ativo : null"
                                class="form-control" required />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="itens_checklist">Itens do Checklist</label>
                    <div id="itens_checklist">
                        @foreach($itensChecklist ?? [] as $index => $item)
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="itens_checklist[]" value="{{ $item }}" placeholder="Item do checklist">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-danger remover-item" type="button">Remover</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="adicionar-item">Adicionar Item</button>
                </div>

                <div class="form-group">
                    <label for="observacoes">Observações</label>
                    <textarea class="form-control" id="observacoes" name="observacoes" rows="3">{{ $checklist->observacoes ?? '' }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block mt-4">
                    Salvar Checklist
                </button>
            </x-form>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#adicionar-item').click(function() {
                $('#itens_checklist').append(`
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" name="itens_checklist[]" placeholder="Item do checklist">
                        <div class="input-group-append">
                            <button class="btn btn-outline-danger remover-item" type="button">Remover</button>
                        </div>
                    </div>
                `);
            });

            $(document).on('click', '.remover-item', function() {
                $(this).closest('.input-group').remove();
            });
        });
    </script>
@stop
