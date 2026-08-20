@props(['user' => null, 'src' => null, 'name' => null, 'size' => 'md'])

@php
    $avatarName = $name ?? ($user?->name ?? 'Chambapp');
    $initials = collect(preg_split('/\s+/', trim($avatarName)))
        ->filter()
        ->take(2)
        ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->join('');
    $avatarSrc = $src ?? $user?->profile_photo ?? null;
    $avatarUrl = $avatarSrc && preg_match('/^(https?:\/\/|\/)/', $avatarSrc)
        ? $avatarSrc
        : ($avatarSrc ? \Illuminate\Support\Facades\Storage::disk('public')->url($avatarSrc) : null);
@endphp

<span {{ $attributes->merge(['class' => 'ui-avatar ui-avatar--'.$size]) }}>
    @if ($avatarUrl)
        <img src="{{ $avatarUrl }}" alt="Foto de {{ $avatarName }}" loading="lazy">
    @else
        <span aria-hidden="true">{{ $initials }}</span>
    @endif
    <span class="visually-hidden">{{ $avatarName }}</span>
</span>
