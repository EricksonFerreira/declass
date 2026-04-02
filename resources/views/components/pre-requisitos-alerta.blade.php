@props([
    'title' => 'Pre-requisitos pendentes',
    'chips' => [],
    'missingItems' => [],
    'actionLinks' => [],
    'showWhenComplete' => true,
    'icon' => 'fas fa-exclamation-triangle',
])

@php
    $hasMissingItems = !empty($missingItems);
@endphp

@if ($showWhenComplete || $hasMissingItems)
    <div class="pre-requisitos-alerta {{ $hasMissingItems ? 'pre-requisitos-alerta--warning' : 'pre-requisitos-alerta--ok' }}">
        @if (!empty($chips))
            <div class="pre-requisitos-alerta__chips">
                @foreach ($chips as $chip)
                    @php
                        $label = is_array($chip) ? (string) ($chip['label'] ?? '') : '';
                        $value = is_array($chip) ? (string) ($chip['value'] ?? '0') : '0';
                    @endphp
                    @if ($label !== '')
                        <span class="pre-requisitos-alerta__chip">{{ $label }}: {{ $value }}</span>
                    @endif
                @endforeach
            </div>
        @endif

        @if ($hasMissingItems)
            <div class="pre-requisitos-alerta__title">
                <i class="{{ $icon }} mr-1"></i>
                {{ $title }}
            </div>
            @foreach ($missingItems as $item)
                <div class="pre-requisitos-alerta__item">{{ $item }}</div>
            @endforeach
            @if (!empty($actionLinks))
                <div class="pre-requisitos-alerta__links">
                    @foreach ($actionLinks as $link)
                        @php
                            $url = is_array($link) ? (string) ($link['url'] ?? '') : '';
                            $label = is_array($link) ? (string) ($link['label'] ?? '') : '';
                        @endphp
                        @if ($url !== '' && $label !== '')
                            <a href="{{ $url }}" class="pre-requisitos-alerta__link" target="_blank" rel="noopener noreferrer">
                                {{ $label }}
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif
        @endif
    </div>
@endif

@once
    <style>
        .pre-requisitos-alerta {
            margin-bottom: 12px;
            border-radius: 10px;
            padding: 10px;
            border: 1px solid #dbe6f3;
            background: #f8fbff;
        }

        .pre-requisitos-alerta--warning {
            border: 1px dashed #f3c56a;
            background: #fffbeb;
        }

        .pre-requisitos-alerta--ok {
            border-style: solid;
        }

        .pre-requisitos-alerta__chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 6px;
        }

        .pre-requisitos-alerta__chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid #d6e2f1;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            color: #334155;
            background: #fff;
        }

        .pre-requisitos-alerta__title {
            font-weight: 700;
            color: #92400e;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .pre-requisitos-alerta__item {
            font-size: 12px;
            color: #7c2d12;
            margin-bottom: 4px;
        }

        .pre-requisitos-alerta__links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .pre-requisitos-alerta__link {
            display: inline-flex;
            align-items: center;
            border: 1px solid #2563eb;
            color: #2563eb;
            background: #fff;
            border-radius: 999px;
            padding: 5px 11px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none !important;
            transition: all .15s ease;
        }

        .pre-requisitos-alerta__link:hover {
            color: #fff;
            background: #2563eb;
            border-color: #2563eb;
        }
    </style>
@endonce
