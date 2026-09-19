@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Provedor e comportamento da IA nos recursos inteligentes (ex.: análise de editais de licitação)" />
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

    @if ($usoMes && $usoMes->chamadas > 0)
        <div class="alert alert-light border shadow-sm d-flex align-items-center" style="font-size:.85rem;">
            <i class="fas fa-chart-line mr-2 text-primary"></i>
            <span>
                <strong>Uso no mês:</strong>
                {{ number_format($usoMes->chamadas, 0, ',', '.') }} chamada(s) ·
                {{ number_format($usoMes->tokens, 0, ',', '.') }} tokens ·
                custo estimado US$ {{ number_format($usoMes->custo, 4, ',', '.') }}
            </span>
        </div>
    @endif

    <form method="POST" action="{{ route('configuracao-ia.update') }}">
        @csrf
        @method('PUT')

        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title text-bold text-sm"><i class="fas fa-robot mr-1"></i> Integração com IA</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="provedor_padrao">Provedor Padrão</label>
                        <select name="provedor_padrao" id="provedor_padrao" class="form-control form-control-sm @error('provedor_padrao') is-invalid @enderror">
                            @foreach ($provedores as $slug => $nome)
                                <option value="{{ $slug }}" @selected(old('provedor_padrao', $config->provedor_padrao ?: 'deepseek') === $slug)>{{ $nome }}{{ $slug === 'deepseek' ? ' (Padrão)' : '' }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Define qual provedor de IA será usado por padrão nas análises.</small>
                        @error('provedor_padrao')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="tokens_limite">Limite de Tokens (Resposta)</label>
                        <input type="number" name="tokens_limite" id="tokens_limite" min="256" max="32000" step="256"
                               class="form-control form-control-sm @error('tokens_limite') is-invalid @enderror"
                               value="{{ old('tokens_limite', $config->tokens_limite ?: 4000) }}">
                        <small class="text-muted">Controla o tamanho máximo da resposta gerada. Ex.: 4000 tokens.</small>
                        @error('tokens_limite')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                <hr>
                <h6 class="text-bold"><i class="fas fa-user-shield mr-1"></i> Privacidade e Sigilo (LGPD)</h6>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="anonimizar" name="anonimizar" value="1"
                           @checked(old('anonimizar', $config->anonimizar === 'S'))>
                    <label class="custom-control-label" for="anonimizar">Anonimizar dados antes de enviar à IA</label>
                </div>
                <small class="text-muted d-block mt-1">
                    Substitui CNPJ, CPF, e-mails, telefones, CEP e nomes da empresa, clientes e colaboradores por
                    marcadores ([CNPJ], [E-MAIL], [CLIENTE] etc.) antes de enviar à IA. O conteúdo é mantido —
                    a IA entende o contexto sem expor pessoas ou dados identificáveis (LGPD).
                </small>

                <hr>
                <h6 class="text-bold"><i class="fas fa-sliders-h mr-1"></i> Ajuste do Resumo</h6>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="tamanho_resumo">Tamanho do Resumo</label>
                        <select name="tamanho_resumo" id="tamanho_resumo" class="form-control form-control-sm @error('tamanho_resumo') is-invalid @enderror">
                            @foreach ($tamanhos as $slug => $nome)
                                <option value="{{ $slug }}" @selected(old('tamanho_resumo', $config->tamanho_resumo ?: 'medio') === $slug)>{{ $nome }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Controla o quão resumido o texto gerado será.</small>
                        @error('tamanho_resumo')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label for="instrucoes_extras">Instruções Extras para a IA</label>
                    <textarea name="instrucoes_extras" id="instrucoes_extras" rows="3" maxlength="4000"
                              class="form-control form-control-sm @error('instrucoes_extras') is-invalid @enderror"
                              placeholder="Ex.: Sempre destaque prazos de abertura em primeiro lugar. Use linguagem acessível.">{{ old('instrucoes_extras', $config->instrucoes_extras) }}</textarea>
                    <small class="text-muted">Texto livre incluído no prompt de todas as análises — ajusta o comportamento da IA ao estilo da empresa.</small>
                    @error('instrucoes_extras')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="row">
            {{-- DeepSeek --}}
            <div class="col-lg-6">
                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title text-bold text-sm">DeepSeek</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="deepseek_key">API Key</label>
                            <input type="password" name="deepseek_key" id="deepseek_key" autocomplete="new-password"
                                   class="form-control form-control-sm @error('deepseek_key') is-invalid @enderror"
                                   placeholder="{{ $config->deepseek_key ? '•••••••••••••••••••••••••••••••••••' : 'sk-...' }}">
                            <small class="text-muted">
                                {{ $config->deepseek_key ? 'Chave da empresa cadastrada — em branco mantém a atual.' : 'Vazio = usa a chave global do servidor (DEEPSEEK_API_KEY).' }}
                            </small>
                            @error('deepseek_key')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            @if ($config->deepseek_key)
                                <div class="custom-control custom-checkbox mt-1">
                                    <input type="checkbox" class="custom-control-input" id="remover_deepseek_key" name="remover_deepseek_key" value="1">
                                    <label class="custom-control-label text-danger" for="remover_deepseek_key" style="font-size:.8rem;">Remover chave da empresa</label>
                                </div>
                            @endif
                        </div>
                        <div class="form-group mb-0">
                            <label for="deepseek_model">Modelo</label>
                            <input type="text" name="deepseek_model" id="deepseek_model" list="modelos-deepseek" maxlength="60"
                                   class="form-control form-control-sm @error('deepseek_model') is-invalid @enderror"
                                   value="{{ old('deepseek_model', $config->deepseek_model) }}" placeholder="{{ config('services.deepseek.model', 'deepseek-chat') }}">
                            <datalist id="modelos-deepseek">
                                <option value="deepseek-chat">
                                <option value="deepseek-reasoner">
                            </datalist>
                            @error('deepseek_model')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- OpenAI --}}
            <div class="col-lg-6">
                <div class="card card-outline card-success shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title text-bold text-sm">OpenAI (ChatGPT)</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="openai_key">API Key</label>
                            <input type="password" name="openai_key" id="openai_key" autocomplete="new-password"
                                   class="form-control form-control-sm @error('openai_key') is-invalid @enderror"
                                   placeholder="{{ $config->openai_key ? '•••••••••••••••••••••••••••••••••••' : 'sk-...' }}">
                            <small class="text-muted">
                                {{ $config->openai_key ? 'Chave da empresa cadastrada — em branco mantém a atual.' : 'Vazio = usa a chave global do servidor (OPENAI_API_KEY).' }}
                            </small>
                            @error('openai_key')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            @if ($config->openai_key)
                                <div class="custom-control custom-checkbox mt-1">
                                    <input type="checkbox" class="custom-control-input" id="remover_openai_key" name="remover_openai_key" value="1">
                                    <label class="custom-control-label text-danger" for="remover_openai_key" style="font-size:.8rem;">Remover chave da empresa</label>
                                </div>
                            @endif
                        </div>
                        <div class="form-group mb-0">
                            <label for="openai_model">Modelo</label>
                            <input type="text" name="openai_model" id="openai_model" list="modelos-openai" maxlength="60"
                                   class="form-control form-control-sm @error('openai_model') is-invalid @enderror"
                                   value="{{ old('openai_model', $config->openai_model) }}" placeholder="{{ config('services.openai.model', 'gpt-4o-mini') }}">
                            <datalist id="modelos-openai">
                                <option value="gpt-4o-mini">
                                <option value="gpt-4o">
                                <option value="gpt-4.1-mini">
                                <option value="gpt-4.1">
                            </datalist>
                            @error('openai_model')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-right mb-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Salvar Configurações de IA
            </button>
        </div>
    </form>
@stop
