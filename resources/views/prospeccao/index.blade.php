@extends('adminlte::page')

@section('title', 'Prospecção')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="m-0">Prospecção de Locadoras</h1>
            <p class="text-muted mb-0">Busque locadoras por estado e cidade usando OpenStreetMap + Overpass API.</p>
        </div>
    </div>
@endsection

@section('content')
    @php
        $cidadesSelecionadas = old('cidades', $listaCidades ?? []);
        $termosInput = old('termos', implode("\n", $listaTermos ?? ['locadora de veículos', 'aluguel de carros', 'locação de veículos', 'aluguel de vans']));
        $estados = $estados ?? [];
    @endphp

    @if (!empty($erroBusca))
        <div class="alert alert-danger">{{ $erroBusca }}</div>
    @endif

    <div class="row">
        <div class="col-12 col-xl-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title mb-0">Busca por região</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('prospeccao.index') }}" id="form-prospeccao">
                        <input type="hidden" name="buscar" value="1">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="estado">Estado</label>
                                    <select id="estado" name="estado" class="form-control">
                                        <option value="">Selecione</option>
                                        @foreach ($estados as $sigla => $nome)
                                            <option value="{{ $sigla }}" @selected(($estado ?? 'PE') === $sigla)>{{ $nome }} ({{ $sigla }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="cidades">Cidades</label>
                                    <select id="cidades" name="cidades[]" class="form-control select2-cidades" multiple></select>
                                    <small class="text-muted d-block mt-1">Selecione uma ou mais cidades do estado escolhido.</small>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label for="termos">Expressões de busca</label>
                                    <textarea id="termos" name="termos" class="form-control" rows="4" placeholder="Uma expressão por linha">{{ $termosInput }}</textarea>
                                    <small class="text-muted d-block mt-1">Cada cidade será consultada com cada expressão.</small>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center mt-3" style="gap:.75rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-1"></i> Buscar locadoras
                            </button>
                            <a href="{{ route('prospeccao.index') }}" class="btn btn-outline-secondary">Limpar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title mb-0">Status do módulo</h3>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Fonte:</strong> OpenStreetMap / Overpass API</p>
                    <p class="mb-2"><strong>Estados:</strong> 27 UFs do Brasil</p>
                    <p class="mb-2"><strong>Cidades:</strong> carregadas dinamicamente ao escolher o estado</p>
                    <p class="mb-0"><strong>Busca:</strong> retorna empresas encontradas por cidade e termo.</p>
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
                <thead>
                    <tr>
                        <th>Locadora</th>
                        <th>Cidade</th>
                        <th>Telefone</th>
                        <th>Site</th>
                        <th>Avaliação</th>
                        <th>Situação</th>
                        <th>Endereço</th>
                        <th>Mapa</th>
                        <th>Qtd. avaliações</th>
                        <th>Localizada em</th>
                    </tr>
                </thead>
                <tbody id="resultado-corpo">
                    @forelse ($resultados ?? [] as $resultado)
                        <tr>
                            <td>{{ $resultado['nome'] ?? '-' }}</td>
                            <td>{{ $resultado['cidade'] ?? '-' }}</td>
                            <td>{{ $resultado['telefone'] ?? 'Não informado' }}</td>
                            <td>{{ $resultado['site'] ?? 'Não informado' }}</td>
                            <td>{{ $resultado['avaliacao'] ?? 'Não disponível' }}</td>
                            <td><span class="badge badge-info">{{ $resultado['status_prospeccao'] ?? 'Nova' }}</span></td>
                            <td>{{ $resultado['endereco'] ?? '—' }}</td>
                            <td>
                                @if (!empty($resultado['google_maps_url']))
                                    <a href="{{ $resultado['google_maps_url'] }}" target="_blank" rel="noopener">Abrir</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $resultado['quantidade_avaliacoes'] ?? 'Não disponível' }}</td>
                            <td>{{ !empty($resultado['consultado_google_em']) ? \Carbon\Carbon::parse($resultado['consultado_google_em'])->format('d/m/Y H:i') : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                Faça uma busca para exibir as locadoras encontradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap4-theme/1.5.2/select2-bootstrap4.min.css">
    <style>
        .select2-container--bootstrap4 .select2-selection {
            border-radius: .25rem;
            min-height: calc(1.5em + .75rem + 2px);
        }

        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered {
            padding: .2rem .35rem;
        }

        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            margin-top: .2rem;
        }
    </style>
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
    <script>
        (function () {
            const estadoSelect = document.getElementById('estado');
            const cidadesSelect = document.getElementById('cidades');
            const form = document.getElementById('form-prospeccao');
            const resultadoCorpo = document.getElementById('resultado-corpo');
            const resultadoContador = document.getElementById('resultado-contador');
            const btnBuscar = form?.querySelector('button[type="submit"]');
            const cidadesSelecionadas = @json($cidadesSelecionadas);
            const cidadesUrlBase = @json(route('prospeccao.cidades', ['uf' => '__UF__']));
            const buscaUrl = @json(route('prospeccao.index'));

            function inicializarSelect2Cidades() {
                if (window.jQuery && typeof window.jQuery.fn.select2 === 'function') {
                    window.jQuery('.select2-cidades').select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        placeholder: 'Selecione as cidades',
                        closeOnSelect: false,
                    });
                }
            }

            async function carregarCidades(uf) {
                cidadesSelect.innerHTML = '<option value="">Carregando...</option>';
                cidadesSelect.disabled = true;

                if (!uf) {
                    cidadesSelect.innerHTML = '';
                    cidadesSelect.disabled = false;
                    return;
                }

                try {
                    const response = await fetch(cidadesUrlBase.replace('__UF__', uf), {
                        headers: { 'Accept': 'application/json' }
                    });

                    const data = await response.json();
                    cidadesSelect.innerHTML = '';

                    (Array.isArray(data) ? data : []).forEach(function (cidade) {
                        const option = document.createElement('option');
                        option.value = cidade.nome;
                        option.textContent = cidade.nome;
                        if (cidadesSelecionadas.includes(cidade.nome)) {
                            option.selected = true;
                        }
                        cidadesSelect.appendChild(option);
                    });

                    inicializarSelect2Cidades();
                } catch (error) {
                    cidadesSelect.innerHTML = '<option value="">Erro ao carregar cidades</option>';
                } finally {
                    cidadesSelect.disabled = false;
                }
            }

            function renderResultados(resultados) {
                resultadoCorpo.innerHTML = '';
                resultadoContador.textContent = `${Array.isArray(resultados) ? resultados.length : 0} resultado(s)`;

                if (!Array.isArray(resultados) || resultados.length === 0) {
                    resultadoCorpo.innerHTML = `
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                Nenhuma locadora encontrada para os filtros informados.
                            </td>
                        </tr>
                    `;
                    return;
                }

                resultados.forEach(function (resultado) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${resultado.nome ?? '-'}</td>
                        <td>${resultado.cidade ?? '-'}</td>
                        <td>${resultado.telefone ?? 'Não informado'}</td>
                        <td>${resultado.site ?? 'Não informado'}</td>
                        <td>${resultado.avaliacao ?? 'Não disponível'}</td>
                        <td><span class="badge badge-info">${resultado.status_prospeccao ?? 'Nova'}</span></td>
                        <td>${resultado.endereco ?? '—'}</td>
                        <td>${resultado.google_maps_url ? `<a href="${resultado.google_maps_url}" target="_blank" rel="noopener">Abrir</a>` : '-'}</td>
                        <td>${resultado.quantidade_avaliacoes ?? 'Não disponível'}</td>
                        <td>${resultado.consultado_google_em ? new Date(resultado.consultado_google_em).toLocaleString('pt-BR') : '—'}</td>
                    `;
                    resultadoCorpo.appendChild(tr);
                });
            }

            if (estadoSelect) {
                estadoSelect.addEventListener('change', function () {
                    carregarCidades(this.value);
                });
                carregarCidades(estadoSelect.value);
            }

            if (form) {
                form.addEventListener('submit', async function (event) {
                    event.preventDefault();

                    const cidadesSelecionadasAtuais = Array.from(cidadesSelect.selectedOptions)
                        .map(option => option.value)
                        .filter(Boolean);

                    if (cidadesSelecionadasAtuais.length === 0) {
                        renderResultados([]);
                        resultadoCorpo.innerHTML = `
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    Selecione pelo menos uma cidade para buscar.
                                </td>
                            </tr>
                        `;
                        return;
                    }

                    const formData = new FormData(form);
                    formData.delete('cidades[]');
                    cidadesSelecionadasAtuais.forEach(cidade => formData.append('cidades[]', cidade));
                    formData.set('buscar', '1');

                    if (btnBuscar) {
                        btnBuscar.disabled = true;
                        btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Buscando...';
                    }

                    try {
                        const response = await fetch(buscaUrl + '?' + new URLSearchParams(formData).toString(), {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Falha ao buscar locadoras.');
                        }

                        renderResultados(data.resultados || []);
                    } catch (error) {
                        resultadoCorpo.innerHTML = `
                            <tr>
                                <td colspan="10" class="text-center text-danger py-4">
                                    ${error.message || 'Erro ao executar a busca.'}
                                </td>
                            </tr>
                        `;
                    } finally {
                        if (btnBuscar) {
                            btnBuscar.disabled = false;
                            btnBuscar.innerHTML = '<i class="fas fa-search mr-1"></i> Buscar locadoras';
                        }
                    }
                });
            }
        })();
    </script>
@endsection
