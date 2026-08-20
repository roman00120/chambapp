<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminReviewStatusRequest;
use App\Models\Review;
use App\Services\AdminAuditService;
use App\Services\ProfessionalRatingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = Review::query()
            ->with(['client', 'professional.user', 'jobRequest.service'])
            ->withCount('reports')
            ->when($request->has('hidden') && $request->input('hidden') !== '', fn ($query) => $query->where('is_hidden', $request->boolean('hidden')))
            ->when($request->input('rating'), fn ($query, $rating) => $query->where('rating', $rating))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function show(Review $review): View
    {
        return view('admin.reviews.show', ['review' => $review->load(['client', 'professional.user', 'jobRequest.service', 'reports.reporter'])]);
    }

    public function moderate(AdminReviewStatusRequest $request, Review $review, AdminAuditService $audit, ProfessionalRatingService $ratings): RedirectResponse
    {
        $action = $request->validated('action');
        if ($action === 'hide') {
            $review->forceFill([
                'is_hidden' => true,
                'hidden_by' => $request->user()->getKey(),
                'hidden_at' => now(),
                'moderation_reason' => $request->validated('reason'),
            ])->save();
        } else {
            $review->forceFill([
                'is_hidden' => false,
                'hidden_by' => null,
                'hidden_at' => null,
                'moderation_reason' => null,
            ])->save();
        }
        $ratings->recalculate($review->professional);
        $audit->record($request->user(), 'review.'.$action, $review, ['reason' => $request->validated('reason')], $request);

        return back()->with('status', $action === 'hide' ? 'Reseña ocultada de la reputación pública.' : 'Reseña restaurada.');
    }
}
