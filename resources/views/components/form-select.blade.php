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
        @php
            $optionIsRecord = is_array($option) || is_object($option);
            $isOptGroup = $optionIsRecord && is_array($option) && array_key_exists('options', $option);
            if ($isOptGroup) {
                $groupLabel = data_get($option, 'label', data_get($option, 'nome', $key));
            }
            $optionValue = $optionIsRecord
                ? data_get($option, 'id', $key)
                : $key;
            $optionLabel = $optionIsRecord
                ? data_get($option, 'nome', data_get($option, 'name', data_get($option, 'label', $optionValue)))
                : $option;
        @endphp
        @if($isOptGroup)
            <optgroup label="{{ $groupLabel }}">
                @foreach(data_get($option, 'options', []) as $groupKey => $groupOption)
                    @php
                        $groupOptionIsRecord = is_array($groupOption) || is_object($groupOption);
                        $groupOptionValue = $groupOptionIsRecord
                            ? data_get($groupOption, 'id', $groupKey)
                            : $groupKey;
                        $groupOptionLabel = $groupOptionIsRecord
                            ? data_get($groupOption, 'nome', data_get($groupOption, 'name', data_get($groupOption, 'label', $groupOptionValue)))
                            : $groupOption;
                    @endphp
                    <option value="{{ $groupOptionValue }}" @if($isSelected($groupOptionValue)) selected="selected" @endif>
                        {{ $groupOptionLabel }}
                    </option>
                @endforeach
            </optgroup>
        @else
            <option value="{{ $optionValue }}" @if($isSelected($optionValue)) selected="selected" @endif>
                {{ $optionLabel }}
            </option>
        @endif
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
