@props([
    'title' => null,
    'subtitle' => null,
    'showHeader' => true,
])

@once
    @push('css')
        <style>
            .card-estacao { border-radius: 14px; overflow: hidden; }
            .card-header-estacao {
                background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
                padding: 1.1rem 1.5rem;
            }
            .card-header-estacao h4 { color: #0f2740; font-weight: 700; }
        </style>
    @endpush
@endonce

<div class="card card-estacao shadow-sm border-0">
    @if ($showHeader && (!empty($title) || !empty($subtitle) || isset($actions)))
        <div class="card-header card-header-estacao border-0">
            <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
                <div>
                    @if (!empty($title))
                        <h4 class="mb-1">{{ $title }}</h4>
                    @endif
                    @if (!empty($subtitle))
                        <p class="mb-0 text-muted">{{ $subtitle }}</p>
                    @endif
                </div>
                @isset($actions)
                    <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                        {{ $actions }}
                    </div>
                @endisset
            </div>
        </div>
    @endif
    <div class="card-body p-3">
        {{ $slot }}
    </div>
</div>
