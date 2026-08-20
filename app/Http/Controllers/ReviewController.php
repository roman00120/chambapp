<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewReportRequest;
use App\Http\Requests\StoreReviewRequest;
use App\Models\JobRequest;
use App\Models\ProfessionalProfile;
use App\Models\Report;
use App\Models\Review;
use App\Services\ReviewService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function create(Request $request, JobRequest $jobRequest): View
    {
        $this->authorize('create', [Review::class, $jobRequest]);

        return view('reviews.create', [
            'jobRequest' => $jobRequest->load(['professional.user', 'service']),
        ]);
    }

    public function store(StoreReviewRequest $request, JobRequest $jobRequest, ReviewService $reviews): RedirectResponse
    {
        $this->authorize('create', [Review::class, $jobRequest]);

        try {
            $reviews->create($jobRequest, $request->user(), (int) $request->validated('rating'), $request->validated('comment'));
        } catch (DomainException $exception) {
            return back()->withErrors(['review' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('job-requests.show', $jobRequest)->with('status', 'Reseña publicada correctamente.');
    }

    public function index(ProfessionalProfile $professionalProfile): View
    {
        $professionalProfile->load('user');
        abort_unless($professionalProfile->isPubliclyVisible(), 404);

        return view('reviews.index', [
            'profile' => $professionalProfile,
            'reviews' => $professionalProfile->reviews()
                ->visible()
                ->with(['client:id,name', 'jobRequest:id,service_id', 'jobRequest.service:id,title'])
                ->latest()
                ->paginate(10),
        ]);
    }

    public function report(StoreReviewReportRequest $request, Review $review): RedirectResponse
    {
        $review->load('professional');
        $this->authorize('report', $review);
        Report::create([
            'reporter_id' => $request->user()->getKey(),
            'reportable_type' => $review->getMorphClass(),
            'reportable_id' => $review->getKey(),
            'reason' => $request->validated('reason'),
            'description' => $request->validated('description'),
        ]);

        return back()->with('status', 'El reporte fue enviado a revisión.');
    }
}
