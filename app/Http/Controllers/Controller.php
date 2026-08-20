<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;

abstract class Controller
{
    protected function authorize($ability, $arguments = []): void
    {
        Gate::authorize($ability, $arguments);
    }
}
