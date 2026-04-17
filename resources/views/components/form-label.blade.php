@props(['label', 'for','required', 'class' => '', 'error' => '', 'hint' => null])
@if ($label)
    <label {!! $attributes->merge(['class' => $class ? $class . ' text-xs' : 'text-xs']) !!}>
        {{ $label }}

        @if (isset($required) && $required == true)
            <span style="color: red;">*</span>
        @endif

        @if (!empty($hint))
            <i class="fas fa-info-circle ml-1 text-muted"
                title="{{ $hint }}"
                data-toggle="tooltip"
                data-placement="top"
                aria-label="{{ $hint }}"></i>
        @endif

        @if ($error && $errors->has($error))
            <x-form-errors :name="$error" :message="$errors->first($error)" />
        @endif
    </label>
@endif
