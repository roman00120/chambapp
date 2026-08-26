@props(['user' => null, 'src' => null, 'name' => null, 'size' => 'md'])

@php
    $avatarName = $name ?? ($user?->name ?? 'Chambapp');
    $initials = collect(preg_split('/\s+/', trim($avatarName)))
        ->filter()
        ->take(2)
        ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->join('');

    $avatarSrc = $src
        ?? ($user && method_exists($user, 'profilePhotoUrl') ? $user->profilePhotoUrl() : null)
        ?? $user?->professionalProfile?->profile_photo
        ?? $user?->profile_photo
        ?? $user?->avatar_url
        ?? null;

    $avatarUrl = null;
    if ($avatarSrc) {
        if (preg_match('/^https?:\/\//i', $avatarSrc)) {
            $avatarUrl = $avatarSrc;
        } elseif (str_starts_with($avatarSrc, '/')) {
            $avatarUrl = $avatarSrc;
        } else {
            $avatarUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($avatarSrc);
        }
    }
@endphp

<span {{ $attributes->merge(['class' => 'ui-avatar ui-avatar--'.$size]) }}>
    @if ($avatarUrl)
        <img src="{{ $avatarUrl }}" alt="Foto de {{ $avatarName }}" loading="lazy" onerror="this.onerror=null;this.style.display='none';var s=this.nextElementSibling;if(s)s.style.display='inline';">
        <span aria-hidden="true" style="display:none;">{{ $initials }}</span>
    @else
        <span aria-hidden="true">{{ $initials }}</span>
    @endif
    <span class="visually-hidden">{{ $avatarName }}</span>
</span>
