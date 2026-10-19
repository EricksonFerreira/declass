@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .imp-formato { font-size:.8rem; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px; padding:10px 12px; }
        .imp-formato code { color:#0f172a; }
    </style>
@endpush

@section('content')
    @php $resumo = session('importacao_resumo'); @endphp

    @if ($resumo)
        <div class="alert alert-{{ count($resumo['resumo']['erros'] ?? []) ? 'warning' : 'success' }} shadow-sm">
            <h5 class="mb-2">
                <i class="fas fa-{{ count($resumo['resumo']['erros'] ?? []) ? 'exclamation-triangle' : 'check-circle' }} mr-1"></i>
                Importação de {{ $resumo['tipo'] }} concluída
            </h5>
            <div class="mb-1">
                <strong>{{ $resumo['resumo']['criados'] }}</strong> criado(s) ·
                <strong>{{ $resumo['resumo']['atualizados'] }}</strong> atualizado(s)
                @if (isset($resumo['resumo']['estimativas']))
                    · <strong>{{ $resumo['resumo']['estimativas'] }}</strong> estimativa(s) por função
                @endif
                · <strong>{{ count($resumo['resumo']['erros'] ?? []) }}</strong> erro(s)
            </div>
            @if (!empty($resumo['resumo']['erros']))
                <ul class="mb-0 pl-3" style="font-size:.85rem; max-height:200px; overflow:auto;">
                    @foreach ($resumo['resumo']['erros'] as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <div class="row">
        {{-- Usuários --}}
        <div class="col-lg-6">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm"><i class="fas fa-user-cog mr-1"></i> Colaboradores</h3>
                </div>
                <form method="POST" action="{{ route('importacao.preview', 'usuarios') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="imp-formato mb-3">
                            <div class="font-weight-bold mb-1">Colunas esperadas (cabeçalho na 1ª linha, <code>;</code> ou <code>,</code>):</div>
                            <code>nome;email;usuario;funcao;papel;jornada_diaria;celular</code>
                            <div class="text-muted mt-1">
                                Obrigatórias: <strong>nome</strong> e <strong>email</strong>.<br>
                                <strong>papel</strong>: administrador · lider · colaborador (padrão) · visualizador.<br>
                                <strong>funcao</strong>: cria a função se não existir (o custo/hora vem dela). E-mail já cadastrado → atualiza os dados.
                            </div>
                            @can('importacao.modelo')
                                <a href="{{ route('importacao.modelo', 'usuarios') }}" class="btn btn-xs btn-outline-primary mt-2">
                                    <i class="fas fa-download mr-1"></i>Baixar modelo CSV
                                </a>
                            @endcan
                        </div>
                        <div class="form-group">
                            <label>Arquivo CSV <span class="text-danger">*</span></label>
                            <input type="file" name="arquivo" class="form-control-file" accept=".csv,.txt" required>
                        </div>
                        <div class="form-group mb-0">
                            <label>Senha inicial dos novos usuários <span class="text-danger">*</span></label>
                            <input type="text" name="senha_inicial" class="form-control form-control-sm" minlength="6" required placeholder="mín. 6 caracteres">
                            <small class="text-muted">Todos trocam a senha no primeiro acesso.</small>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        @can('importacao.preview')
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search mr-1"></i>Enviar e conferir</button>
                        @endcan
                    </div>
                </form>
            </div>
        </div>

        {{-- Projetos --}}
        <div class="col-lg-6">
            <div class="card card-outline card-success shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm"><i class="fas fa-project-diagram mr-1"></i> Projetos</h3>
                </div>
                <form method="POST" action="{{ route('importacao.preview', 'projetos') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="imp-formato mb-3">
                            <div class="font-weight-bold mb-1">Colunas esperadas:</div>
                            <code>nome;codigo_cc;numero;ano;status;cliente;descricao;horas_orcadas;valor_faturado;estimativas</code>
                            <div class="text-muted mt-1">
                                Obrigatória: <strong>nome</strong>.<br>
                                <strong>status</strong>: Andamento · Concluído · Congelado · Cancelado.<br>
                                <strong>numero</strong> vazio + <strong>ano</strong> preenchido → sequencial automático.<br>
                                <strong>estimativas</strong>: <code>Engenheiro Civil:530|Projetista:130</code> cria as estimativas por função.<br>
                                Projeto já cadastrado (mesmo nome) → atualiza os dados.
                            </div>
                            @can('importacao.modelo')
                                <a href="{{ route('importacao.modelo', 'projetos') }}" class="btn btn-xs btn-outline-success mt-2">
                                    <i class="fas fa-download mr-1"></i>Baixar modelo CSV
                                </a>
                            @endcan
                        </div>
                        <div class="form-group mb-0">
                            <label>Arquivo CSV <span class="text-danger">*</span></label>
                            <input type="file" name="arquivo" class="form-control-file" accept=".csv,.txt" required>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        @can('importacao.preview')
                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-search mr-1"></i>Enviar e conferir</button>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-body py-2 text-muted" style="font-size:.85rem;">
            <i class="fas fa-info-circle mr-1"></i>
            O envio mostra uma <strong>prévia</strong> com as linhas válidas e os erros antes de gravar — nada é alterado sem a confirmação.
            No Excel: <em>Salvar como → CSV (separado por ponto e vírgula)</em>.
        </div>
    </div>
@stop
