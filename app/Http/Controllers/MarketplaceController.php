<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchServicesRequest;
use App\Models\Category;
use App\Models\ProfessionalProfile;
use App\Services\ServiceSearchService;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function search(SearchServicesRequest $request, ServiceSearchService $search): View
    {
        $filters = $request->validated();
        $category = ! empty($filters['category'])
            ? Category::active()->where('slug', $filters['category'])->first()
            : null;

        return view('marketplace.search', [
            'services' => $search->search($filters),
            'categories' => Category::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']),
            'cities' => $this->cities(),
            'filters' => $filters,
            'category' => $category,
            'favoriteProfessionalIds' => auth()->check() && auth()->user()->isClient()
                ? auth()->user()->favorites()->pluck('professional_id')->all()
                : [],
        ]);
    }

    private function cities(): array
    {
        return ProfessionalProfile::query()
            ->whereNotNull('city')
            ->publiclyVisible()
            ->orderBy('city')
            ->distinct()
            ->pluck('city')
            ->all();
    }
}
