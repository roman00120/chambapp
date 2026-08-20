@if (session('status'))
    <div class="container pt-3">
        <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
    </div>
@endif

@if (session('error'))
    <div class="container pt-3">
        <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
    </div>
@endif
