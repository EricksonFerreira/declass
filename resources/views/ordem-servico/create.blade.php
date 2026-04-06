@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <h1 class="m-0 text-dark">{{ $titulo }}</h1>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="post" action="{{ route('ordem-servico.store') }}">
                @csrf
                <div class="form-group">
                    <label for="veiculo_id">Veículo <span class="text-danger">*</span></label>
                    <select name="veiculo_id" id="veiculo_id" class="form-control @error('veiculo_id') is-invalid @enderror" required>
                        <option value="">— Selecione —</option>
                        @foreach($veiculos as $v)
                            <option value="{{ $v->id }}" @selected((int) old('veiculo_id', $veiculoId) === (int) $v->id)>
                                {{ $v->nome }} ({{ $v->id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Avarias pendentes (sem correção e sem OS aberta)</label>
                    <div id="danos_container" class="border rounded p-3 bg-light" style="max-height:220px;overflow-y:auto;">
                        <p class="text-muted small mb-0">Selecione um veículo para listar as avarias.</p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reserva_id">Reserva (opcional)</label>
                    <input type="number" name="reserva_id" id="reserva_id" class="form-control @error('reserva_id') is-invalid @enderror"
                           value="{{ old('reserva_id') }}" min="1" placeholder="ID da reserva, se houver vínculo">
                </div>

                <div class="form-group">
                    <label for="observacoes">Observações da OS</label>
                    <textarea name="observacoes" id="observacoes" class="form-control @error('observacoes') is-invalid @enderror" rows="3">{{ old('observacoes') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="servicos_livres">Serviços adicionais (um por linha, sem vínculo com avaria)</label>
                    <textarea name="servicos_livres" id="servicos_livres" class="form-control @error('servicos_livres') is-invalid @enderror" rows="4"
                              placeholder="Ex: Troca de óleo&#10;Revisão geral">{{ old('servicos_livres') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Abrir ordem de serviço</button>
                <a href="{{ route('ordem-servico.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
(function () {
    const veiculoEl = document.getElementById('veiculo_id');
    const container = document.getElementById('danos_container');
    const preselected = @json(array_values(array_map('intval', $danoIds ?? [])));
    const danosListUrl = @json(route('ordem-servico.danos-pendentes'));

    function renderDanos(list) {
        container.innerHTML = '';
        if (!list.length) {
            container.innerHTML = '<p class="text-muted small mb-0">Nenhuma avaria pendente disponível para OS.</p>';
            return;
        }
        list.forEach(function (d) {
            const id = 'dano_cb_' + d.id;
            const wrap = document.createElement('div');
            wrap.className = 'custom-control custom-checkbox mb-1';
            const input = document.createElement('input');
            input.type = 'checkbox';
            input.className = 'custom-control-input';
            input.name = 'veiculo_dano_ids[]';
            input.value = d.id;
            input.id = id;
            if (preselected.indexOf(d.id) !== -1) input.checked = true;
            const label = document.createElement('label');
            label.className = 'custom-control-label';
            label.setAttribute('for', id);
            label.textContent = d.label;
            wrap.appendChild(input);
            wrap.appendChild(label);
            container.appendChild(wrap);
        });
    }

    function loadDanos() {
        const vid = veiculoEl.value;
        if (!vid) {
            container.innerHTML = '<p class="text-muted small mb-0">Selecione um veículo para listar as avarias.</p>';
            return;
        }
        container.innerHTML = '<p class="text-muted small mb-0">Carregando…</p>';
        const sep = danosListUrl.indexOf('?') >= 0 ? '&' : '?';
        const url = danosListUrl + sep + 'veiculo_id=' + encodeURIComponent(vid);
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(renderDanos)
            .catch(function () {
                container.innerHTML = '<p class="text-danger small mb-0">Erro ao carregar avarias.</p>';
            });
    }

    veiculoEl.addEventListener('change', loadDanos);
    if (veiculoEl.value) {
        loadDanos();
    }
})();
</script>
@stop
