{{-- Formulario de nova solicitacao a secretaria (usado na pagina dedicada e no collapse da listagem). --}}
<form method="POST" action="{{ route('portal.solicitacoes.store') }}" enctype="multipart/form-data">
    @csrf
    @if($alunos->count() > 1)
        <div class="form-group">
            <label for="aluno_id">Aluno</label>
            <select name="aluno_id" id="aluno_id" class="form-control" required>
                @foreach($alunos as $aluno)
                    <option value="{{ $aluno->id }}" {{ (string) old('aluno_id') === (string) $aluno->id ? 'selected' : '' }}>{{ $aluno->pessoa->nome ?? $aluno->nome }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if($tipos->isNotEmpty())
        <div class="form-group">
            <label for="tipo_id">Tipo de solicitação</label>
            <select name="tipo_id" id="tipo_id" class="form-control" required>
                <option value="">Selecione…</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->id }}" {{ (string) old('tipo_id') === (string) $tipo->id ? 'selected' : '' }}>{{ $tipo->nome }}</option>
                @endforeach
            </select>
        </div>
        <div id="tipo-instrucoes" class="alert alert-info py-2 small" style="display:none;"></div>
        <div id="campos-dinamicos"></div>
    @else
        <div class="form-group">
            <label for="tipo">Tipo</label>
            <select name="tipo" id="tipo" class="form-control" required>
                @foreach(\App\Models\Solicitacao::TIPOS as $tipo => $label)
                    <option value="{{ $tipo }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <div class="form-group">
        <label for="descricao">Descrição</label>
        <textarea name="descricao" id="descricao" class="form-control" rows="3" maxlength="3000" placeholder="Descreva o que você precisa, prazo ou outras informações úteis">{{ old('descricao') }}</textarea>
    </div>
    <div class="form-group">
        <label for="anexos">Anexos <span id="anexo-obrigatorio" class="badge badge-warning" style="display:none;">obrigatório</span></label>
        <input type="file" name="anexos[]" id="anexos" class="form-control-file" multiple accept=".jpg,.jpeg,.png,.pdf">
        <small class="text-muted">JPG, PNG ou PDF — até 8MB cada (máx. 5 arquivos)</small>
    </div>
    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-paper-plane mr-1"></i> Enviar à secretaria</button>
</form>

@push('js')
@if($tipos->isNotEmpty())
@php
    $tiposJson = $tipos->keyBy('id')->map(fn ($t) => [
        'descricao' => $t->descricao,
        'exige_anexo' => $t->exige_anexo,
        'campos' => $t->campos ?? [],
    ]);
@endphp
<script>
const tiposSolicitacao = {{ \Illuminate\Support\Js::from($tiposJson) }};

function campoInput(campo, i) {
    const name = 'campos[' + i + ']';
    const obrigatorio = !!campo.obrigatoria;
    const $group = $('<div class="form-group"></div>');
    const $label = $('<label></label>').text(campo.rotulo);
    if (obrigatorio) {
        $label.append(' ').append($('<span class="text-danger">*</span>'));
    }
    $group.append($label);

    let $input;
    if (campo.tipo === 'anexo') {
        $input = $('<input type="file" class="form-control-file">')
            .attr('accept', '.jpg,.jpeg,.png,.pdf,.doc,.docx');
        $group.append($('<small class="form-text text-muted">JPG, PNG, PDF ou Word — máx. 8 MB</small>'));
    } else if (campo.tipo === 'texto_longo') {
        $input = $('<textarea class="form-control" rows="3" maxlength="2000"></textarea>');
    } else if (campo.tipo === 'numero') {
        $input = $('<input type="number" class="form-control">');
    } else if (campo.tipo === 'data') {
        $input = $('<input type="date" class="form-control">');
    } else if (campo.tipo === 'selecao') {
        $input = $('<select class="form-control"></select>');
        $input.append($('<option value=""></option>').text('Selecione…'));
        (campo.opcoes || []).forEach(function (opcao) {
            $input.append($('<option></option>').attr('value', opcao).text(opcao));
        });
    } else {
        $input = $('<input type="text" class="form-control" maxlength="2000">');
    }

    $input.attr('name', name);
    if (obrigatorio) {
        $input.prop('required', true);
    }

    return $group.append($input);
}

function renderCamposTipo(tipoId) {
    const tipo = tiposSolicitacao[tipoId];
    const $campos = $('#campos-dinamicos').empty();
    const $instrucoes = $('#tipo-instrucoes').hide().empty();
    const $anexos = $('#anexos');
    const $badge = $('#anexo-obrigatorio').hide();

    $anexos.prop('required', false);

    if (!tipo) {
        return;
    }

    if (tipo.descricao) {
        $instrucoes.text(tipo.descricao).show();
    }

    (tipo.campos || []).forEach(function (campo, i) {
        $campos.append(campoInput(campo, i));
    });

    if (tipo.exige_anexo) {
        $anexos.prop('required', true);
        $badge.show();
    }
}

$('#tipo_id').on('change', function () {
    renderCamposTipo(this.value);
}).trigger('change');
</script>
@endif
@endpush
