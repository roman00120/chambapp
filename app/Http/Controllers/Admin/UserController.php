<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminStatusRequest;
use App\Http\Requests\AdminUserIndexRequest;
use App\Models\User;
use App\Services\AdminAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(AdminUserIndexRequest $request): View
    {
        $filters = $request->validated();
        $users = User::query()
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(function ($search) use ($term): void {
                $search->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%')
                    ->orWhere('phone', 'like', '%'.$term.'%');
            }))
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'filters'));
    }

    public function show(User $user): View
    {
        $user->load('professionalProfile');

        return view('admin.users.show', [
            'user' => $user,
            'jobs' => $user->jobRequests()->with(['professional.user', 'service'])->latest()->paginate(10, ['*'], 'jobs_page'),
            'payments' => $user->payments()->with('jobRequest')->latest()->paginate(10, ['*'], 'payments_page'),
        ]);
    }

    public function status(AdminStatusRequest $request, User $user, AdminAuditService $audit): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'No puedes cambiar el estado de tu propia cuenta administrativa.']);
        }

        $status = UserStatus::from($request->validated('status'));
        $previous = $user->status->value;
        $user->forceFill(['status' => $status])->save();
        $audit->record($request->user(), 'user.status_changed', $user, ['from' => $previous, 'to' => $status->value], $request);

        return back()->with('status', match ($status) {
            UserStatus::ACTIVE => 'Usuario activado correctamente.',
            UserStatus::SUSPENDED => 'Usuario suspendido correctamente.',
            UserStatus::BLOCKED => 'Usuario bloqueado correctamente.',
        });
    }
}
