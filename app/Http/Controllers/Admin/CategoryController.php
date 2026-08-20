<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCategoryRequest;
use App\Models\Category;
use App\Services\AdminAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', ['categories' => Category::withCount('services')->orderBy('sort_order')->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(AdminCategoryRequest $request, AdminAuditService $audit): RedirectResponse
    {
        $category = Category::create($request->safe()->merge(['slug' => $this->uniqueSlug($request->validated('name'))])->all());
        $audit->record($request->user(), 'category.created', $category, [], $request);

        return redirect()->route('admin.categories.index')->with('status', 'Categoría creada correctamente.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(AdminCategoryRequest $request, Category $category, AdminAuditService $audit): RedirectResponse
    {
        $category->forceFill($request->safe()->except([])->all())->save();
        $audit->record($request->user(), 'category.updated', $category, [], $request);

        return redirect()->route('admin.categories.index')->with('status', 'Categoría actualizada correctamente.');
    }

    public function toggle(Category $category, AdminAuditService $audit, Request $request): RedirectResponse
    {
        $category->forceFill(['is_active' => ! $category->is_active])->save();
        $audit->record($request->user(), 'category.status_changed', $category, ['is_active' => $category->is_active], $request);

        return back()->with('status', $category->is_active ? 'Categoría activada.' : 'Categoría desactivada.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'categoria';
        $slug = $base;
        $suffix = 2;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
