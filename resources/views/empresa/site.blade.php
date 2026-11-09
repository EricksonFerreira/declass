@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <x-page-title :title="'Site público: '.$empresa->nome" subtitle="Apresentação da empresa para visitantes — sem login" />
        <a href="{{ $empresa->site_url }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm mb-2">
            <i class="fas fa-external-link-alt"></i> Abrir site
        </a>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('empresa.site.update', $empresa) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="site_ativo" label="Site de apresentação no ar?" :options="['N' => 'Não — domínio vai direto para o login', 'S' => 'Sim — domínio mostra a apresentação']" :selected="old('site_ativo', $empresa->site_ativo ?? 'N')" id="site-ativo" />
                </div>
                <div class="col-md-8 d-flex align-items-end pb-3">
                    <small class="text-muted">
                        URL: <a href="{{ $empresa->site_url }}" target="_blank" rel="noopener">{{ $empresa->site_url }}</a>
                        @if($empresa->dominio) — domínio: <code>{{ $empresa->dominio }}</code> (definido no cadastro da empresa)@endif
                    </small>
                </div>
            </div>

            <div id="site-config">
            <hr>
            <h6 class="font-weight-bold">Modelo de layout</h6>
            <div class="row">
                @php
                    $descModelos = [
                        'classico' => 'Hero em gradiente nas cores da marca, com logo em destaque.',
                        'escola' => 'Institucional: nav com âncoras, foto ao lado do título, chips de destaque e CTA.',
                        'gandolfi' => 'Tradicional: barra superior com portais, hero com foto, aviso de matrículas, stats e acesso rápido.',
                        'hero' => 'Imagem de banner em tela cheia com texto sobreposto.',
                        'minimal' => 'Limpo e compacto, foco no conteúdo e contatos.',
                    ];
                    $modeloAtual = old('site_modelo', $empresa->site_modelo ?: 'classico');
                @endphp
                @foreach($modelos as $chave => $rotulo)
                    <div class="col-md-4 mb-2">
                        <div class="custom-control custom-radio border rounded p-3 h-100 {{ $modeloAtual === $chave ? 'border-primary' : '' }}" style="min-height:90px;">
                            <input class="custom-control-input" type="radio" name="site_modelo" id="modelo_{{ $chave }}" value="{{ $chave }}" {{ $modeloAtual === $chave ? 'checked' : '' }}>
                            <label class="custom-control-label w-100" for="modelo_{{ $chave }}" style="cursor:pointer;">
                                <span class="d-block font-weight-bold"><i class="fas fa-desktop mr-1 text-primary"></i> {{ $rotulo }}</span>
                                <small class="text-muted">{{ $descModelos[$chave] ?? '' }}</small>
                                <span class="d-block mt-2">
                                    <span class="btn btn-xs btn-outline-primary preview-modelo" data-modelo="{{ $chave }}">
                                        <i class="fas fa-eye"></i> Pré-visualizar
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>
                @endforeach
                @error('site_modelo')<div class="col-12 text-danger small">{{ $message }}</div>@enderror
            </div>

            <hr>
            <h6 class="font-weight-bold">Textos</h6>
            <div class="row">
                <div class="col-md-12">
                    <x-form-input name="site_titulo" label="Título / tagline" :value="old('site_titulo', $empresa->site_titulo ?? '')" maxlength="255" placeholder="Ex.: Educação que transforma" />
                </div>
                <div class="col-md-12">
                    <x-form-input name="site_aviso" label="Faixa de aviso" :value="old('site_aviso', $empresa->site_aviso ?? '')" maxlength="255" placeholder="Ex.: Matrículas {{ date('Y') + 1 }} abertas — converse com a secretaria" />
                    <small class="text-muted d-block mb-2" style="margin-top:-.5rem;">Aparece numa faixa destacada no topo do site. Vazio = usa o período de matrícula automaticamente (ou sem faixa se oculto).</small>
                </div>
                <div class="col-md-12">
                    <label class="text-xs">Descrição (apresentação da rede)</label>
                    <textarea name="site_descricao" class="form-control" rows="3" maxlength="2000" placeholder="Texto de apresentação da rede">{{ old('site_descricao', $empresa->site_descricao ?? '') }}</textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <label class="text-xs">Depoimentos (um por linha: Nome | Texto)</label>
                    <textarea name="site_depoimentos" class="form-control" rows="3" maxlength="4000" placeholder="Maria Souza — mãe do Pedro | A escola acolheu meu filho desde o primeiro dia.">{{ old('site_depoimentos', $empresa->site_depoimentos ?? '') }}</textarea>
                    @error('site_depoimentos')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr>
            <h6 class="font-weight-bold">Imagens</h6>
            <div class="row">
                <div class="col-md-6">
                    <label class="text-xs">Banner (fundo do topo — modelo "Destaque com imagem")</label>
                    <input type="file" name="site_banner" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp">
                    @error('site_banner')<div class="text-danger small">{{ $message }}</div>@enderror
                    @if($empresa->site_banner_url)
                        <img src="{{ $empresa->site_banner_url }}" alt="Banner" class="img-fluid rounded mt-2" style="max-height:90px;">
                    @endif
                    <small class="d-block text-muted">A logo vem do cadastro da empresa.</small>
                </div>
                <div class="col-md-6">
                    <label class="text-xs">Imagem da seção "Sobre"</label>
                    <input type="file" name="site_imagem_sobre" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp">
                    @error('site_imagem_sobre')<div class="text-danger small">{{ $message }}</div>@enderror
                    @if($empresa->site_imagem_sobre_url)
                        <img src="{{ $empresa->site_imagem_sobre_url }}" alt="Sobre" class="img-fluid rounded mt-2" style="max-height:90px;">
                    @endif
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <label class="text-xs">Galeria de fotos (estrutura, atividades, eventos)</label>
                    <input type="file" name="site_galeria[]" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp" multiple>
                    @error('site_galeria.*')<div class="text-danger small">{{ $message }}</div>@enderror
                    <small class="d-block text-muted">Até 12 fotos. As novas entram junto das atuais.</small>
                    @php $galeriaPaths = $empresa->site_galeria ?? []; @endphp
                    @if($galeriaPaths)
                        <div class="d-flex flex-wrap mt-2" style="gap:.6rem;">
                            @foreach($galeriaPaths as $i => $path)
                                @php $url = \Illuminate\Support\Facades\Storage::url($path); @endphp
                                <div class="text-center">
                                    <img src="{{ $url }}" alt="Foto {{ $i + 1 }}" class="img-thumbnail d-block" style="height:72px;width:96px;object-fit:cover;">
                                    <label class="text-xs text-muted m-0">
                                        <input type="checkbox" name="galeria_remover[]" value="{{ $path }}"> Remover
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <hr>
            <h6 class="font-weight-bold">Contato e módulos</h6>
            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="site_whatsapp" label="WhatsApp" :value="old('site_whatsapp', $empresa->site_whatsapp ?? '')" maxlength="30" placeholder="(00) 90000-0000" />
                </div>
                <div class="col-md-6">
                    <x-form-input name="site_instagram" label="Instagram" :value="old('site_instagram', $empresa->site_instagram ?? '')" maxlength="100" placeholder="@minhaescola" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="site_unidades" label="Mostrar unidades" :options="['S' => 'Sim', 'N' => 'Não']" :selected="old('site_unidades', $empresa->site_unidades ?? 'S')" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="site_matricula" label="Período de matrícula" :options="['S' => 'Abertas', 'F' => 'Encerradas', 'N' => 'Ocultar do site']" :selected="old('site_matricula', $empresa->site_matricula ?? 'S')" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="site_matricula_ano" label="Ano das matrículas" :value="old('site_matricula_ano', $empresa->site_matricula_ano ?? '')" maxlength="10" placeholder="Ex.: 2027" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="site_contato" label="Mostrar contato" :options="['S' => 'Sim', 'N' => 'Não']" :selected="old('site_contato', $empresa->site_contato ?? 'S')" />
                </div>
            </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('empresa.index') }}" class="btn btn-secondary">Voltar</a>
                <button type="submit" class="btn btn-primary">Salvar site</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalPreview" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width:95vw;">
        <div class="modal-content" style="height:88vh;">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="fas fa-eye mr-1"></i> Pré-visualização do modelo <span id="preview-modelo-nome"></span></h6>
                <div class="d-flex align-items-center" style="gap:.5rem;">
                    <div class="btn-group btn-group-xs" role="group" aria-label="Largura do preview">
                        <button type="button" class="btn btn-outline-secondary preview-largura active" data-largura="100%" title="Desktop">🖥</button>
                        <button type="button" class="btn btn-outline-secondary preview-largura" data-largura="768px" title="Tablet">▤</button>
                        <button type="button" class="btn btn-outline-secondary preview-largura" data-largura="375px" title="Celular">📱</button>
                    </div>
                    <a href="#" id="preview-abrir-nova" target="_blank" rel="noopener" class="btn btn-xs btn-outline-secondary"><i class="fas fa-external-link-alt"></i> Abrir em nova aba</a>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                </div>
            </div>
            <div class="modal-body p-0" style="display:flex;justify-content:center;">
                <iframe id="preview-frame" src="" style="width:100%;height:100%;border:0;"></iframe>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var select = document.getElementById('site-ativo');
    var bloco = document.getElementById('site-config');
    if (select && bloco) {
        function alternar() {
            bloco.style.display = select.value === 'S' ? '' : 'none';
        }
        select.addEventListener('change', alternar);
        alternar();
    }

    var previewBase = @json(route('empresa.site.preview', $empresa));
    document.querySelectorAll('.preview-modelo').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var url = previewBase + '?modelo=' + btn.dataset.modelo;
            document.getElementById('preview-frame').src = url;
            document.getElementById('preview-abrir-nova').href = url;
            document.getElementById('preview-modelo-nome').textContent =
                '“' + btn.closest('label').querySelector('.font-weight-bold').textContent.trim() + '”';
            $('#modalPreview').modal('show');
        });
    });
    $('#modalPreview').on('hidden.bs.modal', function () {
        document.getElementById('preview-frame').src = '';
    });
    document.querySelectorAll('.preview-largura').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.preview-largura').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            document.getElementById('preview-frame').style.maxWidth = btn.dataset.largura;
            document.getElementById('preview-frame').style.marginInline = 'auto';
        });
    });
});
</script>
@stop
