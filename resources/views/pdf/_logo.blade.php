@php
    // Logo para PDFs: prioriza a escola e cai para a empresa (rede)
    // quando a escola nao tem logo. O DomPDF nao busca URL remota
    // por padrao, entao a imagem vai embutida em base64.
    $escolaPdf = $escola ?? null;
    $empresaPdf = $empresa ?? $escolaPdf?->empresa;
    $logoPath = null;

    foreach ([$escolaPdf?->logo_path, $empresaPdf?->logo_path] as $path) {
        if (is_string($path) && trim($path) !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            $logoPath = $path;
            break;
        }
    }

    $logoUri = null;
    if ($logoPath) {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $logoUri = 'data:'.$disk->mimeType($logoPath).';base64,'.base64_encode($disk->get($logoPath));
    }
@endphp
@if($logoUri)
    <img src="{{ $logoUri }}" alt="" style="{{ $style ?? 'display:block;max-height:52px;max-width:170px;margin:0 auto 6px;' }}">
@endif
