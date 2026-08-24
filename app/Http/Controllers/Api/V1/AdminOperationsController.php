<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\JobDispute;
use App\Models\JobRequest;
use App\Models\Payment;
use App\Models\Report;
use App\Models\Review;
use App\Models\Service;
use App\Services\AdminAuditService;
use App\Services\ProfessionalRatingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminOperationsController extends Controller
{
    public function index(Request $request, string $section): JsonResponse
    {
        abort_unless(in_array($section, ['categories', 'services', 'jobs', 'payments', 'commissions', 'reports', 'reviews', 'disputes'], true), 404);
        $query = $this->query($section, $request);
        $items = $query->latest()->paginate(30);
        $payload = ['data' => $items->getCollection()->map(fn ($item) => $this->serialize($section, $item)), 'meta' => ['total' => $items->total()]];
        if ($section === 'commissions') {
            $base = Payment::where('status', PaymentStatus::APPROVED->value);
            $payload['summary'] = ['gross' => (string) (clone $base)->sum('gross_amount'), 'fees' => (string) (clone $base)->sum('platform_fee'), 'professional_amount' => (string) (clone $base)->sum('professional_amount')];
        }
        return response()->json($payload);
    }

    public function storeCategory(Request $request, AdminAuditService $audit): JsonResponse
    {
        $data = $this->categoryData($request);
        $base = Str::slug($data['name']) ?: 'categoria'; $slug = $base; $suffix = 2;
        while (Category::where('slug', $slug)->exists()) $slug = $base.'-'.$suffix++;
        $category = Category::create([...$data, 'slug' => $slug]);
        $audit->record($request->user(), 'category.created', $category, [], $request);
        return response()->json(['message' => 'Categoría creada.', 'data' => $this->serialize('categories', $category->loadCount('services'))], 201);
    }

    public function updateCategory(Request $request, Category $category, AdminAuditService $audit): JsonResponse
    {
        $category->forceFill($this->categoryData($request))->save();
        $audit->record($request->user(), 'category.updated', $category, [], $request);
        return response()->json(['message' => 'Categoría actualizada.', 'data' => $this->serialize('categories', $category->loadCount('services'))]);
    }

    public function toggleCategory(Request $request, Category $category, AdminAuditService $audit): JsonResponse
    {
        $category->forceFill(['is_active' => ! $category->is_active])->save();
        $audit->record($request->user(), 'category.status_changed', $category, ['is_active' => $category->is_active], $request);
        return response()->json(['message' => 'Estado actualizado.', 'data' => $this->serialize('categories', $category->loadCount('services'))]);
    }

    public function moderateService(Request $request, Service $service, AdminAuditService $audit): JsonResponse
    {
        $data = $request->validate(['action' => ['required', Rule::in(['activate', 'deactivate', 'feature', 'unfeature'])]]);
        $attributes = match ($data['action']) { 'activate' => ['is_active' => true], 'deactivate' => ['is_active' => false], 'feature' => ['is_featured' => true], default => ['is_featured' => false] };
        $service->forceFill($attributes)->save();
        $audit->record($request->user(), 'service.'.$data['action'], $service, $attributes, $request);
        return response()->json(['message' => 'Servicio actualizado.']);
    }

    public function reportStatus(Request $request, Report $report, AdminAuditService $audit): JsonResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['pending', 'reviewing', 'resolved', 'dismissed'])]]);
        $report->forceFill(['status' => $data['status'], 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()])->save();
        $audit->record($request->user(), 'report.'.$data['status'], $report, $data, $request);
        return response()->json(['message' => 'Reporte actualizado.']);
    }

    public function moderateReview(Request $request, Review $review, AdminAuditService $audit, ProfessionalRatingService $ratings): JsonResponse
    {
        $data = $request->validate(['action' => ['required', Rule::in(['hide', 'restore'])], 'reason' => ['nullable', 'string', 'max:500', Rule::requiredIf($request->input('action') === 'hide')]]);
        $hidden = $data['action'] === 'hide';
        $review->forceFill(['is_hidden' => $hidden, 'hidden_by' => $hidden ? $request->user()->id : null, 'hidden_at' => $hidden ? now() : null, 'moderation_reason' => $hidden ? $data['reason'] : null])->save();
        $ratings->recalculate($review->professional);
        $audit->record($request->user(), 'review.'.$data['action'], $review, ['reason' => $data['reason'] ?? null], $request);
        return response()->json(['message' => 'Reseña actualizada.']);
    }

    public function disputeStatus(Request $request, JobDispute $dispute, AdminAuditService $audit): JsonResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['open', 'reviewing', 'resolved', 'rejected'])]]);
        $closed = in_array($data['status'], ['resolved', 'rejected'], true);
        $dispute->forceFill(['status' => $data['status'], 'resolved_by' => $closed ? $request->user()->id : null, 'resolved_at' => $closed ? now() : null])->save();
        $audit->record($request->user(), 'dispute.'.$data['status'], $dispute, $data, $request);
        return response()->json(['message' => 'Disputa actualizada sin modificar el pago.']);
    }

    private function query(string $section, Request $request): Builder
    {
        $q = trim($request->string('q')->toString()); $status = $request->string('status')->toString();
        return match ($section) {
            'categories' => Category::query()->withCount('services')->orderBy('sort_order'),
            'services' => Service::query()->with(['professional.user', 'category'])->withCount('reports')->when($q, fn ($x) => $x->where('title', 'like', '%'.$q.'%')),
            'jobs' => JobRequest::query()->with(['client', 'professional.user', 'category', 'payment'])->when($status, fn ($x) => $x->where('status', $status)),
            'payments' => Payment::query()->with(['client', 'professional.user', 'jobRequest'])->when($status, fn ($x) => $x->where('status', $status)),
            'commissions' => Payment::query()->with(['professional.user', 'jobRequest'])->where('status', PaymentStatus::APPROVED->value),
            'reports' => Report::query()->with('reporter')->when($status, fn ($x) => $x->where('status', $status)),
            'reviews' => Review::query()->with(['client', 'professional.user'])->withCount('reports'),
            'disputes' => JobDispute::query()->with(['jobRequest.client', 'jobRequest.professional.user', 'opener'])->when($status, fn ($x) => $x->where('status', $status)),
        };
    }

    private function serialize(string $section, $item): array
    {
        return match ($section) {
            'categories' => ['id' => $item->id, 'title' => $item->name, 'subtitle' => ($item->services_count ?? 0).' servicios', 'status' => $item->is_active ? 'active' : 'inactive', 'sort_order' => $item->sort_order],
            'services' => ['id' => $item->id, 'title' => $item->title, 'subtitle' => ($item->professional?->user?->name ?? 'Sin profesional').' · '.($item->category?->name ?? 'Sin categoría'), 'status' => $item->is_active ? 'active' : 'inactive', 'featured' => (bool) $item->is_featured, 'reports' => $item->reports_count],
            'jobs' => ['id' => $item->id, 'title' => '#'.$item->id.' '.($item->title ?: 'Chamba'), 'subtitle' => ($item->client?->name ?? 'Sin cliente').' · '.($item->professional?->user?->name ?? 'Sin asignar'), 'status' => $item->status->value],
            'payments', 'commissions' => [
                'id' => $item->id,
                'title' => '$'.($item->customer_total ?? $item->gross_amount).' MXN',
                'subtitle' => ($item->client?->name ?? $item->professional?->user?->name ?? 'Pago').' · Ref. '.($item->external_reference ?? 'N/D'),
                'status' => $item->status->value,
                'economic_model_version' => $item->economic_model_version ?? 'single_platform_fee_15',
                'base_amount' => (string) ($item->base_amount ?? $item->gross_amount),
                'client_service_fee' => (string) ($item->client_service_fee ?? '0.00'),
                'professional_commission' => (string) ($item->professional_commission ?? $item->platform_fee),
                'customer_total' => (string) ($item->customer_total ?? $item->gross_amount),
                'platform_gross_fee' => (string) ($item->platform_gross_fee ?? $item->platform_fee),
                'professional_amount_before_external_costs' => (string) ($item->professional_amount_before_external_costs ?? $item->professional_amount),
                'provider_fee' => $item->provider_fee !== null ? (string) $item->provider_fee : null,
                'refunded_amount' => (string) $item->refunded_amount,
            ],
            'reports' => ['id' => $item->id, 'title' => $item->reason, 'subtitle' => ($item->reporter?->name ?? 'Usuario').' · '.class_basename($item->reportable_type), 'status' => $item->status],
            'reviews' => ['id' => $item->id, 'title' => str_repeat('★', $item->rating).' '.($item->comment ?: 'Sin comentario'), 'subtitle' => ($item->client?->name ?? 'Cliente').' → '.($item->professional?->user?->name ?? 'Profesional'), 'status' => $item->is_hidden ? 'hidden' : 'visible', 'reports' => $item->reports_count],
            'disputes' => ['id' => $item->id, 'title' => 'Disputa #'.$item->id.' · Chamba #'.$item->job_request_id, 'subtitle' => $item->reason, 'status' => $item->status->value],
        };
    }

    private function categoryData(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:100'], 'description' => ['nullable', 'string', 'max:500'], 'sort_order' => ['required', 'integer', 'min:0', 'max:10000'], 'is_active' => ['required', 'boolean']]);
    }
}
