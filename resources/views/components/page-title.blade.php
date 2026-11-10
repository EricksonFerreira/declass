<div class="page-title d-flex justify-content-between align-items-start flex-wrap" style="gap:.5rem;">
    <div>
        <h3 class="mb-0 font-weight-bold">{{ $title }}</h3>
        @if (!empty($subtitle))
            <div class="page-title__subtitle text-muted" style="font-size:.86rem;">{{ $subtitle }}</div>
        @endif
    </div>

    <div class="d-flex align-items-center flex-wrap" style="gap:.35rem;">
        {{ $slot }}

        @if(isset($createUrl) && $createUrl && (!isset($createCan) || (auth()->check() && auth()->user()->hasPermissao($createCan))))
            <a href="{{ $createUrl }}" class="btn btn-xs btn-primary btn-create-resource"><i class="fa fa-plus" style="font-size:10px"></i> Adicionar</a>
        @endif
    </div>
</div>
