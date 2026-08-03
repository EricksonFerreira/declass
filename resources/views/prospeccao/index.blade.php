@extends('adminlte::page')

@section('title', 'Prospecção')

@section('content_header')
    <div>
        <h1 class="m-0">Prospecção de Locadoras</h1>
        <p class="text-muted mb-0">Busque locadoras por estado e cidade usando OpenStreetMap.</p>
    </div>
@endsection

@section('content')
    @php
        $cidadesSelecionadas = old('cidades', $listaCidades ?? []);
        $estados = $estados ?? [];
    @endphp

    @if (!empty($erroBusca))
        <div class="alert alert-danger">{{ $erroBusca }}</div>
    @endif

    <div class="row">
        <div class="col-12 col-xl-8">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title mb-0">Busca por região</h3></div>
                <div class="card-body">
                    <form method="GET" action="{{ route('prospeccao.index') }}" id="form-prospeccao">
                        <input type="hidden" name="buscar" value="1">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="estado">Estado</label>
                                    <select id="estado" name="estado" class="form-control" required>
                                        <option value="">Selecione</option>
                                        @foreach ($estados as $sigla => $nome)
                                            <option value="{{ $sigla }}" @selected(($estado ?? 'PE') === $sigla)>{{ $nome }} ({{ $sigla }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group mb-0">
                                    <label for="cidades">Cidades</label>
                                    <select id="cidades" name="cidades[]" class="form-control select2-cidades" multiple required></select>
                                    <small class="text-muted d-block mt-1">Selecione uma ou mais cidades. As categorias de locação são pesquisadas automaticamente.</small>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center mt-3" style="gap:.75rem;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Buscar locadoras</button>
                            <a href="{{ route('prospeccao.index') }}" class="btn btn-outline-secondary">Limpar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title mb-0">Como funciona</h3></div>
                <div class="card-body">
                    <p class="mb-2"><strong>Fonte:</strong> OpenStreetMap / Overpass API</p>
                    <p class="mb-2"><strong>Região:</strong> estado e cidades selecionadas</p>
                    <p class="mb-0"><strong>Busca automática:</strong> estabelecimentos classificados como locadoras de carros.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;">
            <h3 class="card-title mb-0">Resultados</h3>
            <div class="text-muted small" id="resultado-contador">{{ count($resultados ?? []) }} resultado(s)</div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered mb-0">
                <thead><tr><th>Locadora</th><th>Cidade</th><th>Telefone</th><th>Site</th><th>Situação</th><th>Endereço</th><th>Mapa</th><th>Localizada em</th></tr></thead>
                <tbody id="resultado-corpo">
                    @forelse ($resultados ?? [] as $resultado)
                        <tr>
                            <td>{{ $resultado['nome'] ?? '-' }}</td><td>{{ $resultado['cidade'] ?? '-' }}</td>
                            <td>{{ $resultado['telefone'] ?? 'Não informado' }}</td><td>{{ $resultado['site'] ?? 'Não informado' }}</td>
                            <td><span class="badge badge-info">{{ $resultado['status_prospeccao'] ?? 'Nova' }}</span></td>
                            <td>{{ $resultado['endereco'] ?? '—' }}</td>
                            <td>@if (!empty($resultado['google_maps_url']))<a href="{{ $resultado['google_maps_url'] }}" target="_blank" rel="noopener">Abrir</a>@else - @endif</td>
                            <td>{{ !empty($resultado['consultado_google_em']) ? \Carbon\Carbon::parse($resultado['consultado_google_em'])->format('d/m/Y H:i') : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Faça uma busca para exibir as locadoras encontradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap4-theme/1.5.2/select2-bootstrap4.min.css">
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
    <script>
        (function () {
            const estadoSelect = document.getElementById('estado');
            const cidadesSelect = document.getElementById('cidades');
            const form = document.getElementById('form-prospeccao');
            const corpo = document.getElementById('resultado-corpo');
            const contador = document.getElementById('resultado-contador');
            const botao = form.querySelector('button[type="submit"]');
            const cidadesIniciais = @json($cidadesSelecionadas);
            const cidadesUrl = @json(route('prospeccao.cidades', ['uf' => '__UF__']));
            const buscaUrl = @json(route('prospeccao.index'));

            function escapar(valor) {
                const div = document.createElement('div');
                div.textContent = valor == null ? '' : String(valor);
                return div.innerHTML;
            }

            function iniciarSelect2() {
                const select = window.jQuery(cidadesSelect);
                if (select.hasClass('select2-hidden-accessible')) select.select2('destroy');
                select.select2({theme: 'bootstrap4', width: '100%', placeholder: 'Selecione as cidades', closeOnSelect: false});
            }

            async function carregarCidades(uf, selecionadas = []) {
                cidadesSelect.disabled = true;
                cidadesSelect.innerHTML = '<option>Carregando...</option>';

                if (!uf) {
                    cidadesSelect.innerHTML = '';
                    cidadesSelect.disabled = false;
                    iniciarSelect2();
                    return;
                }

                try {
                    const response = await fetch(cidadesUrl.replace('__UF__', encodeURIComponent(uf)), {headers: {'Accept': 'application/json'}});
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'Erro ao carregar cidades.');
                    cidadesSelect.innerHTML = '';
                    data.forEach(cidade => cidadesSelect.add(new Option(cidade.nome, cidade.nome, false, selecionadas.includes(cidade.nome))));
                } catch (erro) {
                    cidadesSelect.innerHTML = `<option>${escapar(erro.message)}</option>`;
                } finally {
                    cidadesSelect.disabled = false;
                    iniciarSelect2();
                }
            }

            function renderizar(resultados) {
                contador.textContent = `${resultados.length} resultado(s)`;
                if (!resultados.length) {
                    corpo.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Nenhuma locadora cadastrada no OpenStreetMap foi encontrada.</td></tr>';
                    return;
                }
                corpo.innerHTML = resultados.map(item => `<tr>
                    <td>${escapar(item.nome || '-')}</td><td>${escapar(item.cidade || '-')}</td>
                    <td>${escapar(item.telefone || 'Não informado')}</td><td>${escapar(item.site || 'Não informado')}</td>
                    <td><span class="badge badge-info">${escapar(item.status_prospeccao || 'Nova')}</span></td>
                    <td>${escapar(item.endereco || '—')}</td>
                    <td>${item.google_maps_url ? `<a href="${escapar(item.google_maps_url)}" target="_blank" rel="noopener">Abrir</a>` : '-'}</td>
                    <td>${item.consultado_google_em ? new Date(item.consultado_google_em).toLocaleString('pt-BR') : '—'}</td>
                </tr>`).join('');
            }

            estadoSelect.addEventListener('change', () => carregarCidades(estadoSelect.value));
            carregarCidades(estadoSelect.value, cidadesIniciais);

            form.addEventListener('submit', async function (evento) {
                evento.preventDefault();
                const cidades = Array.from(cidadesSelect.selectedOptions).map(option => option.value).filter(Boolean);
                if (!cidades.length) {
                    corpo.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4">Selecione pelo menos uma cidade.</td></tr>';
                    return;
                }

                const parametros = new URLSearchParams({estado: estadoSelect.value, buscar: '1'});
                cidades.forEach(cidade => parametros.append('cidades[]', cidade));
                botao.disabled = true;
                botao.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Buscando...';

                try {
                    const response = await fetch(`${buscaUrl}?${parametros}`, {headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'Falha ao buscar locadoras.');
                    renderizar(data.resultados || []);
                } catch (erro) {
                    corpo.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">${escapar(erro.message || 'Erro ao executar a busca.')}</td></tr>`;
                } finally {
                    botao.disabled = false;
                    botao.innerHTML = '<i class="fas fa-search mr-1"></i> Buscar locadoras';
                }
            });
        })();
    </script>
@endsection
