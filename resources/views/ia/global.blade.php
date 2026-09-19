@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Provedor padrão da plataforma — usado quando a empresa não tem configuração própria" />
@stop

@push('css')
    @include('partials.ux-index-styles')
@endpush

@section('content')
    @if (session('success'))
        <div class="alert alert-success shadow-sm">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row">
        {{-- Formulário --}}
        <div class="col-lg-7">
            <form method="POST" action="{{ route('ia-global.update') }}">
                @csrf
                @method('PUT')

                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title text-bold text-sm"><i class="fas fa-robot mr-1"></i> Provedor de IA</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="provedor">Provedor</label>
                            <select name="provedor" id="provedor" class="form-control @error('provedor') is-invalid @enderror">
                                @foreach ($provedores as $slug => $nome)
                                    <option value="{{ $slug }}" @selected(old('provedor', $config->provedor ?: 'deepseek') === $slug)>{{ $nome }}</option>
                                @endforeach
                            </select>
                            @error('provedor')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="api_key">API Key / Token</label>
                            <input type="password" name="api_key" id="api_key" autocomplete="new-password"
                                   class="form-control @error('api_key') is-invalid @enderror"
                                   placeholder="{{ $config->api_key ? '•••••••••• (deixe em branco para manter)' : 'Cole o token' }}">
                            <small class="text-muted">O token fica salvo no banco e nunca é exibido.</small>
                            @error('api_key')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            @if ($config->api_key)
                                <div class="custom-control custom-checkbox mt-1">
                                    <input type="checkbox" class="custom-control-input" id="remover_api_key" name="remover_api_key" value="1">
                                    <label class="custom-control-label text-danger" for="remover_api_key" style="font-size:.8rem;">Remover token salvo</label>
                                </div>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="modelo">Modelo</label>
                            <input type="text" name="modelo" id="modelo" maxlength="60"
                                   class="form-control @error('modelo') is-invalid @enderror"
                                   value="{{ old('modelo', $config->modelo) }}"
                                   placeholder="Deixe em branco para usar o modelo padrão do provedor"
                                   list="modelos-globais">
                            <datalist id="modelos-globais">
                                <option value="deepseek-chat">
                                <option value="deepseek-reasoner">
                                <option value="gpt-4o-mini">
                                <option value="gpt-4o">
                                <option value="gpt-4.1-mini">
                            </datalist>
                            @error('modelo')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="assistente_ativo" name="assistente_ativo" value="1"
                                   @checked(old('assistente_ativo', $config->assistente_ativo === 'S'))>
                            <label class="custom-control-label" for="assistente_ativo">Assistente ativo</label>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-secondary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title text-bold text-sm"><i class="fas fa-microphone mr-1"></i> Transcrição de áudio</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted" style="font-size:.85rem;">
                            Provedor separado para transcrever áudios gravados no chat. Use OpenAI ou Groq
                            (suportam Whisper). Se não preencher, usa o provedor principal.
                        </p>
                        <div class="form-group">
                            <label for="transcricao_provedor">Provedor de transcrição</label>
                            <select name="transcricao_provedor" id="transcricao_provedor" class="form-control @error('transcricao_provedor') is-invalid @enderror">
                                @foreach ($transcricaoProvedores as $slug => $nome)
                                    <option value="{{ $slug }}" @selected(old('transcricao_provedor', $config->transcricao_provedor ?? '') === (string) $slug)>{{ $nome }}</option>
                                @endforeach
                            </select>
                            @error('transcricao_provedor')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="transcricao_key">API Key da transcrição (separada)</label>
                            <input type="password" name="transcricao_key" id="transcricao_key" autocomplete="new-password"
                                   class="form-control @error('transcricao_key') is-invalid @enderror"
                                   placeholder="{{ $config->transcricao_key ? '•••••••••• (salva)' : 'Cole o token do OpenAI/Groq' }}">
                            <small class="text-muted">Se ficar em branco, usa a mesma key do provedor principal.</small>
                            @error('transcricao_key')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            @if ($config->transcricao_key)
                                <div class="custom-control custom-checkbox mt-1">
                                    <input type="checkbox" class="custom-control-input" id="remover_transcricao_key" name="remover_transcricao_key" value="1">
                                    <label class="custom-control-label text-danger" for="remover_transcricao_key" style="font-size:.8rem;">Remover token da transcrição</label>
                                </div>
                            @endif
                        </div>
                        <div class="form-group mb-0">
                            <label for="transcricao_modelo">Modelo de transcrição</label>
                            <input type="text" name="transcricao_modelo" id="transcricao_modelo" maxlength="60"
                                   class="form-control @error('transcricao_modelo') is-invalid @enderror"
                                   value="{{ old('transcricao_modelo', $config->transcricao_modelo) }}"
                                   placeholder="Padrão do provedor (whisper-1 / whisper-large-v3-turbo)">
                            @error('transcricao_modelo')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Salvar configuração</button>
                        <a href="{{ route('ia-uso.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-chart-bar mr-1"></i> Tokens por empresa
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Como funciona --}}
        <div class="col-lg-5">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm"><i class="fas fa-info-circle mr-1"></i> Como funciona</h3>
                </div>
                <div class="card-body">
                    <ul class="pl-3" style="font-size:.9rem;">
                        <li class="mb-2">Este é o <strong>provedor padrão da plataforma</strong> — usado quando a empresa não configurou o próprio em Gerenciar → Integração com IA.</li>
                        <li class="mb-2">Alimenta a <strong>análise de editais</strong> do módulo Licitações e futuros recursos de IA.</li>
                        <li class="mb-2">O <strong>assistente virtual</strong> (quando ativo) responde sobre telas e processos usando os dados da empresa.</li>
                        <li class="mb-2">O consumo de tokens é <strong>descontado da cota</strong> de cada empresa — veja em Tokens por empresa.</li>
                        <li class="mb-0"><strong>Transcrição de áudio</strong>: usa um provedor separado (OpenAI/Groq) para transcrever antes de enviar à IA.</li>
                    </ul>
                    <hr>
                    <div class="d-flex align-items-center" style="gap:.5rem;">
                        <span class="text-muted" style="font-size:.85rem;">Status atual:</span>
                        @if ($configurado)
                            <span class="badge badge-success">Configurado ({{ $provedores[$config->provedor] ?? $config->provedor }})</span>
                        @else
                            <span class="badge badge-warning">Sem chave — configure ou defina no .env</span>
                        @endif
                    </div>
                    @if ($usoMesGlobal && $usoMesGlobal->chamadas > 0)
                        <div class="text-muted mt-3" style="font-size:.82rem;">
                            <i class="fas fa-chart-line mr-1"></i>
                            Uso no mês (todas as empresas): {{ number_format($usoMesGlobal->chamadas, 0, ',', '.') }} chamada(s) ·
                            {{ number_format($usoMesGlobal->tokens, 0, ',', '.') }} tokens ·
                            US$ {{ number_format($usoMesGlobal->custo, 4, ',', '.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
