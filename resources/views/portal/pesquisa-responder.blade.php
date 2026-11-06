@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-page-title :title="$pesquisa->titulo" subtitle="Responda até ".$pesquisa->data_fim->format('d/m/Y')" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if($pesquisa->descricao)
            <p class="text-muted">{{ $pesquisa->descricao }}</p>
        @endif

        @if(count($alunos) > 1)
            <div class="form-group">
                <label class="form-label">Aluno relacionado (opcional)</label>
                <select name="aluno_id" id="aluno_id" class="form-control" style="max-width:400px;">
                    <option value="">—</option>
                    @foreach($alunos as $aluno)
                        <option value="{{ $aluno->id }}">{{ $aluno->pessoa->nome ?? $aluno->nome }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <input type="hidden" id="aluno_id" value="{{ $alunos->first()->id }}">
        @endif

        <form method="POST" action="{{ route('portal.pesquisa.salvar', $pesquisa->id) }}">
            @csrf
            <input type="hidden" name="aluno_id" id="aluno_id_hidden" value="{{ $alunos->first()->id }}">

            @foreach($pesquisa->perguntas as $p)
                <div class="border rounded p-3 mb-3">
                    <label class="font-weight-bold">
                        {{ $loop->iteration }}. {{ $p->pergunta }}
                        @if($p->obrigatoria === 'S')<span class="text-danger">*</span>@endif
                        <small class="text-muted font-weight-normal">— {{ $p->tipoLabel() }}</small>
                    </label>

                    @if($p->tipo === 'nota')
                        <div class="d-flex flex-wrap mt-2" style="gap:.5rem;">
                            @for($n = 1; $n <= 5; $n++)
                                <label class="btn btn-outline-primary btn-sm mb-0">
                                    <input type="radio" name="respostas[{{ $p->id }}]" value="{{ $n }}" class="d-none" required>
                                    {{ $n }}
                                </label>
                            @endfor
                        </div>
                    @elseif($p->tipo === 'sim_nao')
                        <div class="mt-2">
                            <label class="btn btn-outline-success btn-sm mr-2 mb-0">
                                <input type="radio" name="respostas[{{ $p->id }}]" value="S" class="d-none" required> Sim
                            </label>
                            <label class="btn btn-outline-danger btn-sm mb-0">
                                <input type="radio" name="respostas[{{ $p->id }}]" value="N" class="d-none" required> Não
                            </label>
                        </div>
                    @else
                        <textarea name="respostas[{{ $p->id }}]" class="form-control mt-2" rows="3" maxlength="4000"
                            placeholder="Sua resposta..."></textarea>
                    @endif

                    @error("respostas.{$p->id}")
                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                    @enderror
                </div>
            @endforeach

            <a href="{{ route('portal.pesquisas') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Enviar respostas</button>
        </form>
    </div>
</div>

<script>
// Marca o radio visualmente ao clicar no label
$(document).on('click', '.btn-outline-primary label, .btn-outline-success label, .btn-outline-danger label', function() {});
$(document).on('change', 'input[type=radio]', function () {
    const name = $(this).attr('name');
    $(`input[name="${name}"]`).closest('label').removeClass('active');
    $(this).closest('label').addClass('active');
});

// aluno_id do select vai para o hidden do form
$('#aluno_id').on('change', function () {
    $('#aluno_id_hidden').val($(this).val() || '');
});
</script>
@stop
