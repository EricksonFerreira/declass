@php
    // Modelo de layout escolhido na tela "Site público" da empresa.
    $modelo = array_key_exists($empresa->site_modelo ?? '', \App\Models\Empresa::SITE_MODELOS)
        ? $empresa->site_modelo
        : 'classico';
@endphp
@include('site.modelos.'.$modelo)
