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

        if ($service->professional?->user_id === auth()->id()) {
            abort(403, 'No puedes solicitar tu propio servicio.');
        }

        return view('jobs.create', compact('service'));
    }

    public function store(StoreJobRequestRequest $request, Service $service): RedirectResponse
    {
        $service->load(['category', 'professional.user']);
        abort_unless($this->isPublicService($service), 404);

        if ($service->professional?->user_id === $request->user()->getKey()) {
            abort(403, 'No puedes solicitar tu propio servicio.');
        }

        $money = app(\App\Services\PaymentCalculationService::class)->calculateJob((string) $service->price);
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
            'status' => 'awaiting_payment',
            'agreed_price' => $money->baseAmount,
            'economic_model_version' => $money->economicModelVersion,
            'base_amount' => $money->baseAmount,
            'client_service_fee_percent' => $money->clientServiceFeePercent,
            'client_service_fee' => $money->clientServiceFee,
            'professional_commission_percent' => $money->professionalCommissionPercent,
            'professional_commission' => $money->professionalCommission,
            'customer_total' => $money->customerTotal,
            'platform_gross_fee' => $money->platformGrossFee,
            'professional_amount_before_external_costs' => $money->professionalAmountBeforeExternalCosts,
        ]);
        $service->professional?->user?->notify(new \App\Notifications\DirectServiceRequestedNotification(
            $jobRequest->loadMissing(['client', 'service'])
        ));

        return redirect()->route('job-requests.show', $jobRequest)
            ->with('status', 'Solicitud creada correctamente. Procede con el pago para formalizar.');
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
            && ($service->professional?->isPubliclyVisible() ?? false)
            && app(\App\Services\ProfessionalIdentityVerificationService::class)->professionalCanAcceptJobs($service->professional);
    }
}
