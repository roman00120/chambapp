<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Http\Requests\StoreJobRequestRequest;
use App\Models\JobRequest;
use App\Models\Service;
use App\Notifications\ChambappNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientJobRequestController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('status', 'all')->toString();
        $jobs = JobRequest::query()
            ->with(['service', 'professional.user'])
            ->where('client_id', $request->user()->getKey())
            ->when($filter !== 'all', fn ($query) => $query->whereIn('status', $this->statusesFor($filter)))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('jobs.client.index', ['jobs' => $jobs, 'filter' => $filter]);
    }

    public function create(Service $service): View
    {
        $service->load(['category', 'professional.user']);
        abort_unless($this->isPublicService($service), 404);

        return view('jobs.create', compact('service'));
    }

    public function store(StoreJobRequestRequest $request, Service $service): RedirectResponse
    {
        $service->load(['category', 'professional.user']);
        abort_unless($this->isPublicService($service), 404);

        if ($service->professional?->user_id === $request->user()->getKey()) {
            return back()->withErrors(['service' => 'No puedes solicitar tu propio servicio.'])->withInput();
        }

        $jobRequest = JobRequest::query()->create([
            'client_id' => $request->user()->getKey(),
            'professional_id' => $service->professional_id,
            'service_id' => $service->getKey(),
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'requested_date' => $request->validated('requested_date'),
            'address' => $request->validated('address'),
            'city' => $request->validated('city'),
            'state' => $request->validated('state'),
            'postal_code' => $request->validated('postal_code'),
            'status' => 'pending',
        ]);
        $service->professional?->user?->notify(new ChambappNotification(
            'job_requested',
            'Nueva solicitud de servicio',
            $jobRequest->title,
            route('job-requests.show', $jobRequest),
        ));

        return redirect()->route('job-requests.show', $jobRequest)
            ->with('status', 'Solicitud enviada correctamente.');
    }

    private function statusesFor(string $filter): array
    {
        return match ($filter) {
            'pending' => ['pending'],
            'active' => ['accepted', 'awaiting_payment', 'in_progress', 'awaiting_confirmation'],
            'completed' => ['completed'],
            'cancelled' => ['cancelled'],
            default => ['pending', 'accepted', 'awaiting_payment', 'rejected', 'in_progress', 'awaiting_confirmation', 'completed', 'cancelled'],
        };
    }

    private function isPublicService(Service $service): bool
    {
        return $service->is_active
            && $service->category?->is_active === true
            && $service->professional?->verification_status === VerificationStatus::VERIFIED
            && $service->professional?->user?->status === UserStatus::ACTIVE
            && $service->professional?->user?->role === UserRole::PROFESSIONAL;
    }
}
