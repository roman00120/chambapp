<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImmediateJobRequestRequest;
use App\Http\Requests\StoreScheduledJobRequestRequest;
use App\Models\Category;
use App\Models\JobRequest;
use App\Services\JobRequestService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientOnDemandController extends Controller
{
    public function create(): View
    {
        return view('client.on-demand.create', [
            'categories' => Category::query()->active()->with(['services' => fn ($query) => $query->active()->with('professional.user')])->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreImmediateJobRequestRequest $request, JobRequestService $jobs): RedirectResponse
    {
        $job = $jobs->createImmediate($request->user(), $request->validated(), $request->file('photos', []));

        return redirect()->route('job-requests.show', $job)->with('status', 'Solicitud creada correctamente.');
    }

    public function scheduledCreate(): View
    {
        return view('client.scheduled.create', [
            'categories' => Category::query()->active()->with(['services' => fn ($query) => $query->active()->with('professional.user')])->orderBy('sort_order')->get(),
        ]);
    }

    public function scheduledStore(StoreScheduledJobRequestRequest $request, JobRequestService $jobs): RedirectResponse
    {
        $job = $jobs->createScheduled($request->user(), $request->validated());

        return redirect()->route('job-requests.show', $job)->with('status', 'Solicitud programada correctamente.');
    }

    public function show(Request $request, JobRequest $jobRequest): RedirectResponse
    {
        abort_unless($jobRequest->client_id === $request->user()->getKey(), 404);

        return redirect()->route('job-requests.show', $jobRequest);
    }

    public function status(Request $request, JobRequest $jobRequest): JsonResponse
    {
        abort_unless($jobRequest->client_id === $request->user()->getKey(), 404);
        $professional = $jobRequest->professional;

        return response()->json([
            'status' => $jobRequest->status?->value,
            'search_round' => $jobRequest->search_round,
            'search_radius_km' => $jobRequest->search_radius_km,
            'expires_at' => $jobRequest->search_expires_at?->toIso8601String(),
            'professional' => $professional ? [
                'name' => $professional->user?->name,
                'photo' => $professional->profile_photo,
                'rating' => $professional->average_rating,
                'completed_jobs' => $professional->total_completed_jobs,
                'verified' => $professional->hasVerifiedIdentity(),
            ] : null,
        ]);
    }

    public function cancel(Request $request, JobRequest $jobRequest): RedirectResponse
    {
        abort_unless($jobRequest->client_id === $request->user()->getKey(), 404);
        if ($jobRequest->isCancellable()) {
            $jobRequest->update(['status' => \App\Enums\JobStatus::CANCELLED]);
        }

        return redirect()->route('job-requests.show', $jobRequest)->with('status', 'Solicitud cancelada.');
    }

    public function searchAgain(Request $request, JobRequest $jobRequest): RedirectResponse
    {
        return redirect()->route('marketplace.search');
    }
}
