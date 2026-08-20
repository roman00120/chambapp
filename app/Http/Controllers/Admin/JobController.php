<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $jobs = JobRequest::query()
            ->with(['client', 'professional.user', 'service', 'payment', 'category'])
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->string('q')->toString(), function ($query, $term): void {
                $query->where(function ($search) use ($term): void {
                    $search->where('title', 'like', '%'.$term.'%')->orWhere('id', is_numeric($term) ? (int) $term : 0);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.jobs.index', compact('jobs'));
    }

    public function show(JobRequest $job): View
    {
        return view('admin.jobs.show', [
            'job' => $job->load(['client', 'professional.user', 'service.category', 'category', 'quotes.professional.user', 'invitations.professional.user', 'payment.transactions', 'review', 'dispute']),
        ]);
    }
}
