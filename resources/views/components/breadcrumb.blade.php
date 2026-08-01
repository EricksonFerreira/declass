<ol class="breadcrumb page_bradcam mb-0">
    @foreach ($breadcrumbs as $breadcrumb)
        @php
            $name = is_array($breadcrumb)
                ? ($breadcrumb['name'] ?? '')
                : (method_exists($breadcrumb, 'getName') ? $breadcrumb->getName() : ($breadcrumb->name ?? ''));
            $url = is_array($breadcrumb)
                ? ($breadcrumb['url'] ?? null)
                : (method_exists($breadcrumb, 'getUrl') ? $breadcrumb->getUrl() : ($breadcrumb->url ?? null));
        @endphp
        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
            @if (!$loop->last)
                <a href="{{ $url }}">{{ $name }}</a>
            @else
                {{ $name }}
            @endif
        </li>
    @endforeach
</ol>
