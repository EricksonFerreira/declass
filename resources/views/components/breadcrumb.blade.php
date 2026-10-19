@section('content_top_nav_left')
    <li class="nav-item navbar-breadcrumb d-none d-sm-flex align-items-center">
        <ol class="breadcrumb mb-0 flex-nowrap navbar-breadcrumb-badge">
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
    <style>
        .navbar-breadcrumb-badge {
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 999px;
            padding: .3rem .95rem;
        }
        .main-header .navbar-breadcrumb-badge,
        .main-header .navbar-breadcrumb-badge .breadcrumb-item { font-weight: 700 !important; }
        .main-header .navbar-breadcrumb-badge .breadcrumb-item,
        .main-header .navbar-breadcrumb-badge .breadcrumb-item a { color: #000 !important; font-weight: 700 !important; opacity: 1 !important; }
        .main-header .navbar-breadcrumb-badge .breadcrumb-item.active { color: #000 !important; font-weight: 700 !important; }
        .main-header .navbar-breadcrumb-badge .breadcrumb-item + .breadcrumb-item::before { color: #000 !important; font-weight: 700 !important; opacity: 1 !important; }
    </style>
@endsection
