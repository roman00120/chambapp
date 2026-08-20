<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['user' => null, 'src' => null, 'name' => null, 'size' => 'md']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['user' => null, 'src' => null, 'name' => null, 'size' => 'md']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $avatarName = $name ?? ($user?->name ?? 'Chambapp');
    $initials = collect(preg_split('/\s+/', trim($avatarName)))
        ->filter()
        ->take(2)
        ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->join('');
    $avatarSrc = $src ?? $user?->profile_photo ?? $user?->avatar_url ?? null;
    $avatarUrl = $avatarSrc && preg_match('/^(https?:\/\/|\/)/', $avatarSrc)
        ? $avatarSrc
        : ($avatarSrc ? \Illuminate\Support\Facades\Storage::disk('public')->url($avatarSrc) : null);
?>

<span <?php echo e($attributes->merge(['class' => 'ui-avatar ui-avatar--'.$size])); ?>>
    <?php if($avatarUrl): ?>
        <img src="<?php echo e($avatarUrl); ?>" alt="Foto de <?php echo e($avatarName); ?>" loading="lazy">
    <?php else: ?>
        <span aria-hidden="true"><?php echo e($initials); ?></span>
    <?php endif; ?>
    <span class="visually-hidden"><?php echo e($avatarName); ?></span>
</span>
<?php /**PATH C:\Users\Roman\Desktop\chambapp-master\resources\views\components\ui\avatar.blade.php ENDPATH**/ ?>