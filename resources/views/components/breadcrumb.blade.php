@section('content_top_nav_left')
    <li class="nav-item navbar-breadcrumb d-none d-sm-flex align-items-center">
        <ol class="breadcrumb bg-transparent mb-0 p-0 flex-nowrap">
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
                    @if (!$loop->last && $url)
                        <a href="{{ $url }}">{{ $name }}</a>
                    @else
                        {{ $name }}
                    @endif
                </li>
            @endforeach
        </ol>
    </li>
@endsection
