<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImmediateJobRequestRequest;
use App\Http\Requests\StoreScheduledJobRequestRequest;
use App\Models\Category;
use App\Models\JobRequest;
use App\Services\JobRequestService;
use App\Services\OnDemandMatchingService;
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
            'categories' => Category::query()->active()->with(['services' => fn ($query) => $query->active()])->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreImmediateJobRequestRequest $request, JobRequestService $jobs): RedirectResponse
    {
        $job = $jobs->createImmediate($request->user(), $request->validated(), $request->file('photos', []));

        return redirect()->route('client.ondemand.show', $job)->with('status', 'Estamos buscando profesionales cerca de ti.');
    }

    public function scheduledCreate(): View
    {
        return view('client.scheduled.create', [
            'categories' => Category::query()->active()->with(['services' => fn ($query) => $query->active()])->orderBy('sort_order')->get(),
        ]);
    }

    public function scheduledStore(StoreScheduledJobRequestRequest $request, JobRequestService $jobs): RedirectResponse
    {
        $job = $jobs->createScheduled($request->user(), $request->validated());

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
                'verified' => $professional->hasVerifiedIdentity(),
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
}
