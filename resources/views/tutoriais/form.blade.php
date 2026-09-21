@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Preencha os dados do vídeo tutorial" />
@stop

@section('content')
    <form method="POST" enctype="multipart/form-data"
          action="{{ $tutorial ? route('tutoriais.update', $tutorial) : route('tutoriais.store') }}">
        @csrf
        @if ($tutorial) @method('PUT') @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm" style="border-radius:12px; border:1px solid #e2e8f0;">
                    <div class="card-header bg-white py-3">
                        <h6 class="font-weight-bold m-0"><i class="fas fa-video mr-2 text-primary"></i>Dados do tutorial</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="titulo">Título <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" id="titulo" maxlength="255" required
                                   class="form-control @error('titulo') is-invalid @enderror"
                                   value="{{ old('titulo', $tutorial->titulo ?? '') }}"
                                   placeholder="Ex.: Como registrar horas no projeto">
                            @error('titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="ordem">Ordem</label>
                                <input type="number" name="ordem" id="ordem" min="0"
                                       class="form-control @error('ordem') is-invalid @enderror"
                                       value="{{ old('ordem', $tutorial->ordem ?? 0) }}">
                                @error('ordem') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="ativo">Status</label>
                                <select name="ativo" id="ativo" class="form-control @error('ativo') is-invalid @enderror">
                                    <option value="1" @selected(old('ativo', $tutorial->ativo ?? 1) == 1)>Ativo</option>
                                    <option value="0" @selected(old('ativo', $tutorial->ativo ?? 1) == 0)>Inativo</option>
                                </select>
                                @error('ativo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="tutorial_categoria_id">Categoria</label>
                                <select name="tutorial_categoria_id" id="tutorial_categoria_id"
                                        class="form-control @error('tutorial_categoria_id') is-invalid @enderror">
                                    <option value="">Sem categoria</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}"
                                            @selected((string) old('tutorial_categoria_id', $tutorial->tutorial_categoria_id ?? '') === (string) $categoria->id)>
                                            {{ $categoria->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tutorial_categoria_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="visibilidade">Visibilidade</label>
                            <select name="visibilidade" id="visibilidade" class="form-control @error('visibilidade') is-invalid @enderror">
                                <option value="todos" @selected(old('visibilidade', $tutorial->visibilidade ?? 'todos') === 'todos')>
                                    Todos os usuários
                                </option>
                                <option value="superadmin" @selected(old('visibilidade', $tutorial->visibilidade ?? '') === 'superadmin')>
                                    Somente superadmin
                                </option>
                            </select>
                            @error('visibilidade') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="principal" name="principal" value="1"
                                   @checked(old('principal', $tutorial->principal ?? false))>
                            <label class="custom-control-label" for="principal">
                                Marcar como tutorial principal <i class="fas fa-star text-warning"></i>
                            </label>
                        </div>

                        <div class="form-group mb-0">
                            <label for="descricao">Descrição</label>
                            <textarea name="descricao" id="descricao" rows="4"
                                      class="form-control @error('descricao') is-invalid @enderror"
                                      placeholder="Resumo do que o vídeo ensina">{{ old('descricao', $tutorial->descricao ?? '') }}</textarea>
                            @error('descricao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm" style="border-radius:12px; border:1px solid #e2e8f0;">
                    <div class="card-header bg-white py-3">
                        <h6 class="font-weight-bold m-0"><i class="fas fa-photo-video mr-2 text-primary"></i>Mídia</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $videoTipoAtual = old('video_tipo', $tutorial ? ($tutorial->video_path ? 'arquivo' : 'link') : 'link');
                        @endphp
                        <label class="d-block">Vídeo</label>
                        <div class="custom-control custom-radio mb-2">
                            <input type="radio" class="custom-control-input" id="video_tipo_arquivo"
                                   name="video_tipo" value="arquivo" @checked($videoTipoAtual === 'arquivo')>
                            <label class="custom-control-label" for="video_tipo_arquivo">Enviar arquivo</label>
                        </div>
                        <div id="video-arquivo-box" class="mb-3 {{ $videoTipoAtual === 'arquivo' ? '' : 'd-none' }}">
                            <input type="file" name="video" id="video" accept="video/mp4,video/webm,video/ogg,video/quicktime"
                                   class="form-control-file @error('video') is-invalid @enderror">
                            <small class="text-muted d-block">MP4, WebM, OGG ou MOV — até 500MB.</small>
                            @if ($tutorial?->video_path)
                                <small class="text-success d-block mt-1">
                                    <i class="fas fa-check-circle"></i> Arquivo atual enviado — enviar outro substitui.
                                </small>
                            @endif
                            @error('video') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="custom-control custom-radio mb-2">
                            <input type="radio" class="custom-control-input" id="video_tipo_link"
                                   name="video_tipo" value="link" @checked($videoTipoAtual === 'link')>
                            <label class="custom-control-label" for="video_tipo_link">Link do vídeo</label>
                        </div>
                        <div id="video-link-box" class="mb-3 {{ $videoTipoAtual === 'link' ? '' : 'd-none' }}">
                            <input type="text" name="video_url" id="video_url" maxlength="500"
                                   class="form-control @error('video_url') is-invalid @enderror"
                                   value="{{ old('video_url', $tutorial->video_url ?? '') }}"
                                   placeholder="https://youtube.com/watch?v=...">
                            <small class="text-muted d-block">YouTube e Vimeo abrem embutidos; outros links abrem em nova aba.</small>
                            @error('video_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <hr>

                        <label for="capa">Capa</label>
                        <input type="file" name="capa" id="capa" accept="image/*"
                               class="form-control-file @error('capa') is-invalid @enderror">
                        <small class="text-muted d-block">Imagem do card — até 5MB.</small>
                        @error('capa') <div class="text-danger small">{{ $message }}</div> @enderror

                        @if ($tutorial?->capa_url)
                            <div class="mt-2">
                                <img src="{{ $tutorial->capa_url }}" class="img-fluid rounded" style="max-height:120px;">
                                <small class="text-muted d-block mt-1">Capa atual — enviar outra substitui.</small>
                            </div>
                        @endif
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save mr-1"></i> {{ $tutorial ? 'Salvar alterações' : 'Cadastrar tutorial' }}
                </button>
                <a href="{{ route('tutoriais.gerenciar') }}" class="btn btn-outline-secondary btn-block">Voltar</a>
            </div>
        </div>
    </form>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const radios = document.querySelectorAll('input[name="video_tipo"]');
            const boxArquivo = document.getElementById('video-arquivo-box');
            const boxLink = document.getElementById('video-link-box');

            function alternar() {
                const tipo = document.querySelector('input[name="video_tipo"]:checked').value;
                boxArquivo.classList.toggle('d-none', tipo !== 'arquivo');
                boxLink.classList.toggle('d-none', tipo !== 'link');
            }

            radios.forEach(r => r.addEventListener('change', alternar));
            alternar();
        });
    </script>
@endpush
