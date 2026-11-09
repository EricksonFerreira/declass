@extends('adminlte::page')
@section('title', $titulo)
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Documentos que a escola pediu — envie a foto ou o PDF por aqui" />
@stop

@section('content')
<div class="card">
    <div class="list-group list-group-flush">
        @forelse($solicitacoes as $doc)
            @php
                $cores = ['pendente' => 'warning', 'enviado' => 'info', 'aceito' => 'success', 'devolvido' => 'danger'];
                $labels = \App\Models\DocumentoSolicitacao::STATUS;
            @endphp
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div>
                        <strong>{{ $doc->tipo }}</strong>
                        <div class="text-muted small">
                            {{ $doc->aluno->pessoa->nome ?? '' }}
                            @if($doc->prazo) · prazo {{ $doc->prazo->format('d/m/Y') }} @endif
                        </div>
                        @if($doc->descricao)<p class="mb-1 mt-1 small">{{ $doc->descricao }}</p>@endif
                    </div>
                    <span class="badge badge-{{ $cores[$doc->status] ?? 'secondary' }}">{{ $labels[$doc->status] ?? $doc->status }}</span>
                </div>

                @if($doc->status === 'devolvido' && $doc->observacao)
                    <div class="alert alert-danger mt-2 mb-2 py-2 small"><strong>Motivo da devolução:</strong> {{ $doc->observacao }}</div>
                @endif

                @if(in_array($doc->status, ['pendente', 'devolvido']))
                    <form method="POST" action="{{ route('portal.documentos.enviar', $doc->id) }}" enctype="multipart/form-data" class="mt-2">
                        @csrf
                        <div class="input-group input-group-sm">
                            <div class="custom-file">
                                <input type="file" name="arquivo" class="custom-file-input" id="arq{{ $doc->id }}" accept=".jpg,.jpeg,.png,.pdf" required>
                                <label class="custom-file-label" for="arq{{ $doc->id }}">Foto ou PDF (até 8MB)</label>
                            </div>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-upload"></i> Enviar</button>
                            </div>
                        </div>
                    </form>
                @elseif($doc->arquivo_path)
                    <small class="text-muted"><i class="fas fa-paperclip mr-1"></i> Arquivo enviado em {{ $doc->enviado_em?->format('d/m/Y H:i') }}</small>
                @endif
            </div>
        @empty
            <div class="list-group-item text-muted text-center py-4">
                Nenhum documento solicitado no momento.
            </div>
        @endforelse
    </div>
</div>
@stop

@section('js')
<script>
    document.querySelectorAll('.custom-file-input').forEach(function (input) {
        input.addEventListener('change', function () {
            this.nextElementSibling.textContent = this.files[0] ? this.files[0].name : 'Foto ou PDF (até 8MB)';
        });
    });
</script>
@stop
