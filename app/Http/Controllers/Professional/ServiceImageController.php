<?php

namespace App\Http\Controllers\Professional;

use App\Http\Controllers\Controller;
use App\Models\ServiceImage;
use App\Services\ServiceImageManager;
use Illuminate\Http\RedirectResponse;

class ServiceImageController extends Controller
{
    public function __construct(private readonly ServiceImageManager $images) {}

    public function destroy(ServiceImage $serviceImage): RedirectResponse
    {
        $serviceImage->load('service.professional');
        $this->authorize('delete', $serviceImage);
        $this->images->remove($serviceImage);

        return back()->with('status', 'Imagen eliminada correctamente.');
    }
}
