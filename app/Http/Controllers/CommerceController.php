<?php

namespace App\Http\Controllers;

use App\Models\CommerceOrder;
use App\Models\Service;
use App\Services\CommerceService;
use App\Exceptions\MercadoPagoException;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommerceController extends Controller
{
    public function featured(Request $request): View
    {
        $profile = $request->user()->professionalProfile;

        return view('professional.commerce.featured', [
            'profile' => $profile,
            'services' => $profile->services()->active()->orderBy('title')->get(),
            'prices' => config('chambapp.commerce.featured_prices'),
        ]);
    }

    public function buyFeatured(Request $request, Service $service, CommerceService $commerce): RedirectResponse
    {
        $data = $request->validate(['days' => ['required', 'integer', 'in:1,7,30']]);
        try {
            $order = $commerce->createFeaturedOrder($request->user()->professionalProfile, $service, (int) $data['days']);
            return redirect()->away($commerce->checkout($order)->checkout_url);
        } catch (MercadoPagoException|DomainException $exception) {
            return back()->withErrors(['commerce' => $exception->getMessage()]);
        }
    }

    public function store(Request $request): View
    {
        return view('professional.commerce.store', [
            'items' => config('chambapp.commerce.store_items'),
            'profile' => $request->user()->professionalProfile,
        ]);
    }

    public function buyItem(Request $request, string $item, CommerceService $commerce): RedirectResponse
    {
        try {
            $order = $commerce->createCustomizationOrder($request->user()->professionalProfile, $item);
            return redirect()->away($commerce->checkout($order)->checkout_url);
        } catch (MercadoPagoException|DomainException $exception) {
            return back()->withErrors(['commerce' => $exception->getMessage()]);
        }
    }

    public function purchaseReturn(string $state): View
    {
        return view('payments.return', ['state' => $state]);
    }
}
