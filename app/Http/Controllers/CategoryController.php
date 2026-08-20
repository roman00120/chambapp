<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchServicesRequest;
use App\Models\Category;
use App\Services\ServiceSearchService;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('marketplace.categories', [
            'categories' => Category::active()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function show(Category $category, SearchServicesRequest $request, ServiceSearchService $search): View
    {
        abort_unless($category->is_active, 404);

        $filters = $request->validated();
        $filters['category'] = $category->slug;

        return view('marketplace.category', [
            'category' => $category,
            'services' => $search->search($filters),
            'categories' => Category::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']),
            'filters' => $filters,
            'favoriteProfessionalIds' => auth()->check() && auth()->user()->isClient()
                ? auth()->user()->favorites()->pluck('professional_id')->all()
                : [],
        ]);
    }
}
