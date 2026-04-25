@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-10 col-lg-11">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap:1rem;">
                        <div>
                            <h4 class="mb-1">{{ $plano->nome }}</h4>
                            <p class="text-muted mb-0">{{ $subtitulo }}</p>
                        </div>
                        <a href="{{ route('plano.edit', $plano) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Voltar ao plano
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('plano.acessos.update', $plano) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="limite_permissoes_por_regra">Limite de permissões por perfil</label>
                                    <input
                                        type="number"
                                        min="1"
                                        max="500"
                                        class="form-control"
                                        id="limite_permissoes_por_regra"
                                        name="limite_permissoes_por_regra"
                                        value="{{ old('limite_permissoes_por_regra', $plano->limite_permissoes_por_regra) }}"
                                        placeholder="Deixe em branco para ilimitado"
                                    >
                                    <small class="text-muted">Controla quantas permissões cada perfil da empresa pode receber.</small>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="alert alert-light border">
                                    <strong>Como funciona:</strong> o administrador da empresa só enxerga telas liberadas aqui e só consegue montar perfis com permissões incluídas neste plano.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap:.5rem;">
                            <h5 class="mb-0">Permissões liberadas no plano</h5>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-success mr-1" onclick="toggleAllPermissions(true)">Marcar tudo</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllPermissions(false)">Desmarcar tudo</button>
                            </div>
                        </div>

                        @foreach ($gruposPermissao as $grupo => $permissoes)
                            <div class="card card-outline card-primary mb-3">
                                <div class="card-header py-2">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:.5rem;">
                                        <strong>{{ ucfirst(str_replace('_', ' ', $grupo)) }}</strong>
                                        <div>
                                            <button type="button" class="btn btn-xs btn-outline-success mr-1" onclick="toggleGroup('{{ $grupo }}', true)">Grupo todo</button>
                                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="toggleGroup('{{ $grupo }}', false)">Limpar grupo</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach ($permissoes as $permissao)
                                            <div class="col-lg-4 col-md-6 mb-2">
                                                <label class="border rounded p-2 d-flex align-items-start h-100 mb-0 permission-item">
                                                    <input
                                                        type="checkbox"
                                                        class="mt-1 mr-2 permission-checkbox"
                                                        data-group="{{ $grupo }}"
                                                        name="permissions[]"
                                                        value="{{ $permissao->id }}"
                                                        {{ in_array((int) $permissao->id, old('permissions', $permitidas), true) ? 'checked' : '' }}
                                                    >
                                                    <span>
                                                        <strong class="d-block">{{ $permissao->name }}</strong>
                                                        <small class="text-muted">{{ $permissao->descricao ?: 'Sem descricao cadastrada.' }}</small>
                                                    </span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Salvar política de acesso
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        function toggleAllPermissions(checked) {
            document.querySelectorAll('.permission-checkbox').forEach(function (checkbox) {
                checkbox.checked = checked;
            });
        }

        function toggleGroup(group, checked) {
            document.querySelectorAll('.permission-checkbox[data-group="' + group + '"]').forEach(function (checkbox) {
                checkbox.checked = checked;
            });
        }
    </script>
@stop
