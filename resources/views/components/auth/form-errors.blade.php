@if ($errors->any())
    <x-ui.alert variant="danger" title="Revisa los datos ingresados:">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif
