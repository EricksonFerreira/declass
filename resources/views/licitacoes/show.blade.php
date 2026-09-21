@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@push('css')
    <style>
        .lic-hero { display:flex; flex-wrap:wrap; gap:16px; align-items:center; }
        .lic-score-big { font-size:2.1rem; font-weight:800; line-height:1; }
        .lic-score-big--alto { color:#16a34a; } .lic-score-big--medio { color:#d97706; } .lic-score-big--baixo { color:#dc2626; }
        .lic-viab { font-size:.95rem; font-weight:600; }
        .lic-crit-barra { height:8px; border-radius:4px; background:#e9ecef; overflow:hidden; min-width:90px; }
        .lic-crit-barra > span { display:block; height:100%; background:#0d6efd; }
        .exig-ok { color:#16a34a; } .exig-nok { color:#dc2626; }
        .lic-gap { border-left:3px solid #f59e0b; padding:6px 10px; background:#fffbeb; border-radius:0 6px 6px 0; margin-bottom:8px; }
        .lic-dl dt { font-size:.75rem; text-transform:uppercase; letter-spacing:.02em; color:#6c757d; font-weight:600; }
        .lic-dl dd { margin-bottom:.5rem; }
        .lic-arq-nome { max-width:60vw; }
        @media (min-width: 768px) {
            .lic-score-big { font-size:2.6rem; }
            .lic-hero { gap:24px; }
            .lic-arq-nome { max-width:420px; }
        }
        @media (max-width: 575.98px) {
            .card-header { padding:.6rem .75rem; }
            .card-body { padding:.75rem; }
            .table { font-size:.82rem; }
        }
    </style>
@endpush

@section('content')
    @php $analise = $licitacao->analises->first(); @endphp

    @if ($analise && ($analise->divergencia['detectada'] ?? false))
        <div class="alert alert-danger shadow-sm">
            <h5 class="mb-2"><i class="fas fa-exclamation-triangle mr-1"></i> Divergência crítica identificada</h5>
            <div class="small">
                Os dados cadastrados não correspondem ao documento anexado.<br>
                <strong>Cadastro:</strong> {{ $analise->divergencia['cadastro'] ?? '—' }}<br>
                <strong>Documento:</strong> {{ $analise->divergencia['documento'] ?? '—' }}<br>
                @if (!empty($analise->divergencia['resumo'])){{ $analise->divergencia['resumo'] }}<br>@endif
                <strong>Confirme o documento correto antes de considerar os resultados abaixo.</strong>
            </div>
        </div>
    @endif

    <div class="row">
        {{-- Análise primeiro no mobile, à direita no desktop --}}
        <div class="col-lg-4 order-1 order-lg-2">
            <div class="card card-outline card-success shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm"><i class="fas fa-robot mr-1"></i> Análise de pontuação</h3>
                </div>
                <div class="card-body">
                    @if ($analise)
                        <div class="lic-hero mb-3">
                            <div>
                                @php $cls = $analise->indice_aderencia >= 70 ? 'alto' : ($analise->indice_aderencia >= 40 ? 'medio' : 'baixo'); @endphp
                                <div class="lic-score-big lic-score-big--{{ $cls }}">{{ $analise->indice_aderencia !== null ? number_format($analise->indice_aderencia, 0) : '—' }}<small class="text-muted" style="font-size:1rem">/100</small></div>
                                <div class="text-muted small">índice de aderência</div>
                                <div class="text-muted" style="font-size:.68rem;max-width:150px">Índice interno do sistema — não é pontuação prevista no edital</div>
                            </div>
                            <div>
                                <div class="lic-viab">
                                    @if ($analise->viabilidade === 'viavel') <span class="badge badge-success p-2">Viável</span>
                                    @elseif ($analise->viabilidade === 'atencao') <span class="badge badge-warning p-2" title="Existem pendências documentais — o sistema não encontrou evidências suficientes">Habilitação não confirmada</span>
                                    @elseif ($analise->viabilidade === 'inviavel') <span class="badge badge-danger p-2">Inviável</span>
                                    @endif
                                </div>
                                @if ($analise->preco_max_edital || $analise->preco_sugerido || $analise->preco_motivo)
                                    <div class="mt-2 small">
                                        @if ($analise->preco_max_edital)
                                            <div class="text-muted">Teto do edital<br><strong class="text-dark">R$ {{ number_format((float) $analise->preco_max_edital, 2, ',', '.') }}</strong></div>
                                        @endif
                                        <div class="text-muted mt-1">Preço recomendado<br>
                                            @if ($analise->preco_sugerido)
                                                <strong class="text-dark">R$ {{ number_format((float) $analise->preco_sugerido, 2, ',', '.') }}</strong>
                                            @else
                                                <strong class="text-dark">Não calculável com segurança</strong>
                                            @endif
                                        </div>
                                        @if ($analise->preco_motivo)
                                            <div class="text-muted" style="font-size:.7rem">{{ $analise->preco_motivo }}</div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if ($analise->parecer)
                            <p class="small border-left pl-2" style="border-color:#0d6efd !important">{{ $analise->parecer }}</p>
                        @endif

                        <div class="text-muted small mb-2">
                            Analisado em {{ $analise->created_at->format('d/m/Y H:i') }}
                            por {{ $analise->usuario?->nome ?? 'sistema' }}
                        </div>
                    @else
                        <p class="text-muted small mb-2">Cole o texto do edital em "Edital" e rode a análise para estimar a aderência da empresa aos critérios de julgamento.</p>
                    @endif

                    <form method="POST" action="{{ route('licitacoes.analisar', $licitacao) }}" id="form-analisar">
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

        </div>

        {{-- Dados e edital: depois da análise no mobile, à esquerda no desktop --}}
        <div class="col-lg-8 order-2 order-lg-1">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:6px">
                    <h3 class="card-title text-bold text-sm mb-0"><i class="fas fa-gavel mr-1"></i> Dados da licitação</h3>
                    <div class="d-flex align-items-center flex-wrap" style="gap:6px">
                        <button type="button" id="btn-edit-licitacao" class="btn btn-xs btn-outline-primary">
                            <i class="fas fa-pen mr-1"></i>Editar
                        </button>
                        <form method="POST" action="{{ route('licitacoes.status', $licitacao) }}">
                            @csrf @method('PATCH')
                            <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                                @foreach ($statusOpcoes as $v => $label)
                                    <option value="{{ $v }}" {{ $licitacao->status === $v ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 lic-dl">
                        <dt class="col-4 col-sm-3">Órgão</dt><dd class="col-8 col-sm-9">{{ $licitacao->orgao }}</dd>
                        <dt class="col-4 col-sm-3">Objeto</dt><dd class="col-8 col-sm-9">{{ $licitacao->objeto }}</dd>
                        <dt class="col-4 col-sm-3">Edital nº</dt><dd class="col-8 col-sm-9">{{ $licitacao->numero_edital ?: '—' }}</dd>
                        <dt class="col-4 col-sm-3">Modalidade</dt><dd class="col-8 col-sm-9">{{ $licitacao->modalidade ?: '—' }}</dd>
                        <dt class="col-4 col-sm-3">Julgamento</dt><dd class="col-8 col-sm-9">{{ $licitacao->criterio_julgamento ?: '—' }}</dd>
                        <dt class="col-4 col-sm-3">Abertura</dt><dd class="col-8 col-sm-9">{{ $licitacao->data_abertura?->format('d/m/Y') ?: '—' }}</dd>
                        <dt class="col-4 col-sm-3">Valor estimado</dt><dd class="col-8 col-sm-9">{{ $licitacao->valor_estimado ? 'R$ '.number_format((float) $licitacao->valor_estimado, 2, ',', '.') : '—' }}</dd>
                        @if ($licitacao->url_edital)
                            <dt class="col-4 col-sm-3">Edital</dt>
                            <dd class="col-8 col-sm-9"><a href="{{ $licitacao->url_edital }}" target="_blank" rel="noopener" class="text-break">{{ $licitacao->url_edital }}</a></dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card card-outline card-secondary shadow-sm" data-collapse-mobile>
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title text-bold text-sm mb-0"><i class="fas fa-file-alt mr-1"></i> Edital</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body pb-2">
                    @if ($licitacao->arquivos)
                        <ul class="list-unstyled small mb-2">
                            @foreach ($licitacao->arquivos as $i => $arq)
                                <li class="d-flex align-items-center mb-1">
                                    <i class="fas fa-paperclip mr-1 text-muted"></i>
                                    <a href="{{ route('licitacoes.arquivo.download', [$licitacao, $i]) }}" class="mr-2 text-truncate lic-arq-nome">{{ $arq['nome'] ?? basename($arq['path']) }}</a>
                                    <form method="POST" action="{{ route('licitacoes.arquivo.excluir', [$licitacao, $i]) }}" onsubmit="return confirm('Remover este arquivo? O texto já extraído permanece — ajuste no campo abaixo se preciso.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-link btn-xs text-danger p-0" title="Remover"><i class="fas fa-times"></i></button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                        <small class="text-muted d-block mb-2">Para anexar mais arquivos, use o botão "Editar" acima.</small>
                    @endif
                    <form method="POST" action="{{ route('licitacoes.texto', $licitacao) }}" class="js-form-saving" data-label="Salvando…">
                        @csrf @method('PUT')
                        <textarea name="texto_edital" rows="8" class="form-control form-control-sm" placeholder="Cole aqui o texto do edital — ou anexe o arquivo pelo botão Editar. Ao menos as seções de critérios de julgamento, pontuação e habilitação. Quanto mais completo, melhor a análise.">{{ old('texto_edital', $licitacao->texto_edital) }}</textarea>
                        @error('texto_edital')<span class="text-danger small">{{ $message }}</span>@enderror
                        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap" style="gap:6px">
                            <small class="text-muted">{{ $licitacao->texto_edital ? number_format(mb_strlen($licitacao->texto_edital), 0, ',', '.').' caracteres' : 'Edital ainda não informado' }}</small>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save mr-1"></i>Salvar texto</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid 2×2: exigências, gaps, critérios e histórico com a mesma largura --}}
    <div class="row">
        @if ($analise && $analise->exigencias)
            <div class="col-md-6">
                <div class="card card-outline card-warning shadow-sm" data-collapse-mobile>
                    <div class="card-header">
                        <h3 class="card-title text-bold text-sm"><i class="fas fa-clipboard-check mr-1"></i> Habilitação — exigências do edital</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead><tr><th style="width:30px"></th><th>Exigência</th><th style="width:120px">Situação</th><th class="d-none d-md-table-cell">Evidência</th></tr></thead>
                                <tbody>
                                    @foreach ($analise->exigencias as $e)
                                        @php
                                            $sit = $e['situacao'] ?? (($e['atende'] ?? false) ? 'comprovado' : 'nao_comprovado');
                                            $sitMeta = [
                                                'comprovado' => ['fa-check-circle exig-ok', 'success', 'Comprovado'],
                                                'nao_comprovado' => ['fa-question-circle text-warning', 'warning', 'Não comprovado'],
                                                'nao_atende' => ['fa-times-circle exig-nok', 'danger', 'Não atende'],
                                            ][$sit] ?? ['fa-question-circle text-warning', 'warning', 'Não comprovado'];
                                        @endphp
                                        <tr>
                                            <td class="text-center align-middle"><i class="fas {{ $sitMeta[0] }}"></i></td>
                                            <td class="small">
                                                {{ $e['exigencia'] ?? '—' }}
                                                @if (!empty($e['evidencia']))
                                                    <div class="text-muted d-md-none" style="font-size:.72rem">{{ $e['evidencia'] }}</div>
                                                @endif
                                            </td>
                                            <td class="align-middle"><span class="badge badge-{{ $sitMeta[1] }}">{{ $sitMeta[2] }}</span></td>
                                            <td class="small text-muted d-none d-md-table-cell">{{ $e['evidencia'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 py-2 border-top text-muted" style="font-size:.72rem">
                            <i class="fas fa-question-circle text-warning"></i> <strong>Não comprovado</strong> = o sistema não encontrou evidência cadastrada — não significa que a empresa não possua o documento.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($analise && $analise->gaps)
            <div class="col-md-6">
                <div class="card card-outline card-danger shadow-sm" data-collapse-mobile>
                    <div class="card-header">
                        <h3 class="card-title text-bold text-sm"><i class="fas fa-exclamation-triangle mr-1"></i> Pendências e gaps</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach ($analise->gaps as $gap)
                            <div class="lic-gap small">{{ $gap }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if ($analise && $analise->criterios)
            @php $temPontos = collect($analise->criterios)->contains(fn ($c) => ($c['pontos_max'] ?? null) !== null); @endphp
            <div class="col-md-6">
                <div class="card card-outline card-info shadow-sm" data-collapse-mobile>
                    <div class="card-header">
                        <h3 class="card-title text-bold text-sm"><i class="fas fa-list-ol mr-1"></i> Critérios de julgamento</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Critério</th><th class="text-center" style="width:110px">{{ $temPontos ? 'Pts' : 'Avaliação' }}</th>@if ($temPontos)<th style="width:100px"></th>@endif</tr></thead>
                            <tbody>
                                @foreach ($analise->criterios as $c)
                                    <tr>
                                        <td>
                                            <div class="small font-weight-bold">{{ $c['criterio'] ?? '—' }}</div>
                                            @if (!empty($c['observacao']))<div class="text-muted" style="font-size:.75rem">{{ $c['observacao'] }}</div>@endif
                                        </td>
                                        <td class="text-center align-middle small">
                                            @if (($c['pontos_max'] ?? null) !== null)
                                                {{ $c['pontos_estimados'] ?? '—' }}<span class="text-muted">/{{ $c['pontos_max'] }}</span>
                                            @else
                                                @php $av = $c['avaliacao'] ?? 'indeterminada'; @endphp
                                                <span class="badge badge-{{ ['favoravel' => 'success', 'moderada' => 'warning', 'desfavoravel' => 'danger', 'indeterminada' => 'secondary'][$av] ?? 'secondary' }}">
                                                    {{ ['favoravel' => 'Favorável', 'moderada' => 'Moderada', 'desfavoravel' => 'Desfavorável', 'indeterminada' => 'Indeterminada'][$av] ?? $av }}
                                                </span>
                                            @endif
                                        </td>
                                        @if ($temPontos)
                                            <td class="align-middle">
                                                @php $pm = (float)($c['pontos_max'] ?? 0); $pe = (float)($c['pontos_estimados'] ?? 0); $pct = $pm > 0 ? min(100, round($pe / $pm * 100)) : 0; @endphp
                                                <div class="lic-crit-barra"><span style="width:{{ $pct }}%"></span></div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if ($licitacao->analises->count() > 1)
            <div class="col-md-6">
                <div class="card card-outline card-secondary shadow-sm collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title text-bold text-sm"><i class="fas fa-history mr-1"></i> Histórico de análises</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Data</th><th>Nota</th><th>Viabilidade</th><th>Analista</th></tr></thead>
                            <tbody>
                                @foreach ($licitacao->analises->skip(1) as $a)
                                    <tr>
                                        <td>{{ $a->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $a->nota_tecnica_estimada !== null ? number_format((float) $a->nota_tecnica_estimada, 0) : '—' }}</td>
                                        <td>{{ \App\Models\LicitacaoAnalise::VIABILIDADES[$a->viabilidade] ?? '—' }}</td>
                                        <td class="small">{{ $a->usuario?->nome ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <x-modal id="licEditModal" icon="fas fa-pen" title="Editar Licitação" formId="licitacao-edit-form" action="{{ route('licitacoes.update', $licitacao) }}" method="PUT" enctype="multipart/form-data">
        <input type="hidden" name="_form" value="edit">
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
            <label>Anexar arquivos ao edital</label>
            <div class="custom-file">
                <input type="file" name="arquivos[]" class="custom-file-input @error('arquivos') is-invalid @enderror" id="arquivo-edital-edit" accept=".pdf,.docx,.txt" multiple>
                <label class="custom-file-label" for="arquivo-edital-edit" data-browse="Procurar">{{ $licitacao->arquivos ? count($licitacao->arquivos).' arquivo(s) anexado(s)' : 'PDF, DOCX ou TXT…' }}</label>
            </div>
            @error('arquivos')<span class="text-danger small">{{ $message }}</span>@enderror
            @error('arquivos.*')<span class="text-danger small">{{ $message }}</span>@enderror
            <small class="text-muted">Os arquivos escolhidos são <strong>adicionados</strong> aos já anexados.</small>
        </div>
    </x-modal>
@stop

@section('js')
    <script>
        $(function() {
            // No mobile, cards secundários já nascem recolhidos
            if (window.matchMedia('(max-width: 767.98px)').matches) {
                $('[data-collapse-mobile]').each(function() {
                    var $card = $(this);
                    if (!$card.hasClass('collapsed-card')) {
                        $card.find('> .card-header [data-card-widget="collapse"]').first().trigger('click');
                    }
                });
            }

            $('.custom-file-input').on('change', function() {
                var rotulo = this.files.length === 0 ? 'PDF, DOCX ou TXT do edital…'
                    : (this.files.length === 1 ? this.files[0].name : this.files.length + ' arquivos selecionados');
                $(this).next('.custom-file-label').text(rotulo);
            });
            $('.js-form-saving').on('submit', function() {
                $(this).find('button[type=submit]').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin mr-1"></i>' + ($(this).data('label') || 'Salvando…'));
            });
            $('#licitacao-edit-form').on('submit', function() {
                $(this).find('button[type=submit]').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin mr-1"></i>Salvando…');
            });
            $('#form-analisar').on('submit', function(e) {
                if (!confirm('Rodar a análise de IA agora?')) {
                    e.preventDefault();
                    return;
                }
                $(this).find('button[type=submit]')
                    .prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin mr-1"></i>Processando…');
                $(this).append('<small class="text-muted d-block mt-2 text-center">A análise pode levar até 2 minutos — não feche a página.</small>');
            });
            $('#btn-edit-licitacao').on('click', function() {
                $('#licEditModalBackdrop').addClass('show');
                $('#licEditModal').addClass('open');
            });
            @if (old('_form') === 'edit' && $errors->any())
                $('#licEditModalBackdrop').addClass('show');
                $('#licEditModal').addClass('open');
            @endif
        });
    </script>
@stop
