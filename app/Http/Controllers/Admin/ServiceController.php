<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Services\AdminAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $services = Service::query()->with(['professional.user', 'category'])->withCount('reports')
            ->when($request->string('q')->toString(), fn ($query, $q) => $query->where('title', 'like', '%'.$q.'%'))
            ->when($request->input('category'), fn ($query, $category) => $query->where('category_id', $category))
            ->when($request->input('professional'), fn ($query, $professional) => $query->where('professional_id', $professional))
            ->when($request->has('active') && $request->input('active') !== '', fn ($query) => $query->where('is_active', (bool) $request->boolean('active')))
            ->when($request->has('featured') && $request->input('featured') !== '', fn ($query) => $query->where('is_featured', $request->boolean('featured')))
            ->when($request->boolean('reported'), fn ($query) => $query->whereHas('reports', fn ($report) => $report->where('status', 'pending')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.services.index', ['services' => $services, 'categories' => Category::orderBy('name')->get(['id', 'name'])]);
    }

    public function show(Service $service): View
    {
        return view('admin.services.show', ['service' => $service->load(['professional.user', 'category', 'reports.reporter'])]);
    }

    public function moderate(Request $request, Service $service, AdminAuditService $audit): RedirectResponse
    {
        $request->validate(['action' => ['required', 'in:activate,deactivate,feature,unfeature']]);
        $action = $request->string('action')->toString();
        $attributes = match ($action) {
            'activate' => ['is_active' => true],
            'deactivate' => ['is_active' => false],
            'feature' => ['is_featured' => true],
            'unfeature' => ['is_featured' => false],
        };
        $service->forceFill($attributes)->save();
        $audit->record($request->user(), 'service.'.$action, $service, $attributes, $request);

        return back()->with('status', 'Servicio actualizado correctamente.');
    }
}
