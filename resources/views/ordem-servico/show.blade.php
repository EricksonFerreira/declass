@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
@php
    $statusSlug = strtolower((string) ($ordem_servico->status ?? 'aberta'));
    $statusClass = match ($statusSlug) {
        \App\Models\OrdemServico::STATUS_CONCLUIDA => 'is-success',
        \App\Models\OrdemServico::STATUS_CANCELADA => 'is-danger',
        \App\Models\OrdemServico::STATUS_EM_ANDAMENTO => 'is-primary',
        default => 'is-warning',
    };
    $statusIcon = match ($statusSlug) {
        \App\Models\OrdemServico::STATUS_CONCLUIDA => 'fas fa-check-circle',
        \App\Models\OrdemServico::STATUS_CANCELADA => 'fas fa-ban',
        \App\Models\OrdemServico::STATUS_EM_ANDAMENTO => 'fas fa-spinner',
        default => 'fas fa-folder-open',
    };
@endphp

<div class="rs-page os-show-page">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rs-hero rs-hero--{{ $statusSlug }} mb-4">
        <div class="rs-hero__accent"></div>
        <div class="rs-hero__body">
            <div class="rs-hero__left">
                <div class="rs-hero__eyebrow">
                    <i class="fas fa-tools"></i>
                    Ordem de serviço #{{ $ordem_servico->numero ?? $ordem_servico->id }}
                </div>
                <h1 class="rs-hero__title">{{ $titulo }}</h1>
                <div class="rs-hero__meta">
                    <span><i class="fas fa-car"></i>{{ optional($ordem_servico->veiculo)->nome ?? ('Veículo #' . $ordem_servico->veiculo_id) }}</span>
                    <span><i class="fas fa-user"></i>{{ optional($ordem_servico->usuarioAbertura)->name ?? 'Usuário não informado' }}</span>
                    @if($ordem_servico->sinistro_id)
                        <span><i class="fas fa-exclamation-triangle text-warning"></i>
                            <a href="{{ route('sinistro.show', $ordem_servico->sinistro_id) }}" class="text-white font-weight-bold text-decoration-underline" style="color: #fbbf24 !important;">
                                Sinistro #{{ $ordem_servico->sinistro_id }}
                            </a>
                        </span>
                    @endif
                </div>
            </div>
            <div class="rs-hero__right">
                <span class="rs-status-badge {{ $statusClass }}">
                    <i class="{{ $statusIcon }}"></i>
                    {{ strtoupper(str_replace('_', ' ', $statusSlug)) }}
                </span>
                <div class="rs-hero__kpis">
                    <div class="rs-kpi">
                        <span>Itens</span>
                        <strong>{{ $ordem_servico->itens->count() }}</strong>
                    </div>
                    <div class="rs-kpi-divider"></div>
                    <div class="rs-kpi">
                        <span>Reserva</span>
                        <strong>{{ $ordem_servico->reserva_id ? '#' . $ordem_servico->reserva_id : '-' }}</strong>
                    </div>
                </div>
                <div class="rs-hero__actions">
                    <a class="rs-btn rs-btn--outline" href="{{ route('ordem-servico.pdf', $ordem_servico) }}" target="_blank" rel="noopener noreferrer">
                        <i class="fas fa-file-pdf text-danger"></i> Exportar PDF
                    </a>
                    <a class="rs-btn rs-btn--outline" href="{{ route('ordem-servico.index') }}">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Se a OS estiver aberta, permite atualizar status / cancelar -->
    @if($ordem_servico->podeAlterarStatus())
        <div class="card mb-4 border-0 p-3 bg-light" style="border-radius: 12px;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="font-weight-bold mb-0">Controles operacionais da OS</h5>
                    <p class="text-xs text-muted mb-0">Altere o status ou cancele a ordem de serviço enquanto ela está aberta.</p>
                </div>
                <div class="d-flex gap-2">
                    @if($ordem_servico->status === \App\Models\OrdemServico::STATUS_ABERTA)
                        <form method="post" action="{{ route('ordem-servico.status', $ordem_servico) }}">
                            @csrf
                            <input type="hidden" name="status" value="{{ \App\Models\OrdemServico::STATUS_EM_ANDAMENTO }}">
                            <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3" style="border-radius: 8px;">
                                <i class="fas fa-play mr-1"></i> Marcar em andamento
                            </button>
                        </form>
                    @endif
                    <form method="post" action="{{ route('ordem-servico.status', $ordem_servico) }}" onsubmit="return confirm('Confirma o cancelamento desta OS? As avarias vinculadas voltarão para o status Pendente.');">
                        @csrf
                        <input type="hidden" name="status" value="{{ \App\Models\OrdemServico::STATUS_CANCELADA }}">
                        <button type="submit" class="btn btn-sm btn-outline-danger font-weight-bold px-3" style="border-radius: 8px;">
                            <i class="fas fa-ban mr-1"></i> Cancelar OS
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Form principal de conclusão (engloba as 4 seções) -->
    @if($ordem_servico->podeConcluir())
        <form method="post" action="{{ route('ordem-servico.concluir', $ordem_servico) }}" enctype="multipart/form-data">
            @csrf
    @endif

    <div class="row">
        <div class="col-12">
            
            <!-- SEÇÃO A: MOTIVOS DA ORDEM (AVARIAS) -->
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-exclamation-circle text-warning mr-2"></i> A. Motivos da Ordem (Avarias Vinculadas)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="px-4">Avaria</th>
                                    <th>Local</th>
                                    <th>Origem</th>
                                    <th>Situação</th>
                                    @if($ordem_servico->podeConcluir())
                                        <th>Resolução / Laudo na Conclusão</th>
                                    @else
                                        <th>Resultado</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @php($hasAvarias = false)
                                @foreach($ordem_servico->itens as $item)
                                    @if($item->veiculo_dano_id && $item->veiculoDano)
                                        @php($hasAvarias = true)
                                        @php($dano = $item->veiculoDano)
                                        <tr>
                                            <td class="px-4 py-3 align-middle">
                                                <span class="font-weight-bold text-dark">#{{ $dano->id }} - {{ $dano->tipo_dano ?? 'Avaria geral' }}</span>
                                            </td>
                                            <td class="align-middle">{{ $dano->local_dano ? strtoupper($dano->local_dano) : '-' }}</td>
                                            <td class="align-middle">
                                                @if($dano->reservaCodigo)
                                                    <span class="text-muted">Reserva #{{ $dano->reservaCodigo->codigo }}</span>
                                                @elseif($dano->contratoCheckin)
                                                    <span class="text-muted">Check-in</span>
                                                @else
                                                    <span class="text-muted">Manual</span>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge badge-light text-warning border px-2 py-1" style="border-radius: 6px;">
                                                    <i class="fas fa-wrench mr-1"></i> {{ ucfirst(str_replace('_', ' ', $dano->status)) }}
                                                </span>
                                            </td>
                                            <td class="py-3">
                                                @if($ordem_servico->podeConcluir())
                                                    <div class="d-flex flex-column gap-2" style="max-width: 320px;">
                                                        <select name="avarias_status[{{ $dano->id }}]" class="form-control form-control-sm font-weight-bold">
                                                            <option value="corrigida">Corrigida completamente</option>
                                                            <option value="parcialmente_corrigida">Corrigida parcialmente</option>
                                                            <option value="pendente">Permanece pendente</option>
                                                            <option value="descartada">Correção não necessária / Descartada</option>
                                                        </select>
                                                        <input type="text" name="avarias_obs[{{ $dano->id }}]" class="form-control form-control-sm mt-1" placeholder="Observação específica (opcional)">
                                                    </div>
                                                @else
                                                    <span class="text-dark">{{ $item->resultado ?: 'Sem resultado' }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach

                                @if(!$hasAvarias)
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Nenhuma avaria vinculada como motivo desta OS.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SEÇÃO B: SERVIÇOS PREVISTOS -->
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-clipboard-list text-primary mr-2"></i> B. Serviços Adicionais Previstos
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="os-box-list p-3 bg-white" style="border-radius: 8px; border: 1px solid #e2e8f0;">
                        @php($hasPrevistos = false)
                        @foreach($ordem_servico->itens as $item)
                            @if(!$item->veiculo_dano_id)
                                @php($hasPrevistos = true)
                                <div class="custom-control custom-checkbox mb-2 d-flex align-items-center py-2" style="border-bottom: 1px solid #f1f5f9;">
                                    @if($ordem_servico->podeConcluir())
                                        <input type="checkbox" class="custom-control-input" id="item_check_{{ $item->id }}" name="itens_completados[]" value="{{ $item->id }}" checked>
                                        <label class="custom-control-label font-weight-bold text-dark ml-2" for="item_check_{{ $item->id }}">
                                            {{ $item->descricao_servico }}
                                            @if($item->resultado)
                                                <small class="text-muted d-block font-weight-normal">{{ $item->resultado }}</small>
                                            @endif
                                        </label>
                                    @else
                                        <i class="fas {{ str_contains($item->resultado, 'Executado') ? 'fa-check-square text-success' : 'fa-minus-square text-muted' }} mr-2 fa-lg"></i>
                                        <span class="font-weight-semibold text-dark">{{ $item->descricao_servico }}</span>
                                        <span class="badge badge-light border ml-3">{{ $item->resultado ?: 'Planejado' }}</span>
                                    @endif
                                </div>
                            @endif
                        @endforeach

                        @if(!$hasPrevistos)
                            <p class="text-muted small mb-0">Nenhum serviço previsto cadastrado na abertura desta OS.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- SEÇÃO C: LANÇAMENTOS DA EXECUÇÃO -->
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-file-invoice-dollar text-success mr-2"></i> C. Lançamentos da Execução (Peças, Materiais e Mão de Obra)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <!-- Peças e materiais -->
                        <div class="col-lg-6 mb-4">
                            <h6 class="font-weight-bold text-secondary mb-3"><i class="fas fa-box-open mr-1"></i> Peças e Materiais Utilizados</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="table_pecas_executadas">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Item / Peça</th>
                                            <th class="text-center" style="width: 80px;">Qtd</th>
                                            <th class="text-right" style="width: 120px;">Vlr. Unitário</th>
                                            <th class="text-right" style="width: 120px;">Total</th>
                                            @if($ordem_servico->podeConcluir())
                                                <th style="width: 40px;"></th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($ordem_servico->itens->where('tipo_item', 'peca') as $execPeca)
                                            <tr>
                                                <td>{{ $execPeca->descricao_servico }}</td>
                                                <td class="text-center">{{ $execPeca->quantidade }}</td>
                                                <td class="text-right">R$ {{ number_format($execPeca->valor_unitario, 2, ',', '.') }}</td>
                                                <td class="text-right font-weight-bold">R$ {{ number_format($execPeca->valor_total, 2, ',', '.') }}</td>
                                                @if($ordem_servico->podeConcluir())
                                                    <td></td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        @if($ordem_servico->podeConcluir())
                                            <tr id="no_pecas_row">
                                                <td colspan="5" class="text-center text-muted small py-3">Nenhuma peça adicionada.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            @if($ordem_servico->podeConcluir())
                                <button type="button" class="btn btn-xs btn-outline-success font-weight-bold mt-2" id="btn_add_peca_exec" style="border-radius: 6px;">
                                    <i class="fas fa-plus mr-1"></i> Adicionar Peça / Material
                                </button>
                            @endif
                        </div>

                        <!-- Mão de obra e serviços executados -->
                        <div class="col-lg-6 mb-4">
                            <h6 class="font-weight-bold text-secondary mb-3"><i class="fas fa-user-cog mr-1"></i> Serviços Realizados e Mão de Obra</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="table_servicos_executados">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Serviço / Mão de Obra</th>
                                            <th class="text-center" style="width: 80px;">Qtd</th>
                                            <th class="text-right" style="width: 120px;">Vlr. Unitário</th>
                                            <th class="text-right" style="width: 120px;">Total</th>
                                            @if($ordem_servico->podeConcluir())
                                                <th style="width: 40px;"></th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($ordem_servico->itens->where('tipo_item', 'servico')->whereNull('veiculo_dano_id') as $execSrv)
                                            <!-- Somente os que têm valores associados -->
                                            @if($execSrv->valor_unitario > 0)
                                                <tr>
                                                    <td>{{ $execSrv->descricao_servico }}</td>
                                                    <td class="text-center">{{ $execSrv->quantidade }}</td>
                                                    <td class="text-right">R$ {{ number_format($execSrv->valor_unitario, 2, ',', '.') }}</td>
                                                    <td class="text-right font-weight-bold">R$ {{ number_format($execSrv->valor_total, 2, ',', '.') }}</td>
                                                    @if($ordem_servico->podeConcluir())
                                                        <td></td>
                                                    @endif
                                                </tr>
                                            @endif
                                        @endforeach
                                        @if($ordem_servico->podeConcluir())
                                            <tr id="no_servicos_row">
                                                <td colspan="5" class="text-center text-muted small py-3">Nenhum serviço de mão de obra adicionado.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            @if($ordem_servico->podeConcluir())
                                <button type="button" class="btn btn-xs btn-outline-success font-weight-bold mt-2" id="btn_add_servico_exec" style="border-radius: 6px;">
                                    <i class="fas fa-plus mr-1"></i> Adicionar Serviço / Mão de Obra
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEÇÃO D: CONCLUSÃO DA OS -->
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-check-double text-success mr-2"></i> D. Conclusão da Ordem de Serviço
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if($ordem_servico->podeConcluir())
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="relatorio_geral" class="font-weight-bold text-secondary">Resumo do serviço realizado *</label>
                                    <textarea name="relatorio_geral" id="relatorio_geral" class="form-control" rows="4" required minlength="3" placeholder="Descreva de forma geral o que foi executado na OS...">{{ old('relatorio_geral') }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="data_conclusao_val" class="font-weight-bold text-secondary">Data da conclusão</label>
                                        <input type="date" name="data_conclusao_val" id="data_conclusao_val" class="form-control" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="anexos" class="font-weight-bold text-secondary">Fotos e documentos</label>
                                        <input type="file" name="anexos[]" id="anexos" class="form-control-file" multiple>
                                        <small class="text-muted">Selecione uma ou mais imagens/laudos da conclusão.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 bg-light p-4 rounded d-flex flex-column justify-content-between" style="border-radius: 12px;">
                                <div>
                                    <h6 class="font-weight-bold text-secondary mb-3"><i class="fas fa-calculator mr-1"></i> Resumo de Fechamento</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Total de Peças:</span>
                                        <strong id="fechamento_pecas">R$ 0,00</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Total de Mão de Obra:</span>
                                        <strong id="fechamento_mao_obra">R$ 0,00</strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold text-dark h5 mb-0">Total Geral:</span>
                                        <span class="font-weight-bold text-success h4 mb-0" id="fechamento_total">R$ 0,00</span>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-success btn-lg btn-block font-weight-bold py-3" style="border-radius: 10px;">
                                        <i class="fas fa-check mr-2"></i> Concluir Ordem de Serviço
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- OS já Concluída -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-4">
                                    <h6 class="font-weight-bold text-secondary"><i class="fas fa-file-alt mr-1"></i> Relatório Geral de Fechamento</h6>
                                    <p class="text-dark bg-light p-3 rounded" style="border-radius: 8px; font-size: 0.95rem; border-left: 4px solid #28a745;">
                                        {{ $ordem_servico->relatorio_conclusao ?: 'Nenhum relatório preenchido.' }}
                                    </p>
                                </div>

                                @if($ordem_servico->anexos->isNotEmpty())
                                    <div>
                                        <h6 class="font-weight-bold text-secondary"><i class="fas fa-paperclip mr-1"></i> Documentos Anexados</h6>
                                        <div class="d-flex gap-2 flex-wrap">
                                            @foreach($ordem_servico->anexos as $anexo)
                                                @php($nome = $anexo->original_name ?: basename((string) $anexo->path))
                                                <a href="{{ asset('storage/' . $anexo->path) }}" target="_blank" class="btn btn-sm btn-outline-secondary px-3 py-2" style="border-radius: 6px;">
                                                    <i class="fas fa-image mr-1"></i> {{ \Illuminate\Support\Str::limit($nome, 30) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-4 bg-light p-4 rounded" style="border-radius: 12px; border: 1px solid #e2e8f0;">
                                <h6 class="font-weight-bold text-secondary mb-3"><i class="fas fa-calculator mr-1"></i> Demonstrativo Final</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total de Peças:</span>
                                    <strong class="text-dark">R$ {{ number_format($ordem_servico->valor_pecas, 2, ',', '.') }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total de Mão de Obra:</span>
                                    <strong class="text-dark">R$ {{ number_format($ordem_servico->valor_mao_obra, 2, ',', '.') }}</strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold text-dark h5 mb-0">Total Consolidado:</span>
                                    <span class="font-weight-bold text-success h4 mb-0">R$ {{ number_format($ordem_servico->valor_total, 2, ',', '.') }}</span>
                                </div>
                                <div class="text-xs text-muted mt-3 text-center">
                                    Lançamento de despesa registrado no Fluxo de Caixa.
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    @if($ordem_servico->podeConcluir())
        </form>
    @endif
</div>

@section('css')
<style>
    .os-show-page {
        font-family: inherit;
    }
    .rs-grid {
        display: block !important;
        width: 100% !important;
    }
    .rs-hero {
        position: relative;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.02);
    }
    .rs-hero__accent {
        height: 6px;
        width: 100%;
    }
    .rs-hero--aberta .rs-hero__accent { background: #f59e0b; }
    .rs-hero--em_andamento .rs-hero__accent { background: #3b82f6; }
    .rs-hero--concluida .rs-hero__accent { background: #10b981; }
    .rs-hero--cancelada .rs-hero__accent { background: #ef4444; }
    
    .rs-hero__body {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        padding: 24px;
        gap: 20px;
    }
    .rs-hero__eyebrow {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 8px;
    }
    .rs-hero__eyebrow i { margin-right: 6px; }
    .rs-hero__title {
        font-size: 1.6rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 10px 0;
    }
    .rs-hero__meta {
        display: flex;
        gap: 16px;
        color: #475569;
        font-size: 0.9rem;
    }
    .rs-hero__meta span i { margin-right: 6px; color: #94a3b8; }
    
    .rs-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 800;
        font-size: 0.8rem;
        letter-spacing: 0.05em;
        padding: 6px 14px;
        border-radius: 9999px;
    }
    .rs-status-badge.is-warning { background: #fef3c7; color: #d97706; }
    .rs-status-badge.is-primary { background: #dbeafe; color: #2563eb; }
    .rs-status-badge.is-success { background: #d1fae5; color: #059669; }
    .rs-status-badge.is-danger { background: #fee2e2; color: #dc2626; }
    
    .rs-hero__kpis {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .rs-kpi {
        text-align: right;
    }
    .rs-kpi span { font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600; }
    .rs-kpi strong { display: block; font-size: 1.3rem; font-weight: 800; color: #0f172a; }
    .rs-kpi-divider { width: 1px; height: 32px; background: #e2e8f0; }

    .rs-hero__actions {
        display: flex;
        gap: 8px;
    }
    .rs-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s;
    }
    .rs-btn--outline {
        border: 1px solid #cbd5e1;
        color: #334155;
        background: #fff;
    }
    .rs-btn--outline:hover {
        background: #f8fafc;
        color: #0f172a;
    }

    .os-box-list {
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .gap-2 { gap: 8px; }
    .w-100 { width: 100% !important; }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tablePecas = document.getElementById('table_pecas_executadas').querySelector('tbody');
    const tableServicos = document.getElementById('table_servicos_executados').querySelector('tbody');

    const btnAddPeca = document.getElementById('btn_add_peca_exec');
    const btnAddServico = document.getElementById('btn_add_servico_exec');

    const fechamentoPecas = document.getElementById('fechamento_pecas');
    const fechamentoMaoObra = document.getElementById('fechamento_mao_obra');
    const fechamentoTotal = document.getElementById('fechamento_total');

    function parseMoeda(str) {
        if (!str) return 0;
        str = str.replace('R$', '').replace(' ', '').trim();
        if (str.indexOf(',') !== -1) {
            str = str.replace(/\./g, '').replace(',', '.');
        }
        const val = parseFloat(str);
        return isNaN(val) ? 0 : val;
    }

    function formatMoeda(val) {
        return 'R$ ' + val.toFixed(2).replace('.', ',').replace(/\d(?=(\d{3})+,)/g, '$&.');
    }

    function recalcularTotais() {
        let totalPecas = 0;
        let totalMaoObra = 0;

        // Soma peças dinâmicas
        tablePecas.querySelectorAll('.peca-row').forEach(row => {
            const qty = parseInt(row.querySelector('.peca-qtd').value) || 0;
            const unit = parseMoeda(row.querySelector('.peca-val').value);
            const total = qty * unit;
            row.querySelector('.peca-total-cell').textContent = formatMoeda(total);
            totalPecas += total;
        });

        // Soma serviços dinâmicos
        tableServicos.querySelectorAll('.srv-row').forEach(row => {
            const qty = parseInt(row.querySelector('.srv-qtd').value) || 0;
            const unit = parseMoeda(row.querySelector('.srv-val').value);
            const total = qty * unit;
            row.querySelector('.srv-total-cell').textContent = formatMoeda(total);
            totalMaoObra += total;
        });

        if (fechamentoPecas) fechamentoPecas.textContent = formatMoeda(totalPecas);
        if (fechamentoMaoObra) fechamentoMaoObra.textContent = formatMoeda(totalMaoObra);
        if (fechamentoTotal) fechamentoTotal.textContent = formatMoeda(totalPecas + totalMaoObra);
    }

    function checkEmptyRows() {
        const noPecas = document.getElementById('no_pecas_row');
        const noServicos = document.getElementById('no_servicos_row');

        if (noPecas) {
            const rows = tablePecas.querySelectorAll('.peca-row').length;
            noPecas.style.display = rows > 0 ? 'none' : '';
        }
        if (noServicos) {
            const rows = tableServicos.querySelectorAll('.srv-row').length;
            noServicos.style.display = rows > 0 ? 'none' : '';
        }
    }

    if (btnAddPeca) {
        btnAddPeca.addEventListener('click', function() {
            const row = document.createElement('tr');
            row.className = 'peca-row';
            row.innerHTML = `
                <td>
                    <input type="text" name="pecas_exec_nome[]" class="form-control form-control-sm" placeholder="Nome da peça / material" required>
                </td>
                <td>
                    <input type="number" name="pecas_exec_qtd[]" class="form-control form-control-sm text-center peca-qtd" value="1" min="1" required>
                </td>
                <td>
                    <input type="text" name="pecas_exec_valor[]" class="form-control form-control-sm text-right peca-val" placeholder="0,00" value="0,00" required>
                </td>
                <td class="text-right align-middle font-weight-bold peca-total-cell">
                    R$ 0,00
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-xs btn-outline-danger border-0 btn-remove-row"><i class="fas fa-trash-alt"></i></button>
                </td>
            `;

            row.querySelector('.btn-remove-row').addEventListener('click', () => {
                row.remove();
                checkEmptyRows();
                recalcularTotais();
            });

            row.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', recalcularTotais);
            });

            const noPecas = document.getElementById('no_pecas_row');
            if (noPecas) tablePecas.insertBefore(row, noPecas);
            else tablePecas.appendChild(row);

            checkEmptyRows();
            recalcularTotais();
        });
    }

    if (btnAddServico) {
        btnAddServico.addEventListener('click', function() {
            const row = document.createElement('tr');
            row.className = 'srv-row';
            row.innerHTML = `
                <td>
                    <input type="text" name="servicos_exec_nome[]" class="form-control form-control-sm" placeholder="Descrição do serviço / mão de obra" required>
                </td>
                <td>
                    <input type="number" name="servicos_exec_qtd[]" class="form-control form-control-sm text-center srv-qtd" value="1" min="1" required>
                </td>
                <td>
                    <input type="text" name="servicos_exec_valor[]" class="form-control form-control-sm text-right srv-val" placeholder="0,00" value="0,00" required>
                </td>
                <td class="text-right align-middle font-weight-bold srv-total-cell">
                    R$ 0,00
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-xs btn-outline-danger border-0 btn-remove-row"><i class="fas fa-trash-alt"></i></button>
                </td>
            `;

            row.querySelector('.btn-remove-row').addEventListener('click', () => {
                row.remove();
                checkEmptyRows();
                recalcularTotais();
            });

            row.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', recalcularTotais);
            });

            const noServicos = document.getElementById('no_servicos_row');
            if (noServicos) tableServicos.insertBefore(row, noServicos);
            else tableServicos.appendChild(row);

            checkEmptyRows();
            recalcularTotais();
        });
    }
});
</script>
@stop
