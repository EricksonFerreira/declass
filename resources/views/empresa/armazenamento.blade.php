@extends('adminlte::page')

@section('title', $empresa->nome . ' · Armazenamento')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@php
    $formatBytes = function (int $bytes): string {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2, ',', '.') . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 0, ',', '.') . ' KB';
        return $bytes . ' B';
    };

    $limitBytes = (int) ($empresa->storage_limit_bytes ?? 0);
    $usedBytes = (int) ($empresa->storage_used_bytes ?? 0);
    $percent = (int) ($empresa->storage_percent ?? 0);
    $restanteBytes = max(0, $limitBytes - $usedBytes);
    $progressClass = $percent >= 90 ? 'bg-danger' : ($percent >= 60 ? 'bg-warning' : 'bg-info');

    $moduloSelecionado = request('modulo');
    $entidadeSelecionada = request('entidade');
    $ordemTamanhoSelecionada = request('ordenar_tamanho');
@endphp

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:12px;">
                <div>
                    <div class="text-muted small text-uppercase font-weight-bold">Armazenamento da empresa</div>
                    <h3 class="card-title mb-0">{{ $empresa->nome }}</h3>
                </div>
                <span class="text-sm badge {{ $percent >= 90 ? 'badge-danger' : 'badge-success' }} p-2">{{ $percent }}% usado</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-6 col-lg-3 mb-3">
                    <div class="small text-muted">Arquivos</div>
                    <div class="h4 mb-0">{{ $stats['total_arquivos'] }}</div>
                </div>
                <div class="col-6 col-lg-3 mb-3">
                    <div class="small text-muted">Uso atual</div>
                    <div class="h4 mb-0">{{ $formatBytes($usedBytes) }}</div>
                </div>
                <div class="col-6 col-lg-3 mb-3">
                    <div class="small text-muted">Limite</div>
                    <div class="h4 mb-0">{{ $formatBytes($limitBytes) }}</div>
                </div>
                <div class="col-6 col-lg-3 mb-3">
                    <div class="small text-muted">Restante</div>
                    <div class="h4 mb-0">{{ $formatBytes($restanteBytes) }}</div>
                </div>
            </div>
            <div class="progress mb-2" style="height:10px;">
                <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ min($percent, 100) }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="text-muted small">{{ $percent }}% usado do limite contratado</div>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="gap:12px;">
            <h3 class="card-title mb-0"><i class="fas fa-folder-open mr-1"></i> Arquivos rastreados</h3>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('empresa.armazenamento', $empresa->id) }}">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label for="modulo" class="font-weight-normal">Filtrar por módulo</label>
                        <select name="modulo" id="modulo" class="form-control">
                            <option value="">Todos os módulos</option>
                            @foreach($modulos as $modulo)
                                <option value="{{ $modulo }}" @selected($moduloSelecionado === $modulo)>{{ $modulo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label for="entidade" class="font-weight-normal">Filtrar por entidade</label>
                        <select name="entidade" id="entidade" class="form-control">
                            <option value="">Todas as entidades</option>
                            @foreach($entidades as $entidade)
                                <option value="{{ $entidade }}" @selected($entidadeSelecionada === $entidade)>{{ $entidade }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3 mb-md-0">
                        <label for="ordenar_tamanho" class="font-weight-normal">Tamanho</label>
                        <select name="ordenar_tamanho" id="ordenar_tamanho" class="form-control">
                            <option value="">Recentes</option>
                            <option value="maior" @selected($ordemTamanhoSelecionada === 'maior')>Maior</option>
                            <option value="menor" @selected($ordemTamanhoSelecionada === 'menor')>Menor</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex" style="gap:8px;">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-filter mr-1"></i> Filtrar
                            </button>
                            <a href="{{ route('empresa.armazenamento', $empresa->id) }}" class="btn btn-outline-secondary">Limpar</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Arquivo</th>
                            <th>Módulo</th>
                            <th>Entidade</th>
                            <th>Campo</th>
                            <th>Tamanho</th>
                            <th>Tipo</th>
                            <th>Rastreio</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($arquivos as $arquivo)
                            @php
                                $path = (string) ($arquivo->path ?? '');
                                $disk = $arquivo->disk ?: 'public';
                                $url = $path !== '' ? asset('storage/' . $path) : null;
                                $collapseId = 'rastreio-' . $arquivo->id;
                            @endphp
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $arquivo->original_name ?? basename($path) ?: 'Arquivo' }}</div>
                                    <div class="text-muted small text-break">{{ $path ?: '—' }}</div>
                                </td>
                                <td><span class="badge badge-info">{{ $arquivo->modulo ?? 'geral' }}</span></td>
                                <td>
                                    <div class="small">
                                        <div><strong>{{ $arquivo->entidade ?? '—' }}</strong></div>
                                        <div class="text-muted">ID {{ $arquivo->entidade_id ?? '—' }}</div>
                                    </div>
                                </td>
                                <td>{{ $arquivo->campo ?? '—' }}</td>
                                <td>{{ $formatBytes((int) ($arquivo->size_bytes ?? 0)) }}</td>
                                <td>{{ $arquivo->mime_type ?? '—' }}</td>
                                <td>
                                    @if($url)
                                        <button class="btn btn-xs btn-outline-info" type="button" data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                                            <i class="fas fa-search-plus mr-1"></i> Ver rastreio
                                        </button>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($url)
                                        <a href="{{ $url }}" target="_blank" class="btn btn-xs btn-primary">
                                            <i class="fas fa-file-alt mr-1"></i> Arquivo
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            @if($url)
                                <tr class="collapse bg-light" id="{{ $collapseId }}">
                                    <td colspan="8">
                                        <div class="small text-muted">
                                            <div><strong>Contexto:</strong> {{ $arquivo->contexto ?? '—' }}</div>
                                            <div><strong>Disco:</strong> {{ $disk }}</div>
                                            <div><strong>Hash:</strong> {{ $arquivo->hash_sha1 ?? '—' }}</div>
                                            <div><strong>Criado em:</strong> {{ $arquivo->created_at?->format('d/m/Y H:i') ?? '—' }}</div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Nenhum arquivo rastreado encontrado para esta empresa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($arquivos->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">{{ $arquivos->links() }}</div>
            </div>
        @endif
    </div>
</div>
@stop
