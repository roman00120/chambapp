<?php

namespace App\Http\Controllers;

use App\Enums\InvitationStatus;
use App\Http\Requests\UpdateAvailabilityRequest;
use App\Http\Requests\UpdateProfessionalLocationRequest;
use App\Models\JobInvitation;
use App\Services\OnDemandMatchingService;
use App\Services\ProfessionalAvailabilityService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalOpportunityController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('professional.jobs.index');
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

    public function availability(UpdateAvailabilityRequest $request, ProfessionalAvailabilityService $availability): RedirectResponse
    {
        $profile = $request->user()->professionalProfile;
        try {
            $updated = $availability->update($profile, $request->validated());
        } catch (DomainException $exception) {
            return back()->withErrors(['availability' => $exception->getMessage()]);
        }

        return back()->with('status', $updated->is_available ? 'Disponibilidad activada.' : 'Disponibilidad pausada.');
    }

    public function location(UpdateProfessionalLocationRequest $request): RedirectResponse
    {
        $request->user()->professionalProfile?->forceFill([
            'last_latitude' => $request->validated('latitude'), 'last_longitude' => $request->validated('longitude'), 'location_updated_at' => now(),
        ])->save();

        return back()->with('status', 'Ubicación actualizada.');
    }
}
