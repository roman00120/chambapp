<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminVerificationRequest;
use App\Models\ProfessionalProfile;
use App\Notifications\ChambappNotification;
use App\Services\AdminAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();
        $verification = $request->string('verification')->toString();
        $professionals = ProfessionalProfile::query()
            ->with(['user', 'identityVerification'])
            ->withCount(['services as active_services_count' => fn ($query) => $query->where('is_active', true), 'jobRequests as completed_jobs_count' => fn ($query) => $query->where('status', 'completed')])
            ->when($search, fn ($query) => $query->whereHas('user', fn ($user) => $user->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'))->orWhere('city', 'like', '%'.$search.'%'))
            ->when($verification, fn ($query) => $query->where('verification_status', $verification))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.professionals.index', compact('professionals', 'search', 'verification'));
    }

    public function show(ProfessionalProfile $professional): View
    {
        $professional->load([
            'user',
            'verifiedBy',
            'identityVerification.consents' => fn ($query) => $query->latest('accepted_at')->limit(5),
            'identityVerification.events' => fn ($query) => $query->latest('occurred_at')->limit(10),
            'credentials.category',
            'services.category',
            'reviews' => fn ($query) => $query->visible()->with('client')->latest()->limit(10),
            'jobRequests.client',
            'jobRequests.service',
            'payments.jobRequest',
        ]);

        return view('admin.professionals.show', compact('professional'));
    }

    public function approve(AdminVerificationRequest $request, ProfessionalProfile $professional, AdminAuditService $audit): RedirectResponse
    {
        return $this->setVerification($request, $professional, VerificationStatus::VERIFIED, $audit);
    }

    public function reject(AdminVerificationRequest $request, ProfessionalProfile $professional, AdminAuditService $audit): RedirectResponse
    {
        return $this->setVerification($request, $professional, VerificationStatus::REJECTED, $audit);
    }

    private function setVerification(AdminVerificationRequest $request, ProfessionalProfile $professional, VerificationStatus $status, AdminAuditService $audit): RedirectResponse
    {
        $professional->forceFill([
            'verification_status' => $status,
            'verified_by' => $request->user()->getKey(),
            'verified_at' => now(),
            'verification_rejection_reason' => $status === VerificationStatus::REJECTED ? $request->validated('reason') : null,
        ])->save();
        // Keep the historical audit action name for compatibility; this mutation is
        // profile moderation only and never changes identity verification records.
        $audit->record($request->user(), 'professional.verification_'.$status->value, $professional, ['reason' => $request->validated('reason')], $request);
        $professional->user?->notify(new ChambappNotification(
            'professional_verification_'.$status->value,
            $status === VerificationStatus::VERIFIED ? 'Perfil profesional aprobado' : 'Perfil profesional rechazado',
            $status === VerificationStatus::VERIFIED ? 'Tu perfil fue habilitado. Esto no significa que tu identidad esté verificada.' : 'Tu perfil requiere ajustes antes de habilitarse.',
            route('professional.profile.show'),
        ));

        return back()->with('status', $status === VerificationStatus::VERIFIED ? 'Profesional verificado correctamente.' : 'Verificación rechazada correctamente.');
    }
}
