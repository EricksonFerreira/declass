@props([
    'label',
    'for' => null,
    'required' => false,
    'class' => '',
    'error' => '',
    'hint' => null,
    'icon' => null,
    'iconHint' => null,
    'iconClass' => 'fas fa-info-circle text-muted',
])

@if ($label)
    <label
        @if($for) for="{{ $for }}" @endif
        {!! $attributes->merge([
            'class' => trim(($class ?: '') . ' text-xs')
        ]) !!}
        style="display:inline-flex;align-items:center;gap:.25rem;flex-wrap:wrap;"
    >
        {{ $label }} 

        @if (!empty($icon))
            <i
                class="{{ $iconClass }}"
                title="{{ $iconHint ?? $hint ?? '' }}"
                data-toggle="tooltip"
                data-placement="top"
                aria-label="{{ $iconHint ?? $hint ?? '' }}"
                style="cursor: help;"
            ></i>
        @endif

        @if ($required)
            <span class="text-danger">*</span>
        @endif

        @if (!empty($hint))
            <i
                class="fas fa-info-circle ml-1 text-muted"
                title="{{ $hint }}"
                data-toggle="tooltip"
                data-placement="top"
                aria-label="{{ $hint }}"
                style="cursor: help;"
            ></i>
        @endif

        @if ($error && $errors->has($error))
            <x-form-errors
                :name="$error"
                :message="$errors->first($error)"
            />
        @endif
    </label>
@endif