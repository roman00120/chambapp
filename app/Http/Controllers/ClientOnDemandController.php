<?php

namespace App\Http\Controllers;

use App\Enums\JobStatus;
use App\Enums\ServiceMode;
use App\Http\Requests\StoreImmediateJobRequestRequest;
use App\Http\Requests\StoreScheduledJobRequestRequest;
use App\Models\Category;
use App\Models\JobRequest;
use App\Models\Service;
use App\Services\OnDemandMatchingService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ClientOnDemandController extends Controller
{
    public function create(): View
    {
        return view('client.on-demand.create', [
            'categories' => Category::query()->active()->with(['services' => fn ($query) => $query->active()])->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreImmediateJobRequestRequest $request, OnDemandMatchingService $matching): RedirectResponse
    {
        $data = $request->validated();
        $service = $this->safeService($data['service_id'] ?? null, (int) $data['category_id']);
        $paths = collect($request->file('photos', []))->map(fn ($photo) => Storage::disk('local')->putFile('on-demand/'.$request->user()->getKey(), $photo))->values()->all();
        $job = JobRequest::query()->create([
            'client_id' => $request->user()->getKey(),
            'professional_id' => null,
            'service_id' => $service?->getKey(),
            'category_id' => $data['category_id'],
            'service_mode' => ServiceMode::IMMEDIATE,
            'title' => $data['title'],
            'description' => $data['description'],
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'requested_date' => now(),
            'status' => JobStatus::SEARCHING,
            'photo_paths' => $paths ?: null,
        ]);
        $matching->startSearch($job);

        return redirect()->route('client.ondemand.show', $job)->with('status', 'Estamos buscando profesionales cerca de ti.');
    }

    public function scheduledCreate(): View
    {
        return view('client.scheduled.create', [
            'categories' => Category::query()->active()->with(['services' => fn ($query) => $query->active()])->orderBy('sort_order')->get(),
        ]);
    }

    public function scheduledStore(StoreScheduledJobRequestRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $service = $this->safeService($data['service_id'] ?? null, (int) $data['category_id']);
        $job = JobRequest::query()->create([
            'client_id' => $request->user()->getKey(),
            'service_id' => $service?->getKey(),
            'category_id' => $data['category_id'],
            'service_mode' => ServiceMode::SCHEDULED,
            'title' => $data['title'],
            'description' => $data['description'],
            'address' => $data['address'], 'city' => $data['city'], 'state' => $data['state'], 'postal_code' => $data['postal_code'],
            'latitude' => $data['latitude'] ?? null, 'longitude' => $data['longitude'] ?? null,
            'requested_date' => $data['scheduled_for'], 'scheduled_for' => $data['scheduled_for'], 'scheduled_slot' => $data['scheduled_slot'],
            'status' => JobStatus::PENDING,
        ]);

        return redirect()->route('job-requests.show', $job)->with('status', 'Solicitud programada correctamente.');
    }

    public function show(Request $request, JobRequest $jobRequest, OnDemandMatchingService $matching): View
    {
        abort_unless($jobRequest->client_id === $request->user()->getKey() && $jobRequest->isImmediate(), 404);
        $jobRequest = $matching->refresh($jobRequest);
        $jobRequest->load(['category', 'service', 'professional.user', 'quotes.professional.user', 'payment']);

        return view('client.on-demand.show', compact('jobRequest'));
    }

    public function status(Request $request, JobRequest $jobRequest, OnDemandMatchingService $matching): JsonResponse
    {
        abort_unless($jobRequest->client_id === $request->user()->getKey() && $jobRequest->isImmediate(), 404);
        $job = $matching->refresh($jobRequest);
        $professional = $job->professional;
        $quote = $job->quotes()->latest()->first();

        return response()->json([
            'status' => $job->status?->value,
            'search_round' => $job->search_round,
            'search_radius_km' => $job->search_radius_km,
            'expires_at' => $job->search_expires_at?->toIso8601String(),
            'professional' => $professional ? [
                'name' => $professional->user?->name,
                'photo' => $professional->profile_photo,
                'rating' => $professional->average_rating,
                'completed_jobs' => $professional->total_completed_jobs,
                'verified' => $professional->isPubliclyVisible(),
            ] : null,
            'quote' => $quote ? ['status' => $quote->status?->value, 'amount' => $quote->amount] : null,
        ]);
    }

    public function cancel(Request $request, JobRequest $jobRequest, OnDemandMatchingService $matching): RedirectResponse
    {
        try {
            $matching->cancelSearch($jobRequest, $request->user());
        } catch (DomainException $exception) {
            return back()->withErrors(['job' => $exception->getMessage()]);
        }

        return redirect()->route('client.ondemand.show', $jobRequest)->with('status', 'Búsqueda cancelada.');
    }

    public function searchAgain(Request $request, JobRequest $jobRequest, OnDemandMatchingService $matching): RedirectResponse
    {
        try {
            $matching->searchAgain($jobRequest, $request->user());
        } catch (DomainException $exception) {
            return back()->withErrors(['job' => $exception->getMessage()]);
        }

        return redirect()->route('client.ondemand.show', $jobRequest)->with('status', 'Reiniciamos la búsqueda.');
    }

    private function safeService(?int $serviceId, int $categoryId): ?Service
    {
        return $serviceId ? Service::query()->active()->where('category_id', $categoryId)->find($serviceId) : null;
    }
}
