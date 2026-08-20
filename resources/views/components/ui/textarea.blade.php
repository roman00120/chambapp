@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'rows' => 4,
    'required' => false,
])

@php
    $textareaId = $attributes->get('id', $name);
    $hasError = $errors->has($name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'ui-form-field']) }}>
    @if ($label)
        <label class="form-label" for="{{ $textareaId }}">{{ $label }}</label>
    @endif
    <textarea
        {{ $attributes->except('class')->merge([
            'class' => 'form-control'.($hasError ? ' is-invalid' : ''),
            'id' => $textareaId,
            'name' => $name,
            'rows' => $rows,
            'placeholder' => $placeholder,
            'required' => $required,
        ]) }}
    >{{ old($name, $value) }}</textarea>
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
