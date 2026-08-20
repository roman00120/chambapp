@props(['icon' => 'inbox', 'title', 'description' => null, 'action' => null, 'actionHref' => '#'])

<div {{ $attributes->merge(['class' => 'ui-empty-state']) }}>
    <span class="ui-empty-state__icon" aria-hidden="true"><i class="bi bi-{{ $icon }}"></i></span>
    <h2>{{ $title }}</h2>
    @if ($description)
        <p>{{ $description }}</p>
    @endif
    @if ($action)
        <x-ui.button :href="$actionHref" variant="outline">{{ $action }}</x-ui.button>
    @endif
</div>
