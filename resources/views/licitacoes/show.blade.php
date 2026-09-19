@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@push('css')
    <style>
        .lic-hero { display:flex; flex-wrap:wrap; gap:24px; align-items:center; }
        .lic-score-big { font-size:2.6rem; font-weight:800; line-height:1; }
        .lic-score-big--alto { color:#16a34a; } .lic-score-big--medio { color:#d97706; } .lic-score-big--baixo { color:#dc2626; }
        .lic-viab { font-size:.95rem; font-weight:600; }
        .lic-crit-barra { height:8px; border-radius:4px; background:#e9ecef; overflow:hidden; min-width:120px; }
        .lic-crit-barra > span { display:block; height:100%; background:#0d6efd; }
        .exig-ok { color:#16a34a; } .exig-nok { color:#dc2626; }
        .lic-gap { border-left:3px solid #f59e0b; padding:6px 10px; background:#fffbeb; border-radius:0 6px 6px 0; margin-bottom:8px; }
    </style>
@endpush

@section('content')
    @php $analise = $licitacao->analises->first(); @endphp

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm"><i class="fas fa-gavel mr-1"></i> Dados da licitação</h3>
                    <div class="card-tools">
                        <button type="button" id="btn-edit-licitacao" class="btn btn-xs btn-outline-primary mr-1">
                            <i class="fas fa-pen mr-1"></i>Editar
                        </button>
                        <form method="POST" action="{{ route('licitacoes.status', $licitacao) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <select name="status" class="form-control form-control-sm d-inline-block" style="width:auto" onchange="this.form.submit()">
                                @foreach ($statusOpcoes as $v => $label)
                                    <option value="{{ $v }}" {{ $licitacao->status === $v ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Órgão</dt><dd class="col-sm-9">{{ $licitacao->orgao }}</dd>
                        <dt class="col-sm-3">Objeto</dt><dd class="col-sm-9">{{ $licitacao->objeto }}</dd>
                        <dt class="col-sm-3">Edital nº</dt><dd class="col-sm-9">{{ $licitacao->numero_edital ?: '—' }}</dd>
                        <dt class="col-sm-3">Modalidade</dt><dd class="col-sm-9">{{ $licitacao->modalidade ?: '—' }}</dd>
                        <dt class="col-sm-3">Julgamento</dt><dd class="col-sm-9">{{ $licitacao->criterio_julgamento ?: '—' }}</dd>
                        <dt class="col-sm-3">Abertura</dt><dd class="col-sm-9">{{ $licitacao->data_abertura?->format('d/m/Y') ?: '—' }}</dd>
                        <dt class="col-sm-3">Valor estimado</dt><dd class="col-sm-9">{{ $licitacao->valor_estimado ? 'R$ '.number_format((float) $licitacao->valor_estimado, 2, ',', '.') : '—' }}</dd>
                        @if ($licitacao->url_edital)
                            <dt class="col-sm-3">Edital</dt>
                            <dd class="col-sm-9"><a href="{{ $licitacao->url_edital }}" target="_blank" rel="noopener">{{ $licitacao->url_edital }}</a></dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm"><i class="fas fa-file-alt mr-1"></i> Edital</h3>
                    @if ($licitacao->arquivo_path)
                        <div class="card-tools">
                            <a href="{{ route('licitacoes.arquivo.download', $licitacao) }}" class="btn btn-xs btn-outline-secondary">
                                <i class="fas fa-download mr-1"></i>{{ basename($licitacao->arquivo_path) }}
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body pb-2">
                    <form method="POST" action="{{ route('licitacoes.arquivo', $licitacao) }}" enctype="multipart/form-data" class="mb-3">
                        @csrf
                        <div class="input-group input-group-sm">
                            <div class="custom-file">
                                <input type="file" name="arquivo" class="custom-file-input" id="arquivo-edital" accept=".pdf,.docx,.txt" required>
                                <label class="custom-file-label" for="arquivo-edital" data-browse="Procurar">PDF, DOCX ou TXT do edital…</label>
                            </div>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-upload mr-1"></i>Anexar e extrair texto</button>
                            </div>
                        </div>
                        @error('arquivo')<span class="text-danger small">{{ $message }}</span>@enderror
                        <small class="text-muted d-block mt-1">O texto é extraído automaticamente para o campo abaixo — revise antes de analisar.</small>
                    </form>
                    <form method="POST" action="{{ route('licitacoes.texto', $licitacao) }}">
                        @csrf @method('PUT')
                        <textarea name="texto_edital" rows="10" class="form-control form-control-sm" placeholder="Cole aqui o texto do edital — ou anexe o arquivo acima. Ao menos as seções de critérios de julgamento, pontuação e habilitação. Quanto mais completo, melhor a análise.">{{ old('texto_edital', $licitacao->texto_edital) }}</textarea>
                        @error('texto_edital')<span class="text-danger small">{{ $message }}</span>@enderror
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">{{ $licitacao->texto_edital ? number_format(mb_strlen($licitacao->texto_edital), 0, ',', '.').' caracteres' : 'Edital ainda não informado' }}</small>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save mr-1"></i>Salvar texto</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-outline card-success shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm"><i class="fas fa-robot mr-1"></i> Análise de pontuação</h3>
                </div>
                <div class="card-body">
                    @if ($analise)
                        <div class="lic-hero mb-3">
                            <div>
                                @php $cls = $analise->nota_tecnica_estimada >= 70 ? 'alto' : ($analise->nota_tecnica_estimada >= 40 ? 'medio' : 'baixo'); @endphp
                                <div class="lic-score-big lic-score-big--{{ $cls }}">{{ $analise->nota_tecnica_estimada !== null ? number_format((float) $analise->nota_tecnica_estimada, 0) : '—' }}<small class="text-muted" style="font-size:1rem">/100</small></div>
                                <div class="text-muted small">nota técnica estimada</div>
                            </div>
                            <div>
                                <div class="lic-viab">
                                    @if ($analise->viabilidade === 'viavel') <span class="badge badge-success p-2">Viável</span>
                                    @elseif ($analise->viabilidade === 'atencao') <span class="badge badge-warning p-2">Viável com ressalvas</span>
                                    @elseif ($analise->viabilidade === 'inviavel') <span class="badge badge-danger p-2">Inviável</span>
                                    @endif
                                </div>
                                @if ($analise->preco_sugerido)
                                    <div class="mt-2 small text-muted">Preço sugerido<br><strong class="text-dark">R$ {{ number_format((float) $analise->preco_sugerido, 2, ',', '.') }}</strong></div>
                                @endif
                            </div>
                        </div>

                        @if ($analise->parecer)
                            <p class="small border-left pl-2" style="border-color:#0d6efd !important">{{ $analise->parecer }}</p>
                        @endif

                        <div class="text-muted small mb-2">
                            Analisado em {{ $analise->created_at->format('d/m/Y H:i') }}
                            por {{ $analise->usuario?->nome ?? 'sistema' }}
                            · {{ $analise->modelo }}
                        </div>
                    @else
                        <p class="text-muted small mb-2">Cole o texto do edital ao lado e rode a análise para estimar a nota técnica da empresa contra os critérios de julgamento.</p>
                    @endif

                    <form method="POST" action="{{ route('licitacoes.analisar', $licitacao) }}" onsubmit="return confirm('Rodar a análise de IA agora?')">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block btn-sm" {{ empty($licitacao->texto_edital) ? 'disabled' : '' }}>
                            <i class="fas fa-bolt mr-1"></i>{{ $analise ? 'Reanalisar' : 'Analisar edital' }}
                        </button>
                        @if (empty($licitacao->texto_edital))
                            <small class="text-muted d-block mt-1">Salve o texto do edital para habilitar.</small>
                        @endif
                    </form>
                </div>
            </div>

            @if ($analise && $analise->criterios)
                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header"><h3 class="card-title text-bold text-sm">Critérios de pontuação</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Critério</th><th class="text-center">Pts</th><th style="width:110px"></th></tr></thead>
                            <tbody>
                                @foreach ($analise->criterios as $c)
                                    <tr>
                                        <td>
                                            <div class="small font-weight-bold">{{ $c['criterio'] ?? '—' }}</div>
                                            @if (!empty($c['observacao']))<div class="text-muted" style="font-size:.75rem">{{ $c['observacao'] }}</div>@endif
                                        </td>
                                        <td class="text-center align-middle small">
                                            {{ $c['pontos_estimados'] ?? '—' }}<span class="text-muted">/{{ $c['pontos_max'] ?? '?' }}</span>
                                        </td>
                                        <td class="align-middle">
                                            @php $pm = (float)($c['pontos_max'] ?? 0); $pe = (float)($c['pontos_estimados'] ?? 0); $pct = $pm > 0 ? min(100, round($pe / $pm * 100)) : 0; @endphp
                                            <div class="lic-crit-barra"><span style="width:{{ $pct }}%"></span></div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($analise && ($analise->exigencias || $analise->gaps))
        <div class="row">
            @if ($analise->exigencias)
                <div class="col-lg-7">
                    <div class="card card-outline card-warning shadow-sm">
                        <div class="card-header"><h3 class="card-title text-bold text-sm"><i class="fas fa-clipboard-check mr-1"></i> Habilitação — exigências do edital</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <thead><tr><th style="width:30px"></th><th>Exigência</th><th>Evidência</th></tr></thead>
                                <tbody>
                                    @foreach ($analise->exigencias as $e)
                                        <tr>
                                            <td class="text-center align-middle">
                                                <i class="fas {{ ($e['atende'] ?? false) ? 'fa-check-circle exig-ok' : 'fa-times-circle exig-nok' }}"></i>
                                            </td>
                                            <td class="small">{{ $e['exigencia'] ?? '—' }}</td>
                                            <td class="small text-muted">{{ $e['evidencia'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
            @if ($analise->gaps)
                <div class="col-lg-5">
                    <div class="card card-outline card-danger shadow-sm">
                        <div class="card-header"><h3 class="card-title text-bold text-sm"><i class="fas fa-exclamation-triangle mr-1"></i> Pendências e gaps</h3></div>
                        <div class="card-body">
                            @foreach ($analise->gaps as $gap)
                                <div class="lic-gap small">{{ $gap }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if ($licitacao->analises->count() > 1)
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header"><h3 class="card-title text-bold text-sm">Histórico de análises</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Data</th><th>Nota</th><th>Viabilidade</th><th>Modelo</th><th>Analista</th></tr></thead>
                    <tbody>
                        @foreach ($licitacao->analises->skip(1) as $a)
                            <tr>
                                <td>{{ $a->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $a->nota_tecnica_estimada !== null ? number_format((float) $a->nota_tecnica_estimada, 0) : '—' }}</td>
                                <td>{{ \App\Models\LicitacaoAnalise::VIABILIDADES[$a->viabilidade] ?? '—' }}</td>
                                <td class="small text-muted">{{ $a->modelo }}</td>
                                <td class="small">{{ $a->usuario?->nome ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    <x-modal id="licEditModal" icon="fas fa-pen" title="Editar Licitação" formId="licitacao-edit-form" action="{{ route('licitacoes.update', $licitacao) }}" method="PUT" enctype="multipart/form-data">
        <div class="form-group">
            <label>Órgão licitante <span class="text-danger">*</span></label>
            <input type="text" name="orgao" class="form-control form-control-sm @error('orgao') is-invalid @enderror" value="{{ old('orgao', $licitacao->orgao) }}" required>
            @error('orgao')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="form-group">
            <label>Objeto <span class="text-danger">*</span></label>
            <textarea name="objeto" rows="3" class="form-control form-control-sm @error('objeto') is-invalid @enderror" required>{{ old('objeto', $licitacao->objeto) }}</textarea>
            @error('objeto')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label>Nº do edital</label>
                <input type="text" name="numero_edital" class="form-control form-control-sm" value="{{ old('numero_edital', $licitacao->numero_edital) }}">
            </div>
            <div class="col-md-6 form-group">
                <label>Modalidade</label>
                <select name="modalidade" class="form-control form-control-sm">
                    <option value="">—</option>
                    @foreach (['Pregão Eletrônico', 'Pregão Presencial', 'Concorrência', 'Concurso', 'Diálogo Competitivo', 'Dispensa', 'Inexigibilidade', 'Leilão'] as $m)
                        <option value="{{ $m }}" {{ old('modalidade', $licitacao->modalidade) === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label>Critério de julgamento</label>
                <select name="criterio_julgamento" class="form-control form-control-sm">
                    <option value="">—</option>
                    @foreach (['Menor preço', 'Maior desconto', 'Melhor técnica', 'Técnica e preço', 'Maior lance', 'Maior retorno econômico'] as $c)
                        <option value="{{ $c }}" {{ old('criterio_julgamento', $licitacao->criterio_julgamento) === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label>Data de abertura</label>
                <input type="date" name="data_abertura" class="form-control form-control-sm" value="{{ old('data_abertura', $licitacao->data_abertura?->format('Y-m-d')) }}">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label>Valor estimado (R$)</label>
                <input type="number" step="0.01" min="0" name="valor_estimado" class="form-control form-control-sm" value="{{ old('valor_estimado', $licitacao->valor_estimado) }}">
            </div>
            <div class="col-md-6 form-group">
                <label>Link do edital/portal</label>
                <input type="url" name="url_edital" class="form-control form-control-sm" value="{{ old('url_edital', $licitacao->url_edital) }}" placeholder="https://...">
            </div>
        </div>
        <div class="form-group mb-0">
            <label>Substituir arquivo do edital</label>
            <div class="custom-file">
                <input type="file" name="arquivo" class="custom-file-input @error('arquivo') is-invalid @enderror" id="arquivo-edital-edit" accept=".pdf,.docx,.txt">
                <label class="custom-file-label" for="arquivo-edital-edit" data-browse="Procurar">{{ $licitacao->arquivo_path ? basename($licitacao->arquivo_path) : 'PDF, DOCX ou TXT…' }}</label>
            </div>
            @error('arquivo')<span class="text-danger small">{{ $message }}</span>@enderror
            <small class="text-muted">Em branco mantém o arquivo atual.</small>
        </div>
    </x-modal>
@stop

@section('js')
    <script>
        $(function() {
            $('.custom-file-input').on('change', function() {
                $(this).next('.custom-file-label').text(this.files[0] ? this.files[0].name : 'PDF, DOCX ou TXT do edital…');
            });
            $('#btn-edit-licitacao').on('click', function() {
                $('#licEditModalBackdrop').addClass('show');
                $('#licEditModal').addClass('open');
            });
            @if ($errors->hasAny(['orgao', 'objeto', 'numero_edital', 'modalidade', 'criterio_julgamento', 'data_abertura', 'valor_estimado', 'url_edital', 'arquivo']))
                $('#licEditModalBackdrop').addClass('show');
                $('#licEditModal').addClass('open');
            @endif
        });
    </script>
@stop
