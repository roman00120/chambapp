<?php

namespace App\Http\Controllers;

use App\Models\JobRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalJobRequestController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $request->user()->professionalProfile;
        $filter = $request->string('status', 'all')->toString();
        $jobs = JobRequest::query()
            ->with(['service', 'client'])
            ->where('professional_id', $profile?->getKey())
            ->when($filter !== 'all', fn ($query) => $query->whereIn('status', $this->statusesFor($filter)))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('jobs.professional.index', ['jobs' => $jobs, 'filter' => $filter]);
    }

    private function statusesFor(string $filter): array
    {
        return match ($filter) {
            'pending' => ['pending'],
            'active' => ['accepted', 'awaiting_payment', 'in_progress', 'awaiting_confirmation'],
            'history' => ['rejected', 'completed', 'cancelled'],
            default => ['pending', 'accepted', 'awaiting_payment', 'rejected', 'in_progress', 'awaiting_confirmation', 'completed', 'cancelled'],
        };
    }
}
