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
    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-10 col-xs-12 card p-2">
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                <div class="col-6">
                    <div class="form-group">
                        <x-form-select name="estacao_origem_id" label="Estação de Origem" :options="$estacoesOrigem"
                            placeholder="Selecione uma estação" :selected="isset($tempo_entre_entacao->estacao_origem_id)
                                ? $tempo_entre_entacao->estacao_origem_id
                                : null" class="form-control form-control-sm"
                            required />
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <x-form-select name="estacao_destino_id" label="Estação de Destino" :options="isset($estacaoDestino) ? $estacaoDestino : []"
                            placeholder="{{ isset($estacaoDestino) ?  'Seleciona uma estação' :  'Selecione primeiro a estação de destino'  }}"
                            :selected="isset($tempo_entre_entacao->estacao_destino_id)
                                ? $tempo_entre_entacao->estacao_destino_id
                                : null" class="form-control form-control-sm" required />
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <x-form-input type="time" class="form-control form-control-sm" id="tempo_deslocamento"
                        name="tempo_deslocamento" label="Tempo Deslocamento" value="{{ $tempo_entre_entacao->tempo_deslocamento ?? '' }}" required />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="..."
                            :selected="isset($tempo_entre_entacao->ativo) && $tempo_entre_entacao->ativo
                                ? $tempo_entre_entacao->ativo
                                : null" class="form-control form-control-sm" required />
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
    <script>
        // ao selecionar eswtacao de origem, carrega as estações de destino
        $('select[name="estacao_origem_id"]').on('change', function() {
            var estacao_origem = $(this).val();
            if (estacao_origem) {
                $.ajax({
                    url: "{{ route('estacao.getEstacoesDisponiveis') }}",
                    type: "GET",
                    data: {
                        estacao_origem_id: estacao_origem
                    },
                    success: function(data) {
                        $('select[name="estacao_destino_id"]').empty();
                        $('select[name="estacao_destino_id"]').append('<option value="">Selecione uma estação</option>');
                        $.each(data, function(key, value) {
                            $('select[name="estacao_destino_id"]').append('<option value="' + value.id + '">' + value.nome + '</option>');
                        });
                    }
                });
            } else {
                $('select[name="estacaoDestino"]').empty();
            }
        });

    </script>
@stop
