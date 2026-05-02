@extends('adminlte::page')

@section('title', 'Editor Visual do Site')

@section('content_header')
    <div class="se-toolbar">
        <div class="se-toolbar-left">
            <a href="{{ route('empresa.site-apresentacao.edit', $empresa->id) }}" class="se-btn se-btn-back">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
            <div class="se-divider"></div>
            <h4 class="se-title"><i class="fas fa-paint-brush mr-2"></i>Editor Visual — {{ $empresa->nome }}</h4>
            <div class="se-divider"></div>
            <div class="se-model-selector">
                <label>Modelo:</label>
                <select id="seModeloSelect" onchange="seTrocarModelo(this.value)" class="se-select">
                    <option value="modelo_1" {{ ($empresa->site_apresentacao_modelo ?? 'modelo_1') === 'modelo_1' ? 'selected' : '' }}>Modelo 1 — Premium</option>
                    <option value="modelo_2" {{ ($empresa->site_apresentacao_modelo ?? 'modelo_1') === 'modelo_2' ? 'selected' : '' }}>Modelo 2 — Moderno</option>
                    <option value="modelo_3" {{ ($empresa->site_apresentacao_modelo ?? 'modelo_1') === 'modelo_3' ? 'selected' : '' }}>Modelo 3 — Executive</option>
                </select>
            </div>
        </div>
        <div class="se-toolbar-right">
            <span id="seSaveStatus" class="se-save-status"></span>
            <button id="seSaveBtn" class="se-btn se-btn-primary" onclick="seSalvar()">
                <i class="fas fa-check"></i>
                <span>Publicar</span>
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="se-editor-layout">
        <div class="se-sidebar" id="seSidebar">
            <div class="se-sidebar-header">
                <i class="fas fa-layer-group"></i>
                <span>Seções</span>
                <button class="se-sidebar-close" onclick="seToggleSidebar()" title="Fechar painel">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="se-section-list">
                @php
                    $secoes = [
                        ['id' => 'topbar',   'icon' => 'fa-bars',        'label' => 'Topo / Navegação', 'desc' => 'Menu e identidade visual'],
                        ['id' => 'hero',     'icon' => 'fa-image',       'label' => 'Banner Principal',  'desc' => 'Hero e formulário'],
                        ['id' => 'frota',    'icon' => 'fa-car',         'label' => 'Frota',             'desc' => 'Cards de veículos'],
                        ['id' => 'vantagens','icon' => 'fa-thumbs-up',   'label' => 'Vantagens',         'desc' => 'Diferenciais'],
                        ['id' => 'etapas',   'icon' => 'fa-list-ol',     'label' => 'Como Funciona',     'desc' => 'Passo a passo'],
                        ['id' => 'contato',  'icon' => 'fa-envelope',    'label' => 'Contato',           'desc' => 'Informações'],
                        ['id' => 'global',   'icon' => 'fa-palette',     'label' => 'Global',            'desc' => 'Cores e botões'],
                    ];
                @endphp
                @foreach($secoes as $s)
                    <button class="se-section-btn" data-section="{{ $s['id'] }}" onclick="seAbrirModal('{{ $s['id'] }}')">
                        <div class="se-section-icon"><i class="fas {{ $s['icon'] }}"></i></div>
                        <div class="se-section-info">
                            <strong>{{ $s['label'] }}</strong>
                            <small>{{ $s['desc'] }}</small>
                        </div>
                        <i class="fas fa-chevron-right se-section-arrow"></i>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="se-preview">
            <div class="se-preview-toolbar">
                <button class="se-sidebar-toggle" onclick="seToggleSidebar()" title="Abrir seções">
                    <i class="fas fa-bars"></i>
                </button>
                <i class="fas fa-mobile-alt"></i>
                <span>Pré-visualização</span>
                <span class="se-preview-badge">Clique nos elementos do preview para editar</span>
                <span class="se-devices">
                    <button class="se-device-btn active" data-device="desktop" onclick="seMudarDevice('desktop')" title="Desktop"><i class="fas fa-desktop"></i></button>
                    <button class="se-device-btn" data-device="tablet" onclick="seMudarDevice('tablet')" title="Tablet"><i class="fas fa-tablet-alt"></i></button>
                    <button class="se-device-btn" data-device="mobile" onclick="seMudarDevice('mobile')" title="Celular"><i class="fas fa-mobile-alt"></i></button>
                </span>
            </div>
            <div class="se-preview-frame" id="sePreviewFrame">
                <iframe id="seIframe" src="{{ route('empresa.site-editor.preview', $empresa->id) }}?m={{ $empresa->site_apresentacao_modelo ?? 'modelo_1' }}" frameborder="0"></iframe>
            </div>
        </div>
    </div>

    {{-- ===== MODAIS ===== --}}

    @php
        $cor = fn($k, $d) => $siteCores[$k] ?? $d;
        $txt = fn($k, $d) => $siteTextos[$k] ?? $d;
    @endphp

    {{-- Modal: Topbar --}}
    <div class="modal fade left-panel" id="modal-topbar" data-backdrop="false" data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5><i class="fas fa-bars mr-2"></i>Topo / Navegação</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-topbar-cores">Cores</a></li>
                    </ul>
                    <div class="tab-content se-tab-content">
                        <div class="tab-pane active" id="tab-topbar-cores">
                            <div class="se-cor-grid">
                                <div class="se-cor-item"><label>Fundo do menu</label><input type="color" class="se-cor-picker" data-section="topbar" data-key="cor_topbar_fundo" value="{{ $cor('topbar_fundo', '#FFFFFF') }}"><input type="text" class="se-cor-text" value="{{ $cor('topbar_fundo', '#FFFFFF') }}"></div>
                                <div class="se-cor-item"><label>Texto do menu</label><input type="color" class="se-cor-picker" data-section="topbar" data-key="cor_topbar_texto" value="{{ $cor('topbar_texto', '#0F172A') }}"><input type="text" class="se-cor-text" value="{{ $cor('topbar_texto', '#0F172A') }}"></div>
                                <div class="se-cor-item"><label>Cor ao passar o mouse</label><input type="color" class="se-cor-picker" data-section="topbar" data-key="cor_topbar_link_hover" value="{{ $cor('topbar_link_hover', '#1F2937') }}"><input type="text" class="se-cor-text" value="{{ $cor('topbar_link_hover', '#1F2937') }}"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="seAplicarSecao('topbar')">Aplicar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Hero --}}
    <div class="modal fade left-panel" id="modal-hero" tabindex="-1" data-backdrop="false" data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5><i class="fas fa-image mr-2"></i>Banner Principal</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-hero-texto">Texto</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-hero-cores">Cores</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-hero-imagem">Imagem</a></li>
                    </ul>
                    <div class="tab-content se-tab-content">
                        <div class="tab-pane active" id="tab-hero-texto">
                            <div class="se-form-grid">
                                <div class="form-group"><label>Etiqueta (tag)</label><input class="form-control" data-section="hero" data-key="hero_tag" value="{{ $txt('hero_tag', '') }}"></div>
                                <div class="form-group"><label>Título</label><input class="form-control" data-section="hero" data-key="hero_titulo" value="{{ $txt('hero_titulo', $empresa->site_apresentacao_titulo ?: 'Aluguel de veículos com praticidade e segurança') }}"></div>
                                <div class="form-group"><label>Subtítulo</label><input class="form-control" data-section="hero" data-key="hero_subtitulo" value="{{ $txt('hero_subtitulo', $empresa->site_apresentacao_subtitulo ?: 'Reserve online, retire rápido e viaje com cobertura nacional.') }}"></div>
                                <div class="form-group"><label>Descrição</label><textarea class="form-control" data-section="hero" data-key="hero_descricao" rows="2">{{ $txt('hero_descricao', $empresa->site_apresentacao_descricao ?: 'Planos diários, semanais e mensais para pessoas físicas e empresas.') }}</textarea></div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-hero-cores">
                            <div class="se-cor-grid">
                                <div class="se-cor-item"><label>Fundo do banner</label><input type="color" class="se-cor-picker" data-section="hero" data-key="cor_hero_fundo" value="{{ $cor('hero_fundo', '#1F2937') }}"><input type="text" class="se-cor-text" value="{{ $cor('hero_fundo', '#1F2937') }}"></div>
                                <div class="se-cor-item"><label>Texto do banner</label><input type="color" class="se-cor-picker" data-section="hero" data-key="cor_hero_texto" value="{{ $cor('hero_texto', '#FFFFFF') }}"><input type="text" class="se-cor-text" value="{{ $cor('hero_texto', '#FFFFFF') }}"></div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-hero-imagem">
                            <div class="se-img-upload">
                                <label>Imagem do banner (recomendado: 1600x900)</label>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <input type="checkbox" class="se-img-toggle" data-section="hero" data-key="usar_imagem_hero" id="toggle-hero-img" {{ ($siteImagensConfig['hero'] ?? true) ? 'checked' : '' }}>
                                    <label for="toggle-hero-img" class="mb-0" style="font-size:.8rem;font-weight:500;color:#475569">Mostrar imagem no banner</label>
                                </div>
                                <div class="se-img-preview" id="seImgPreview-hero">
                                    @php $heroImg = $siteImagens['hero'] ?? null; @endphp
                                    @if($heroImg)
                                        <img src="{{ Storage::url($heroImg) }}" alt="Hero">
                                    @else
                                        <div class="se-img-placeholder"><i class="fas fa-image"></i><span>Nenhuma imagem selecionada</span></div>
                                    @endif
                                </div>
                                <input type="file" class="se-file-input" data-section="hero" data-key="imagem_hero" accept="image/jpeg,image/png,image/webp">
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="this.previousElementSibling.click()">
                                    <i class="fas fa-upload"></i> Escolher imagem
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="seAplicarSecao('hero')">Aplicar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Frota --}}
    <div class="modal fade left-panel" id="modal-frota" tabindex="-1" data-backdrop="false" data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5><i class="fas fa-car mr-2"></i>Frota</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-frota-texto">Texto</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-frota-cores">Cores</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-frota-imagem">Imagens</a></li>
                    </ul>
                    <div class="tab-content se-tab-content">
                        <div class="tab-pane active" id="tab-frota-texto">
                            <div class="se-form-grid">
                                <div class="form-group"><label>Etiqueta (rótulo superior)</label><input class="form-control" data-section="frota" data-key="frota_label_superior" value="{{ $txt('frota_label_superior', 'Frota em destaque') }}"></div>
                                <div class="form-group"><label>Título</label><input class="form-control" data-section="frota" data-key="frota_titulo" value="{{ $txt('frota_titulo', 'Categorias para cada tipo de viagem') }}"></div>
                                <div class="form-group"><label>Descrição</label><textarea class="form-control" data-section="frota" data-key="frota_descricao" rows="2">{{ $txt('frota_descricao', 'Selecione o grupo ideal e reserve com condições ajustadas ao seu perfil.') }}</textarea></div>
                                <hr><h6>Cards da Frota <small class="text-muted">(deixe o título vazio para ocultar)</small></h6>
                                @for($fi = 1; $fi <= 10; $fi++)
                                <div class="se-card-group">
                                    <div class="se-card-header">
                                        <span class="se-card-num">{{ str_pad($fi, 2, '0') }}</span>
                                        <span>Card {{ $fi }}</span>
                                    </div>
                                    <input class="form-control form-control-sm" placeholder="Título" data-section="frota" data-key="frota_card_{{$fi}}_titulo" value="{{ $txt('frota_card_'.$fi.'_titulo', '') }}">
                                    <textarea class="form-control form-control-sm mt-1" placeholder="Descrição" data-section="frota" data-key="frota_card_{{$fi}}_descricao" rows="1">{{ $txt('frota_card_'.$fi.'_descricao', '') }}</textarea>
                                </div>
                                @endfor
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-frota-cores">
                            <div class="se-cor-grid">
                                <div class="se-cor-item"><label>Fundo da seção</label><input type="color" class="se-cor-picker" data-section="frota" data-key="cor_frota_fundo" value="{{ $cor('frota_fundo', '#FFFFFF') }}"><input type="text" class="se-cor-text" value="{{ $cor('frota_fundo', '#FFFFFF') }}"></div>
                                <div class="se-cor-item"><label>Texto etiqueta</label><input type="color" class="se-cor-picker" data-section="frota" data-key="cor_frota_etiqueta_texto" value="{{ $cor('frota_etiqueta_texto', '#334155') }}"><input type="text" class="se-cor-text" value="{{ $cor('frota_etiqueta_texto', '#334155') }}"></div>
                                <div class="se-cor-item"><label>Texto título</label><input type="color" class="se-cor-picker" data-section="frota" data-key="cor_frota_titulo_texto" value="{{ $cor('frota_titulo_texto', '#0B1220') }}"><input type="text" class="se-cor-text" value="{{ $cor('frota_titulo_texto', '#0B1220') }}"></div>
                                <div class="se-cor-item"><label>Texto descrição</label><input type="color" class="se-cor-picker" data-section="frota" data-key="cor_frota_descricao_texto" value="{{ $cor('frota_descricao_texto', '#5E6E85') }}"><input type="text" class="se-cor-text" value="{{ $cor('frota_descricao_texto', '#5E6E85') }}"></div>
                                <div class="se-cor-item"><label>Card — título</label><input type="color" class="se-cor-picker" data-section="frota" data-key="cor_frota_card_titulo_texto" value="{{ $cor('frota_card_titulo_texto', '#0B1220') }}"><input type="text" class="se-cor-text" value="{{ $cor('frota_card_titulo_texto', '#0B1220') }}"></div>
                                <div class="se-cor-item"><label>Card — descrição</label><input type="color" class="se-cor-picker" data-section="frota" data-key="cor_frota_card_descricao_texto" value="{{ $cor('frota_card_descricao_texto', '#5E6E85') }}"><input type="text" class="se-cor-text" value="{{ $cor('frota_card_descricao_texto', '#5E6E85') }}"></div>
                                <div class="se-cor-item"><label>Card — fundo</label><input type="color" class="se-cor-picker" data-section="frota" data-key="cor_frota_card_fundo" value="{{ $cor('frota_card_fundo', '#FFFFFF') }}"><input type="text" class="se-cor-text" value="{{ $cor('frota_card_fundo', '#FFFFFF') }}"></div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-frota-imagem">
                            @for($fi = 1; $fi <= 10; $fi++)
                            <div class="se-img-upload mb-3">
                                <label>Imagem do Card {{ $fi }} (recomendado: 900x600)</label>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <input type="checkbox" class="se-img-toggle" data-section="frota" data-key="usar_imagem_frota_{{$fi}}" id="toggle-frota-{{$fi}}-img" {{ ($siteImagensConfig['frota_'.$fi] ?? true) ? 'checked' : '' }}>
                                    <label for="toggle-frota-{{$fi}}-img" class="mb-0" style="font-size:.8rem;font-weight:500;color:#475569">Mostrar imagem no card {{ $fi }}</label>
                                </div>
                                <div class="se-img-preview" id="seImgPreview-frota_{{$fi}}">
                                    @php $frotaImg = $siteImagens['frota_'.$fi] ?? null; @endphp
                                    @if($frotaImg)
                                        <img src="{{ Storage::url($frotaImg) }}" alt="Frota {{ $fi }}">
                                    @else
                                        <div class="se-img-placeholder"><i class="fas fa-car"></i><span>Nenhuma imagem</span></div>
                                    @endif
                                </div>
                                <input type="file" class="se-file-input" data-section="frota" data-key="imagem_frota_{{$fi}}" accept="image/jpeg,image/png,image/webp">
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="this.previousElementSibling.click()">
                                    <i class="fas fa-upload"></i> Escolher imagem
                                </button>
                            </div>
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="seAplicarSecao('frota')">Aplicar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Vantagens --}}
    <div class="modal fade left-panel" id="modal-vantagens" tabindex="-1" data-backdrop="false" data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5><i class="fas fa-thumbs-up mr-2"></i>Vantagens</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-vantagens-texto">Texto</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-vantagens-cores">Cores</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-vantagens-imagem">Imagem</a></li>
                    </ul>
                    <div class="tab-content se-tab-content">
                        <div class="tab-pane active" id="tab-vantagens-texto">
                            <div class="se-form-grid">
                                <div class="form-group"><label>Etiqueta</label><input class="form-control" data-section="vantagens" data-key="vantagens_label_superior" value="{{ $txt('vantagens_label_superior', 'Diferenciais') }}"></div>
                                <div class="form-group"><label>Título</label><input class="form-control" data-section="vantagens" data-key="vantagens_titulo" value="{{ $txt('vantagens_titulo', 'Por que escolher ' . $empresa->nome) }}"></div>
                                <div class="form-group"><label>Descrição</label><textarea class="form-control" data-section="vantagens" data-key="vantagens_descricao" rows="2">{{ $txt('vantagens_descricao', 'Fluxo inspirado nas melhores locadoras do mercado.') }}</textarea></div>
                                <hr><h6>Itens de Vantagens <small class="text-muted">(deixe vazio para ocultar)</small></h6>
                                @for($i = 1; $i <= 10; $i++)
                                <div class="form-group"><label>Benefício {{ $i }}</label><input class="form-control" data-section="vantagens" data-key="beneficio_{{$i}}" value="{{ $txt('beneficio_'.$i, '') }}"></div>
                                @endfor
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-vantagens-cores">
                            <div class="se-cor-grid">
                                <div class="se-cor-item"><label>Fundo da seção</label><input type="color" class="se-cor-picker" data-section="vantagens" data-key="cor_vantagens_fundo" value="{{ $cor('vantagens_fundo', '#FFFFFF') }}"><input type="text" class="se-cor-text" value="{{ $cor('vantagens_fundo', '#FFFFFF') }}"></div>
                                <div class="se-cor-item"><label>Texto etiqueta</label><input type="color" class="se-cor-picker" data-section="vantagens" data-key="cor_vantagens_etiqueta_texto" value="{{ $cor('vantagens_etiqueta_texto', '#334155') }}"><input type="text" class="se-cor-text" value="{{ $cor('vantagens_etiqueta_texto', '#334155') }}"></div>
                                <div class="se-cor-item"><label>Texto título</label><input type="color" class="se-cor-picker" data-section="vantagens" data-key="cor_vantagens_titulo_texto" value="{{ $cor('vantagens_titulo_texto', '#0B1220') }}"><input type="text" class="se-cor-text" value="{{ $cor('vantagens_titulo_texto', '#0B1220') }}"></div>
                                <div class="se-cor-item"><label>Texto descrição</label><input type="color" class="se-cor-picker" data-section="vantagens" data-key="cor_vantagens_descricao_texto" value="{{ $cor('vantagens_descricao_texto', '#5E6E85') }}"><input type="text" class="se-cor-text" value="{{ $cor('vantagens_descricao_texto', '#5E6E85') }}"></div>
                                <div class="se-cor-item"><label>Texto dos itens</label><input type="color" class="se-cor-picker" data-section="vantagens" data-key="cor_vantagens_item_texto" value="{{ $cor('vantagens_item_texto', '#2D3A4F') }}"><input type="text" class="se-cor-text" value="{{ $cor('vantagens_item_texto', '#2D3A4F') }}"></div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-vantagens-imagem">
                            <div class="se-img-upload">
                                <label>Imagem da seção Vantagens (recomendado: 900x600)</label>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <input type="checkbox" class="se-img-toggle" data-section="vantagens" data-key="usar_imagem_vantagens" id="toggle-vant-img" {{ ($siteImagensConfig['vantagens'] ?? true) ? 'checked' : '' }}>
                                    <label for="toggle-vant-img" class="mb-0" style="font-size:.8rem;font-weight:500;color:#475569">Mostrar imagem na seção</label>
                                </div>
                                <div class="se-img-preview" id="seImgPreview-vantagens">
                                    @php $vantImg = $siteImagens['vantagens'] ?? null; @endphp
                                    @if($vantImg)
                                        <img src="{{ Storage::url($vantImg) }}" alt="Vantagens">
                                    @else
                                        <div class="se-img-placeholder"><i class="fas fa-thumbs-up"></i><span>Nenhuma imagem</span></div>
                                    @endif
                                </div>
                                <input type="file" class="se-file-input" data-section="vantagens" data-key="imagem_vantagens" accept="image/jpeg,image/png,image/webp">
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="this.previousElementSibling.click()">
                                    <i class="fas fa-upload"></i> Escolher imagem
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="seAplicarSecao('vantagens')">Aplicar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Etapas --}}
    <div class="modal fade left-panel" id="modal-etapas" tabindex="-1" data-backdrop="false" data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5><i class="fas fa-list-ol mr-2"></i>Como Funciona</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-etapas-texto">Texto</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-etapas-cores">Cores</a></li>
                    </ul>
                    <div class="tab-content se-tab-content">
                        <div class="tab-pane active" id="tab-etapas-texto">
                            <div class="se-form-grid">
                                <div class="form-group"><label>Etiqueta</label><input class="form-control" data-section="etapas" data-key="etapas_label_superior" value="{{ $txt('etapas_label_superior', 'Jornada do cliente') }}"></div>
                                <div class="form-group"><label>Título</label><input class="form-control" data-section="etapas" data-key="etapas_titulo" value="{{ $txt('etapas_titulo', 'Como funciona a locação') }}"></div>
                                <hr><h6>Passos</h6>
                                @for($i = 1; $i <= 3; $i++)
                                <div class="form-group"><label>Passo {{ $i }} — Título</label><input class="form-control" data-section="etapas" data-key="etapa_{{$i}}_titulo" value="{{ $txt('etapa_'.$i.'_titulo', '') }}"></div>
                                <div class="form-group"><label>Passo {{ $i }} — Descrição</label><textarea class="form-control" data-section="etapas" data-key="etapa_{{$i}}_descricao" rows="1">{{ $txt('etapa_'.$i.'_descricao', '') }}</textarea></div>
                                @endfor
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-etapas-cores">
                            <div class="se-cor-grid">
                                <div class="se-cor-item"><label>Fundo da seção</label><input type="color" class="se-cor-picker" data-section="etapas" data-key="cor_etapas_fundo" value="{{ $cor('etapas_fundo', '#FFFFFF') }}"><input type="text" class="se-cor-text" value="{{ $cor('etapas_fundo', '#FFFFFF') }}"></div>
                                <div class="se-cor-item"><label>Texto etiqueta</label><input type="color" class="se-cor-picker" data-section="etapas" data-key="cor_etapas_etiqueta_texto" value="{{ $cor('etapas_etiqueta_texto', '#334155') }}"><input type="text" class="se-cor-text" value="{{ $cor('etapas_etiqueta_texto', '#334155') }}"></div>
                                <div class="se-cor-item"><label>Texto título</label><input type="color" class="se-cor-picker" data-section="etapas" data-key="cor_etapas_titulo_texto" value="{{ $cor('etapas_titulo_texto', '#0B1220') }}"><input type="text" class="se-cor-text" value="{{ $cor('etapas_titulo_texto', '#0B1220') }}"></div>
                                <div class="se-cor-item"><label>Texto descrição</label><input type="color" class="se-cor-picker" data-section="etapas" data-key="cor_etapas_descricao_texto" value="{{ $cor('etapas_descricao_texto', '#5E6E85') }}"><input type="text" class="se-cor-text" value="{{ $cor('etapas_descricao_texto', '#5E6E85') }}"></div>
                                <div class="se-cor-item"><label>Texto passo</label><input type="color" class="se-cor-picker" data-section="etapas" data-key="cor_etapas_passo_texto" value="{{ $cor('etapas_passo_texto', '#0B1220') }}"><input type="text" class="se-cor-text" value="{{ $cor('etapas_passo_texto', '#0B1220') }}"></div>
                                <div class="se-cor-item"><label>Descrição do passo</label><input type="color" class="se-cor-picker" data-section="etapas" data-key="cor_etapas_passo_descricao_texto" value="{{ $cor('etapas_passo_descricao_texto', '#5E6E85') }}"><input type="text" class="se-cor-text" value="{{ $cor('etapas_passo_descricao_texto', '#5E6E85') }}"></div>
                                <div class="se-cor-item"><label>Fundo do passo</label><input type="color" class="se-cor-picker" data-section="etapas" data-key="cor_etapas_passo_fundo" value="{{ $cor('etapas_passo_fundo', '#FFFFFF') }}"><input type="text" class="se-cor-text" value="{{ $cor('etapas_passo_fundo', '#FFFFFF') }}"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="seAplicarSecao('etapas')">Aplicar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Contato --}}
    <div class="modal fade left-panel" id="modal-contato" tabindex="-1" data-backdrop="false" data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5><i class="fas fa-envelope mr-2"></i>Contato</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-contato-texto">Texto</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-contato-cores">Cores</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-contato-imagem">Imagem</a></li>
                    </ul>
                    <div class="tab-content se-tab-content">
                        <div class="tab-pane active" id="tab-contato-texto">
                            <div class="se-form-grid">
                                <div class="form-group"><label>Etiqueta</label><input class="form-control" data-section="contato" data-key="contato_label_superior" value="{{ $txt('contato_label_superior', 'Contato direto') }}"></div>
                                <div class="form-group"><label>Título</label><input class="form-control" data-section="contato" data-key="contato_titulo" value="{{ $txt('contato_titulo', 'Informações da locadora') }}"></div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-contato-cores">
                            <div class="se-cor-grid">
                                <div class="se-cor-item"><label>Fundo da seção</label><input type="color" class="se-cor-picker" data-section="contato" data-key="cor_contato_fundo" value="{{ $cor('contato_fundo', '#1F2937') }}"><input type="text" class="se-cor-text" value="{{ $cor('contato_fundo', '#1F2937') }}"></div>
                                <div class="se-cor-item"><label>Texto etiqueta</label><input type="color" class="se-cor-picker" data-section="contato" data-key="cor_contato_etiqueta_texto" value="{{ $cor('contato_etiqueta_texto', '#FFFFFF') }}"><input type="text" class="se-cor-text" value="{{ $cor('contato_etiqueta_texto', '#FFFFFF') }}"></div>
                                <div class="se-cor-item"><label>Texto título</label><input type="color" class="se-cor-picker" data-section="contato" data-key="cor_contato_titulo_texto" value="{{ $cor('contato_titulo_texto', '#FFFFFF') }}"><input type="text" class="se-cor-text" value="{{ $cor('contato_titulo_texto', '#FFFFFF') }}"></div>
                                <div class="se-cor-item"><label>Texto informações (email/telefone)</label><input type="color" class="se-cor-picker" data-section="contato" data-key="cor_contato_info_texto" value="{{ $cor('contato_info_texto', '#FFFFFF') }}"><input type="text" class="se-cor-text" value="{{ $cor('contato_info_texto', '#FFFFFF') }}"></div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-contato-imagem">
                            <div class="se-img-upload">
                                <label>Imagem da seção Contato (recomendado: 600x400)</label>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <input type="checkbox" class="se-img-toggle" data-section="contato" data-key="usar_imagem_contato" id="toggle-contato-img" {{ ($siteImagensConfig['contato'] ?? true) ? 'checked' : '' }}>
                                    <label for="toggle-contato-img" class="mb-0" style="font-size:.8rem;font-weight:500;color:#475569">Mostrar imagem na seção</label>
                                </div>
                                <div class="se-img-preview" id="seImgPreview-contato">
                                    @php $contImg = $siteImagens['contato'] ?? null; @endphp
                                    @if($contImg)
                                        <img src="{{ Storage::url($contImg) }}" alt="Contato">
                                    @else
                                        <div class="se-img-placeholder"><i class="fas fa-envelope"></i><span>Nenhuma imagem</span></div>
                                    @endif
                                </div>
                                <input type="file" class="se-file-input" data-section="contato" data-key="imagem_contato" accept="image/jpeg,image/png,image/webp">
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="this.previousElementSibling.click()">
                                    <i class="fas fa-upload"></i> Escolher imagem
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="seAplicarSecao('contato')">Aplicar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Global --}}
    <div class="modal fade left-panel" id="modal-global" tabindex="-1" data-backdrop="false" data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5><i class="fas fa-palette mr-2"></i>Global</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-global-cores">Cores</a></li>
                    </ul>
                    <div class="tab-content se-tab-content">
                        <div class="tab-pane active" id="tab-global-cores">
                            <h6>Identidade da Marca</h6>
                            <div class="se-cor-grid">
                                <div class="se-cor-item"><label>Cor primária</label><input type="color" class="se-cor-picker" data-section="global" data-key="cor_primaria" value="{{ $empresa->cor_primaria ?: '#1F2937' }}"><input type="text" class="se-cor-text" value="{{ $empresa->cor_primaria ?: '#1F2937' }}"></div>
                                <div class="se-cor-item"><label>Cor secundária</label><input type="color" class="se-cor-picker" data-section="global" data-key="cor_secundaria" value="{{ $empresa->cor_secundaria ?: '#334155' }}"><input type="text" class="se-cor-text" value="{{ $empresa->cor_secundaria ?: '#334155' }}"></div>
                                <div class="se-cor-item"><label>Cor terciária (destaque)</label><input type="color" class="se-cor-picker" data-section="global" data-key="cor_terciaria" value="{{ $empresa->cor_terciaria ?: '#64748B' }}"><input type="text" class="se-cor-text" value="{{ $empresa->cor_terciaria ?: '#64748B' }}"></div>
                            </div>
                            <hr><h6>Botões</h6>
                            <div class="se-cor-grid">
                                <div class="se-cor-item"><label>Botão principal — fundo</label><input type="color" class="se-cor-picker" data-section="global" data-key="cor_botao_primario_fundo" value="{{ $cor('botao_primario_fundo', '#E7B93A') }}"><input type="text" class="se-cor-text" value="{{ $cor('botao_primario_fundo', '#E7B93A') }}"></div>
                                <div class="se-cor-item"><label>Botão principal — texto</label><input type="color" class="se-cor-picker" data-section="global" data-key="cor_botao_primario_texto" value="{{ $cor('botao_primario_texto', '#111827') }}"><input type="text" class="se-cor-text" value="{{ $cor('botao_primario_texto', '#111827') }}"></div>
                                <div class="se-cor-item"><label>Botão secundário — fundo</label><input type="color" class="se-cor-picker" data-section="global" data-key="cor_botao_secundario_fundo" value="{{ $cor('botao_secundario_fundo', '#FFFFFF') }}"><input type="text" class="se-cor-text" value="{{ $cor('botao_secundario_fundo', '#FFFFFF') }}"></div>
                                <div class="se-cor-item"><label>Botão secundário — texto</label><input type="color" class="se-cor-picker" data-section="global" data-key="cor_botao_secundario_texto" value="{{ $cor('botao_secundario_texto', '#1F2937') }}"><input type="text" class="se-cor-text" value="{{ $cor('botao_secundario_texto', '#1F2937') }}"></div>
                            </div>
                            <hr><h6>Rodapé</h6>
                            <div class="se-cor-grid">
                                <div class="se-cor-item"><label>Fundo do rodapé</label><input type="color" class="se-cor-picker" data-section="global" data-key="cor_footer_fundo" value="{{ $cor('footer_fundo', '#FFFFFF') }}"><input type="text" class="se-cor-text" value="{{ $cor('footer_fundo', '#FFFFFF') }}"></div>
                                <div class="se-cor-item"><label>Texto do rodapé</label><input type="color" class="se-cor-picker" data-section="global" data-key="cor_footer_texto" value="{{ $cor('footer_texto', '#64748B') }}"><input type="text" class="se-cor-text" value="{{ $cor('footer_texto', '#64748B') }}"></div>
                            </div>
                            <hr><h6>Texto do Rodapé</h6>
                            <div class="form-group"><input class="form-control" data-section="global" data-key="footer_texto" value="{{ $txt('footer_texto', 'Todos os direitos reservados.') }}"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="seAplicarSecao('global')">Aplicar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
/* ═══ EDITOR LAYOUT ═══ */
.se-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0; margin: -15px -15px 0; height: 56px;
    background: #fff; border-bottom: 1px solid #e2e8f0;
}
.se-toolbar-left, .se-toolbar-right { display: flex; align-items: center; gap: 12px; }
.se-toolbar-left { padding-left: 16px; }
.se-toolbar-right { padding-right: 16px; }
.se-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 8px; font-size: .85rem; font-weight: 600;
    border: 1px solid #e2e8f0; background: #fff; color: #334155;
    cursor: pointer; transition: all .15s; text-decoration: none;
}
.se-btn:hover { background: #f1f5f9; text-decoration: none; color: #1e293b; }
.se-btn-primary { background: #0ea5e9; color: #fff; border-color: #0ea5e9; }
.se-btn-primary:hover { background: #0284c7; color: #fff; }
.se-btn-back { border: none; background: transparent; }
.se-btn-back:hover { background: #f1f5f9; }
.se-divider { width: 1px; height: 28px; background: #e2e8f0; }
.se-title { font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0; }
.se-save-status { font-size: .8rem; color: #64748b; }
.se-model-selector { display: flex; align-items: center; gap: 6px; font-size: .82rem; color: #475569; }
.se-model-selector label { margin: 0; font-weight: 500; }
.se-select {
    padding: 4px 10px; border: 1px solid #e2e8f0; border-radius: 6px;
    font-size: .82rem; background: #fff; color: #0f172a; cursor: pointer;
    outline: none; transition: border-color .15s;
}
.se-select:hover { border-color: #0ea5e9; }
.se-select:focus { border-color: #0ea5e9; box-shadow: 0 0 0 2px rgba(14,165,233,.15); }

.se-editor-layout {
    display: flex; gap: 0; margin: -15px; height: calc(100vh - 130px);
    background: #f8fafc;
}
.se-sidebar {
    width: 300px; min-width: 300px; background: #fff;
    border-right: 1px solid #e2e8f0; overflow-y: auto;
    display: flex; flex-direction: column;
}
.se-sidebar-header {
    padding: 16px 20px; font-weight: 700; font-size: .9rem;
    border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px;
    color: #0f172a; background: #f8fafc;
}
.se-sidebar-close {
    margin-left: auto; background: none; border: none; color: #94a3b8;
    cursor: pointer; font-size: 1rem; padding: 4px 8px; border-radius: 6px;
    display: none;
}
.se-sidebar-close:hover { background: #e2e8f0; color: #475569; }
.se-sidebar-toggle {
    background: none; border: 1px solid #e2e8f0; color: #475569;
    cursor: pointer; font-size: .9rem; padding: 6px 10px; border-radius: 6px;
    display: none; align-items: center; justify-content: center;
}
.se-sidebar-toggle:hover { background: #f1f5f9; }
.se-section-list { padding: 8px; display: flex; flex-direction: column; gap: 4px; }
.se-section-btn {
    display: flex; align-items: center; gap: 12px; width: 100%;
    padding: 14px 16px; border: 1px solid transparent; border-radius: 10px;
    background: transparent; cursor: pointer; text-align: left;
    transition: all .15s; color: #334155;
}
.se-section-btn:hover { background: #f1f5f9; border-color: #e2e8f0; }
.se-section-icon {
    width: 38px; height: 38px; border-radius: 10px;
    background: linear-gradient(135deg, #0ea5e9, #3b82f6);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1rem; flex-shrink: 0;
}
.se-section-info { flex: 1; }
.se-section-info strong { display: block; font-size: .85rem; color: #0f172a; }
.se-section-info small { display: block; font-size: .72rem; color: #94a3b8; margin-top: 2px; }
.se-section-arrow { color: #cbd5e1; font-size: .75rem; }

.se-preview { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
.se-preview-toolbar {
    padding: 10px 20px; font-size: .85rem; font-weight: 600; color: #475569;
    border-bottom: 1px solid #e2e8f0; background: #fff;
    display: flex; align-items: center; gap: 10px;
}
.se-preview-badge {
    font-size: .72rem; font-weight: 400; color: #94a3b8;
    background: #f1f5f9; padding: 2px 10px; border-radius: 20px;
}
.se-devices { display: flex; align-items: center; gap: 4px; margin-left: auto; }
.se-device-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0;
    background: #fff; color: #94a3b8; cursor: pointer; transition: all .15s;
    font-size: .85rem;
}
.se-device-btn:hover { background: #f1f5f9; color: #475569; }
.se-device-btn.active { background: #0ea5e9; color: #fff; border-color: #0ea5e9; }
.se-preview-frame {
    flex: 1; padding: 20px; overflow: auto;
    background: #e2e8f0; display: flex; align-items: flex-start; justify-content: center;
    transition: all .3s;
}
.se-preview-frame iframe {
    border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,.10); background: #fff;
    transition: all .3s; width: 100%; height: 100%;
}
.se-preview-frame.device-tablet { padding: 20px calc(50% - 384px); }
.se-preview-frame.device-tablet iframe { width: 768px; }
.se-preview-frame.device-mobile { padding: 40px calc(50% - 185px); }
.se-preview-frame.device-mobile iframe { width: 375px; height: 812px; }

/* ═══ MODAL STYLES ═══ */
.se-tab-content { padding-top: 20px; }
.se-form-grid { display: flex; flex-direction: column; gap: 4px; }
.se-form-grid .form-group { margin-bottom: 8px; }
.se-form-grid h6 { font-size: .85rem; font-weight: 700; color: #0f172a; margin: 12px 0 8px; }
.se-form-grid hr { margin: 12px 0; border-color: #e2e8f0; }
.se-cor-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.se-cor-item {
    display: flex; flex-direction: column; gap: 4px;
    padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;
    background: #f8fafc;
}
.se-cor-item label { font-size: .78rem; font-weight: 600; color: #475569; margin: 0; }
.se-cor-item input[type="color"] {
    width: 100%; height: 36px; padding: 2px; border: 1px solid #e2e8f0;
    border-radius: 6px; cursor: pointer; background: #fff;
}
.se-cor-item .se-cor-text {
    font-size: .75rem; font-family: monospace; text-align: center;
    border: 1px solid #e2e8f0; border-radius: 6px; padding: 4px 8px;
    background: #fff; color: #334155;
}

/* Sync color picker ↔ text */
.se-cor-picker, .se-cor-text { transition: all .1s; }

/* ═══ IMAGE UPLOAD ═══ */
.se-img-upload { padding: 8px 0; }
.se-img-upload label { font-size: .82rem; font-weight: 600; color: #334155; margin-bottom: 8px; display: block; }
.se-img-preview {
    width: 100%; height: 140px; border-radius: 8px; overflow: hidden;
    border: 1px solid #e2e8f0; background: #f8fafc;
    display: flex; align-items: center; justify-content: center;
}
.se-img-preview img { width: 100%; height: 100%; object-fit: cover; }
.se-img-placeholder {
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    color: #94a3b8; font-size: .8rem;
}
.se-img-placeholder i { font-size: 1.6rem; }
.se-file-input { display: none; }

/* ═══ CARD GROUP ═══ */
.se-card-group {
    border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;
    margin-bottom: 10px; background: #f8fafc;
}
.se-card-header {
    display: flex; align-items: center; gap: 8px;
    font-size: .82rem; font-weight: 600; color: #334155; margin-bottom: 8px;
}
.se-card-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px; border-radius: 6px;
    background: #0ea5e9; color: #fff; font-size: .7rem; font-weight: 800;
}

/* ═══ LEFT PANEL (side modal) ═══ */
.modal.left-panel { padding-right: 0 !important; }
.modal.left-panel.fade .modal-dialog {
    position: fixed; left: 0; top: 56px; margin: 0;
    width: 380px; max-width: 100vw;
    height: calc(100vh - 56px);
    transform: translateX(-100%);
    transition: transform .25s ease;
}
.modal.left-panel.fade.show .modal-dialog { transform: translateX(0); }
.modal.left-panel .modal-dialog { max-width: 380px; height: calc(100vh - 56px); }
.modal.left-panel .modal-content {
    height: 100%; border-radius: 0; border: none;
    box-shadow: 4px 0 24px rgba(0,0,0,.10);
}
.modal.left-panel .modal-header {
    border-radius: 0; min-height: 56px; padding: 14px 20px;
    background: #f8fafc; border-bottom: 1px solid #e2e8f0;
}
.modal.left-panel .modal-header h5 { font-size: .95rem; font-weight: 700; margin: 0; }
.modal.left-panel .modal-body { overflow-y: auto; padding: 20px; }
.modal.left-panel .modal-footer {
    border-radius: 0; padding: 12px 20px;
    border-top: 1px solid #e2e8f0;
}

/* ═══ SIDEBAR COLLAPSED ═══ */
.se-sidebar { transition: width .25s ease, min-width .25s ease, opacity .25s ease; overflow: hidden; }
.se-sidebar.collapsed { width: 0 !important; min-width: 0 !important; opacity: 0; padding: 0; }
.se-sidebar.collapsed .se-sidebar-header,
.se-sidebar.collapsed .se-section-list { display: none; }

@media (max-width: 768px) {
    .se-sidebar { position: fixed; left: 0; top: 56px; bottom: 0; z-index: 50; box-shadow: 4px 0 20px rgba(0,0,0,.1); }
    .se-sidebar:not(.collapsed) { width: 320px !important; min-width: 320px !important; }
    .se-sidebar.collapsed { transform: translateX(-100%); width: 320px !important; min-width: 320px !important; opacity: 1; }
    .se-sidebar-close { display: inline-flex; }
    .se-sidebar-toggle { display: inline-flex; }
    .se-preview-frame { padding: 10px; }
}
</style>
@stop

@section('js')
<script>
let seDados = {};
let seArquivos = {};
let seDataUrls = {};

function seColetarSecao(section) {
    const dados = {};
    document.querySelectorAll(`[data-section="${section}"]`).forEach(el => {
        const key = el.dataset.key;
        if (el.type === 'file') return;
        if (el.type === 'checkbox') { dados[key] = el.checked ? '1' : '0'; return; }
        dados[key] = el.value;
    });
    return dados;
}

function seAplicarSecao(section) {
    const dados = seColetarSecao(section);
    Object.assign(seDados, dados);
    seMostrarStatus('Alterações aplicadas na prévia', 'success');
    seAtualizarPreview(section);
    $('#modal-' + section).modal('hide');
}

function seMostrarStatus(msg, type) {
    const el = document.getElementById('seSaveStatus');
    el.textContent = msg;
    el.style.color = type === 'success' ? '#16a34a' : '#dc2626';
    el.style.opacity = '1';
    setTimeout(() => { el.style.opacity = '0'; }, 3000);
}

function seAtualizarPreview(section) {
    const iframe = document.getElementById('seIframe');
    if (!iframe || !iframe.contentWindow) return;
    iframe.contentWindow.postMessage({ type: 'se-atualizar', dados: seDados, dataUrls: seDataUrls, secao: section }, '*');
}

function seSalvar() {
    const btn = document.getElementById('seSaveBtn');
    const status = document.getElementById('seSaveStatus');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
    status.textContent = 'Salvando...';
    status.style.color = '#0ea5e9';
    status.style.opacity = '1';

    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');

    Object.entries(seDados).forEach(([key, value]) => {
        formData.append(key, value);
    });

    Object.entries(seArquivos).forEach(([key, file]) => {
        formData.append(key, file);
    });

    fetch('{{ route("empresa.site-editor.update", $empresa->id) }}', {
        method: 'POST',
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            seMostrarStatus('✓ Publicado com sucesso!', 'success');
            document.getElementById('seIframe').src = document.getElementById('seIframe').src.split('?')[0] + '?_t=' + Date.now();
        } else {
            seMostrarStatus('Erro: ' + (data.error || 'unknown'), 'error');
        }
    })
    .catch(err => {
        seMostrarStatus('Erro de conexão: ' + err.message, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Publicar';
    });
}

function seAbrirModal(section) {
    $('#modal-' + section).modal('show');
    $('#modal-' + section).find('.nav-tabs a:first').tab('show');
    if (window.innerWidth < 768) seToggleSidebar();
}

function seAbrirModalComAlvo(secao, alvo, campo) {
    var modalId = '#modal-' + secao;
    var tabMap = { texto: 'texto', fundo: 'cores', imagem: 'imagem' };
    var tabId = tabMap[alvo] || 'texto';

    $(modalId).modal('show');
    var tabLink = $(modalId + ' a[href*="' + tabId + '"]');
    if (tabLink.length) tabLink.tab('show');

    if (campo) {
        setTimeout(function() {
            var el = document.querySelector('[data-section="' + secao + '"][data-key="' + campo + '"]');
            if (el) {
                if (el.type === 'file') el.click();
                else if (el.type === 'color') el.click();
                else el.focus();
            }
        }, 400);
    }
}

// Sync color picker ↔ text input
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('se-cor-picker')) {
        const text = e.target.parentElement.querySelector('.se-cor-text');
        if (text) text.value = e.target.value.toUpperCase();
    }
    if (e.target.classList.contains('se-cor-text')) {
        const picker = e.target.parentElement.querySelector('.se-cor-picker');
        if (picker && /^#[0-9a-fA-F]{6}$/.test(e.target.value)) {
            picker.value = e.target.value.toUpperCase();
        }
    }
});

// File input change → preview + store
document.addEventListener('change', function(e) {
    if (!e.target.classList.contains('se-file-input')) return;
    var file = e.target.files[0];
    if (!file) return;

    var key = e.target.dataset.key;
    var section = e.target.dataset.section;
    seArquivos[key] = file;

    var previewId = 'seImgPreview-' + key.replace('imagem_', '');
    var preview = document.getElementById(previewId);
    if (preview) {
        var reader = new FileReader();
        reader.onload = function(ev) {
            var dataUrl = ev.target.result;
            preview.innerHTML = '<img src="' + dataUrl + '" alt="Preview">';
            seDataUrls[key] = dataUrl;
            seAtualizarPreview(section);
        };
        reader.readAsDataURL(file);
    }

    seDados[key] = file.name;
    seMostrarStatus('Imagem selecionada. Publique para salvar.', 'success');
});

// Listen for postMessage from iframe
window.addEventListener('message', function(e) {
    if (!e.data) return;

    if (e.data.type === 'se-pronto') {
        seEnviarDadosParaPreview();
        return;
    }

    if (e.data.type === 'se-clicar') {
        var alvo = e.data.alvo;
        var secao = e.data.secao;
        var campo = e.data.campo;
        seAbrirModalComAlvo(secao, alvo, campo);
        return;
    }
});

function seEnviarDadosParaPreview() {
    const iframe = document.getElementById('seIframe');
    if (iframe && iframe.contentWindow) {
        iframe.contentWindow.postMessage({ type: 'se-carregar', dados: seDados, dataUrls: seDataUrls }, '*');
    }
}

// Carregar dados atuais
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-section]').forEach(el => {
        const key = el.dataset.key;
        seDados[key] = el.value;
    });
});

// Trocar modelo
function seTrocarModelo(modelo) {
    if (!confirm('Trocar o modelo pode alterar a aparência do site. Continuar?')) {
        document.getElementById('seModeloSelect').value = '{{ $empresa->site_apresentacao_modelo ?? "modelo_1" }}';
        return;
    }
    var btn = document.getElementById('seSaveBtn');
    var oldHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aplicando...';

    var formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('site_apresentacao_modelo', modelo);

    fetch('{{ route("empresa.site-editor.update", $empresa->id) }}', {
        method: 'POST',
        body: formData,
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            seMostrarStatus('✓ Modelo alterado!', 'success');
            document.getElementById('seIframe').src = '{{ route("empresa.site-editor.preview", $empresa->id) }}?m=' + modelo + '&_t=' + Date.now();
        } else {
            seMostrarStatus('Erro: ' + (data.error || 'unknown'), 'error');
        }
    })
    .catch(function(err) {
        seMostrarStatus('Erro de conexão', 'error');
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = oldHtml;
    });
}

// Device preview
function seMudarDevice(device) {
    var frame = document.getElementById('sePreviewFrame');
    var btns = document.querySelectorAll('.se-device-btn');
    frame.className = 'se-preview-frame' + (device !== 'desktop' ? ' device-' + device : '');
    btns.forEach(function(b) {
        b.classList.toggle('active', b.dataset.device === device);
    });
}

// Toggle sidebar
function seToggleSidebar() {
    var el = document.getElementById('seSidebar');
    el.classList.toggle('collapsed');
}

// Fechar painel ao clicar fora dele
document.addEventListener('click', function(e) {
    document.querySelectorAll('.modal.left-panel.show').forEach(function(modal) {
        var dialog = modal.querySelector('.modal-dialog');
        var toolbar = document.querySelector('.se-toolbar');
        if (dialog && !dialog.contains(e.target) && toolbar && !toolbar.contains(e.target)) {
            $(modal).modal('hide');
        }
    });
});
</script>
@stop
