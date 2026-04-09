@if($floating)
    <div class="form-floating">
@endif

@php
    $selectId = $attributes->get('id') ?: $id();

    $classes = 'form-control';

    if ($multiple) {
        $classes .= ' select2';
    }

    if (isset($hasError) && $hasError($name)) {
        $classes .= ' is-invalid';
    }

    $fieldName = $multiple && substr($name, -2) !== '[]'
        ? $name . '[]'
        : $name;
@endphp

@if(!$floating)
    <x-form-label
        :label="$label"
        :for="$selectId"
        :required="$attributes->has('required')"
    />
@endif

<select
    name="{{ $fieldName }}"
    id="{{ $selectId }}"
    @if($multiple) multiple @endif
    {!! $attributes->class([$classes]) !!}
>
    @if($placeholder && !$multiple)
        <option value="" disabled @if($nothingSelected()) selected="selected" @endif>
            {{ $placeholder }}
        </option>
    @endif

    @forelse($options as $key => $option)
        <option value="{{ $key }}" @if($isSelected($key)) selected="selected" @endif>
            {{ $option }}
        </option>
    @empty
        {!! $slot !!}
    @endforelse
</select>

@if($floating)
    <x-form-label :label="$label" :for="$selectId" />
@endif

@if($floating)
    </div>
@endif

{!! $help ?? null !!}

@php
    $hasErrorAndShow = function($name) {
        return session()->has('errors') && session('errors')->has($name);
    };
@endphp

@if($hasErrorAndShow($name))
    <x-form-errors :name="$name" />
@endif

@if($multiple)
    @push('js')
        <script>
            $(document).ready(function () {
                $('#{{ $selectId }}').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: @json($placeholder ?? 'Selecione...'),
                    allowClear: true,
                    closeOnSelect: false
                });
            });
        </script>
    @endpush
@endif