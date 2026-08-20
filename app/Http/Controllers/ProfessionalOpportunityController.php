<?php

namespace App\Http\Controllers;

use App\Enums\InvitationStatus;
use App\Http\Requests\UpdateAvailabilityRequest;
use App\Http\Requests\UpdateProfessionalLocationRequest;
use App\Models\JobInvitation;
use App\Services\OnDemandMatchingService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalOpportunityController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $request->user()->professionalProfile;
        $invitations = JobInvitation::query()->with(['jobRequest.category', 'jobRequest.service'])
            ->where('professional_id', $profile?->getKey())->open()->where('expires_at', '>=', now())->latest('invited_at')->paginate(12);
        JobInvitation::query()->whereIn('id', $invitations->pluck('id'))->where('status', InvitationStatus::PENDING->value)->update(['status' => InvitationStatus::VIEWED, 'viewed_at' => now()]);

        return view('professional.opportunities', compact('invitations', 'profile'));
    }

    public function status(Request $request): JsonResponse
    {
        $profile = $request->user()->professionalProfile;

        return response()->json([
            'available' => (bool) $profile?->is_available,
            'count' => $profile ? $profile->invitations()->open()->where('expires_at', '>=', now())->count() : 0,
        ]);
    }

    public function accept(Request $request, JobInvitation $invitation, OnDemandMatchingService $matching): RedirectResponse
    {
        try {
            $matching->acceptInvitation($invitation, $request->user());
        } catch (DomainException $exception) {
            return back()->withErrors(['invitation' => $exception->getMessage()]);
        }

        return redirect()->route('job-requests.show', $invitation->job_request_id)->with('status', 'Tomaste la chamba. Envía tu cotización.');
    }

    public function decline(Request $request, JobInvitation $invitation, OnDemandMatchingService $matching): RedirectResponse
    {
        try {
            $matching->declineInvitation($invitation, $request->user());
        } catch (DomainException $exception) {
            return back()->withErrors(['invitation' => $exception->getMessage()]);
        }

        return back()->with('status', 'Invitación declinada.');
    }

    public function availability(UpdateAvailabilityRequest $request): RedirectResponse
    {
        $profile = $request->user()->professionalProfile;
        $data = $request->validated();
        $updates = ['is_available' => (bool) $data['is_available'], 'service_radius_km' => $data['service_radius_km']];
        if (isset($data['latitude'], $data['longitude'])) {
            $updates += ['last_latitude' => $data['latitude'], 'last_longitude' => $data['longitude'], 'location_updated_at' => now()];
        }
        if ($updates['is_available'] && (! $profile?->last_latitude && ! isset($updates['last_latitude']))) {
            return back()->withErrors(['availability' => 'Comparte tu ubicación antes de activar disponibilidad.']);
        }
        $profile?->forceFill($updates)->save();

        return back()->with('status', $updates['is_available'] ? 'Disponibilidad activada.' : 'Disponibilidad pausada.');
    }

    public function location(UpdateProfessionalLocationRequest $request): RedirectResponse
    {
        $request->user()->professionalProfile?->forceFill([
            'last_latitude' => $request->validated('latitude'), 'last_longitude' => $request->validated('longitude'), 'location_updated_at' => now(),
        ])->save();

        return back()->with('status', 'Ubicación actualizada.');
    }
}
