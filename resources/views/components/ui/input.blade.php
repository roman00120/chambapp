@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'help' => null,
    'required' => false,
    'autocomplete' => null,
])

@php
    $inputId = $attributes->get('id', $name);
    $hasError = $errors->has($name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'ui-form-field']) }}>
    @if ($label)
        <label class="form-label" for="{{ $inputId }}">{{ $label }}</label>
    @endif
    <input
        {{ $attributes->except('class')->merge([
            'class' => 'form-control'.($hasError ? ' is-invalid' : ''),
            'id' => $inputId,
            'type' => $type,
            'name' => $name,
            'value' => old($name, $value),
            'placeholder' => $placeholder,
            'autocomplete' => $autocomplete,
            'required' => $required,
        ]) }}
    >
    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
