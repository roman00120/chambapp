<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\JobStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\JobDispute;
use App\Models\JobRequest;
use App\Models\Payment;
use App\Models\ProfessionalProfile;
use App\Models\Report;
use App\Models\Service;
use App\Models\User;
use App\Notifications\ChambappNotification;
use App\Services\AdminAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $approvedPayments = Payment::query()->where('status', PaymentStatus::APPROVED->value);

        return response()->json(['data' => [
            'users' => User::count(),
            'clients' => User::where('role', UserRole::CLIENT->value)->count(),
            'professionals' => User::where('role', UserRole::PROFESSIONAL->value)->count(),
            'pending_verifications' => ProfessionalProfile::where('verification_status', VerificationStatus::PENDING->value)->count(),
            'verified_professionals' => ProfessionalProfile::where('verification_status', VerificationStatus::VERIFIED->value)->count(),
            'active_services' => Service::active()->count(),
            'pending_jobs' => JobRequest::where('status', JobStatus::PENDING->value)->count(),
            'in_progress_jobs' => JobRequest::where('status', JobStatus::IN_PROGRESS->value)->count(),
            'completed_jobs' => JobRequest::where('status', JobStatus::COMPLETED->value)->count(),
            'approved_payments' => (clone $approvedPayments)->count(),
            'gross_amount' => (string) (clone $approvedPayments)->sum('gross_amount'),
            'platform_fees' => (string) (clone $approvedPayments)->sum('platform_fee'),
            'open_disputes' => JobDispute::whereIn('status', ['open', 'reviewing'])->count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'categories' => Category::count(),
        ]]);
    }

    public function users(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::enum(UserRole::class)],
            'status' => ['nullable', Rule::enum(UserStatus::class)],
        ]);
        $term = trim((string) ($data['q'] ?? ''));
        $users = User::query()
            ->when($term !== '', fn ($query) => $query->where(function ($search) use ($term): void {
                $search->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%')
                    ->orWhere('phone', 'like', '%'.$term.'%');
            }))
            ->when($data['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->when($data['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(30);

        return response()->json(['data' => $users->getCollection()->map(fn (User $user) => $this->userData($user)), 'meta' => [
            'current_page' => $users->currentPage(), 'last_page' => $users->lastPage(), 'total' => $users->total(),
        ]]);
    }

    public function updateUserStatus(Request $request, User $user, AdminAuditService $audit): JsonResponse
    {
        $data = $request->validate(['status' => ['required', Rule::enum(UserStatus::class)]]);
        if ($user->is($request->user())) {
            return response()->json(['message' => 'No puedes cambiar el estado de tu propia cuenta administrativa.'], 422);
        }
        $status = UserStatus::from($data['status']);
        $previous = $user->status->value;
        $user->forceFill(['status' => $status])->save();
        if ($status !== UserStatus::ACTIVE) {
            $user->tokens()->delete();
        }
        $audit->record($request->user(), 'user.status_changed', $user, ['from' => $previous, 'to' => $status->value], $request);

        return response()->json(['message' => 'Estado actualizado correctamente.', 'data' => $this->userData($user->fresh())]);
    }

    public function professionals(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'verification' => ['nullable', Rule::enum(VerificationStatus::class)],
        ]);
        $term = trim((string) ($data['q'] ?? ''));
        $professionals = ProfessionalProfile::query()->with('user')
            ->withCount(['services as active_services_count' => fn ($query) => $query->where('is_active', true)])
            ->when($term !== '', fn ($query) => $query->where(function ($search) use ($term): void {
                $search->whereHas('user', fn ($user) => $user->where('name', 'like', '%'.$term.'%')->orWhere('email', 'like', '%'.$term.'%'))
                    ->orWhere('city', 'like', '%'.$term.'%');
            }))
            ->when($data['verification'] ?? null, fn ($query, $status) => $query->where('verification_status', $status))
            ->latest()->paginate(30);

        return response()->json(['data' => $professionals->getCollection()->map(fn (ProfessionalProfile $profile) => $this->professionalData($profile)), 'meta' => [
            'current_page' => $professionals->currentPage(), 'last_page' => $professionals->lastPage(), 'total' => $professionals->total(),
        ]]);
    }

    public function updateVerification(Request $request, ProfessionalProfile $professional, AdminAuditService $audit): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([VerificationStatus::VERIFIED->value, VerificationStatus::REJECTED->value])],
            'reason' => ['nullable', 'string', 'max:500', Rule::requiredIf($request->input('status') === VerificationStatus::REJECTED->value)],
        ]);
        $status = VerificationStatus::from($data['status']);
        $professional->forceFill([
            'verification_status' => $status,
            'verified_by' => $request->user()->getKey(),
            'verified_at' => now(),
            'verification_rejection_reason' => $status === VerificationStatus::REJECTED ? $data['reason'] : null,
        ])->save();
        $audit->record($request->user(), 'professional.verification_'.$status->value, $professional, ['reason' => $data['reason'] ?? null], $request);
        $professional->user?->notify(new ChambappNotification(
            'professional_verification_'.$status->value,
            $status === VerificationStatus::VERIFIED ? 'Perfil profesional aprobado' : 'Perfil profesional rechazado',
            $status === VerificationStatus::VERIFIED ? 'Tu perfil ya puede mostrarse como verificado.' : 'Tu perfil requiere ajustes antes de ser verificado.',
            route('professional.profile.show'),
        ));

        return response()->json(['message' => 'Verificación actualizada correctamente.', 'data' => $this->professionalData($professional->fresh('user'))]);
    }

    private function userData(User $user): array
    {
        return ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone,
            'role' => $user->role->value, 'status' => $user->status->value, 'created_at' => $user->created_at?->toIso8601String()];
    }

    private function professionalData(ProfessionalProfile $profile): array
    {
        return ['id' => $profile->id, 'user_id' => $profile->user_id, 'name' => $profile->user?->name,
            'email' => $profile->user?->email, 'city' => $profile->city, 'verification_status' => $profile->verification_status->value,
            'active_services_count' => (int) ($profile->active_services_count ?? 0), 'rejection_reason' => $profile->verification_rejection_reason];
    }
}
