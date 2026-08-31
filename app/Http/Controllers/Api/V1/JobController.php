<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\JobStatus;
use App\Exceptions\MercadoPagoException;
use App\Exceptions\IdentityVerificationRequiredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreImmediateJobRequest;
use App\Http\Requests\Api\V1\StoreScheduledJobRequest;
use App\Http\Requests\RejectJobQuoteRequest;
use App\Http\Requests\StoreJobDisputeRequest;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\Api\V1\JobQuoteResource;
use App\Http\Resources\Api\V1\JobRequestResource;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Http\Resources\Api\V1\ProfessionalResource;
use App\Http\Resources\Api\V1\ReviewResource;
use App\Models\JobDispute;
use App\Models\JobQuote;
use App\Models\JobRequest;
use App\Models\Review;
use App\Services\JobRequestService;
use App\Services\JobWorkflowService;
use App\Services\OnDemandMatchingService;
use App\Services\PaymentService;
use App\Services\ReviewService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class JobController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', JobRequest::class);
        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(JobStatus::class)],
        ]);

        return JobRequestResource::collection(
            JobRequest::query()
                ->with(['category', 'service.category', 'service.coverImage', 'professional.user', 'payments', 'review.client'])
                ->where('client_id', $request->user()->getKey())
                ->when(
                    $validated['status'] ?? null,
                    fn ($query, string $status) => $query->where('status', $status),
                )
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        );
    }

    public function immediate(StoreImmediateJobRequest $request, JobRequestService $jobs): JsonResponse
    {
        $job = $jobs->createImmediate($request->user(), $request->validated(), $request->file('photos', []));

        return response()->json([
            'data' => new JobRequestResource($job->load(['category', 'service', 'professional.user'])),
            'message' => 'Solicitud inmediata creada. Estamos buscando profesionales.',
        ], 201);
    }

    public function scheduled(StoreScheduledJobRequest $request, JobRequestService $jobs): JsonResponse
    {
        $job = $jobs->createScheduled($request->user(), $request->validated());

        return response()->json([
            'data' => new JobRequestResource($job->load(['category', 'service.category', 'service.professional.user', 'service.coverImage', 'professional.user'])),
            'message' => 'Solicitud programada correctamente.',
        ], 201);
    }

    public function show(Request $request, JobRequest $job): JobRequestResource
    {
        $this->authorize('view', $job);
        $job->load(['category', 'service.category', 'service.coverImage', 'professional.user', 'quotes', 'payment', 'payments', 'review.client']);

        return new JobRequestResource($job);
    }

    public function status(Request $request, JobRequest $job): JsonResponse
    {
        abort_unless($job->client_id === $request->user()->id || $job->professional?->user_id === $request->user()->id, 403);
        $job->load('professional.user');

        return response()->json(['data' => [
            'status' => $job->status?->value,
            'status_label' => (new JobRequestResource($job))->resolve($request)['status_label'],
            'updated_at' => $job->updated_at?->toIso8601String(),
            'search_round' => $job->search_round,
            'search_radius_km' => $job->search_radius_km !== null ? (string) $job->search_radius_km : null,
            'expires_at' => $job->search_expires_at?->toIso8601String(),
            'professional' => $job->professional ? new ProfessionalResource($job->professional) : null,
        ]]);
    }

    public function quotes(Request $request, JobRequest $job): AnonymousResourceCollection
    {
        $this->authorize('view', $job);

        return JobQuoteResource::collection($job->quotes()->latest()->paginate(15));
    }

    public function acceptQuote(Request $request, JobRequest $job, JobQuote $quote, JobWorkflowService $workflow): JsonResponse
    {
        abort_unless($quote->job_request_id === $job->id, 404);
        $this->authorize('accept', $quote);
        try {
            $quote = $workflow->acceptQuote($quote, $request->user());
        } catch (DomainException $exception) {
            $code = str_contains(mb_strtolower($exception->getMessage()), 'expir') ? 'QUOTE_EXPIRED' : 'QUOTE_UNAVAILABLE';

            return $this->domainError($exception, $code);
        }

        return response()->json(['data' => new JobQuoteResource($quote), 'message' => 'Cotización aceptada correctamente.']);
    }

    public function rejectQuote(
        RejectJobQuoteRequest $request,
        JobRequest $job,
        JobQuote $quote,
        JobWorkflowService $workflow,
    ): JsonResponse {
        abort_unless($quote->job_request_id === $job->id, 404);
        $this->authorize('reject', $quote);
        try {
            $quote = $workflow->rejectQuote($quote, $request->user(), $request->validated('reason'));
        } catch (DomainException $exception) {
            return $this->domainError($exception, 'QUOTE_UNAVAILABLE');
        }

        return response()->json(['data' => new JobQuoteResource($quote), 'message' => 'Cotización rechazada correctamente.']);
    }

    public function checkout(Request $request, JobRequest $job, PaymentService $payments): JsonResponse
    {
        $this->authorize('pay', $job);
        try {
            $payment = $payments->startCheckout($job, $request->user());
        } catch (MercadoPagoException $exception) {
            return response()->json(['message' => $exception->getMessage(), 'errors' => (object) [], 'code' => 'PAYMENT_PROVIDER_UNAVAILABLE'], 409);
        } catch (DomainException $exception) {
            return $this->domainError($exception, 'PAYMENT_REQUIRED');
        }

        return response()->json(['data' => [
            'checkout_url' => $payment->checkout_url,
            'payment' => new PaymentResource($payment),
        ], 'message' => 'Checkout preparado correctamente.']);
    }

    public function onTheWay(Request $request, JobRequest $job, JobWorkflowService $workflow): JsonResponse
    {
        $this->authorize('onTheWay', $job);

        return $this->workflow(fn () => $workflow->onTheWay($job), 'Profesional en camino.');
    }

    public function arrived(Request $request, JobRequest $job, JobWorkflowService $workflow): JsonResponse
    {
        $this->authorize('arrive', $job);

        return $this->workflow(fn () => $workflow->arrive($job), 'Llegada registrada.');
    }

    public function start(Request $request, JobRequest $job, JobWorkflowService $workflow): JsonResponse
    {
        if ($job->professional?->user_id === $request->user()->id
            && ! in_array($job->status?->value, ['paid', 'arrived'], true)) {
            return response()->json([
                'message' => 'El trabajo requiere un pago aprobado antes de iniciar.',
                'errors' => (object) [],
                'code' => 'PAYMENT_REQUIRED',
            ], 409);
        }
        $this->authorize('start', $job);

        return $this->workflow(fn () => $workflow->start($job), 'Trabajo iniciado.');
    }

    public function finish(Request $request, JobRequest $job, JobWorkflowService $workflow): JsonResponse
    {
        $this->authorize('finish', $job);

        return $this->workflow(fn () => $workflow->finish($job), 'Trabajo marcado como terminado.');
    }

    public function confirm(Request $request, JobRequest $job, JobWorkflowService $workflow): JsonResponse
    {
        abort_unless($request->user()->isClient() && $job->client_id === $request->user()->id, 403);
        $data = $request->validate(['completion_code' => ['required', 'digits:6']]);

        return $this->workflow(
            fn () => $workflow->confirmCompletion($job, (string) $data['completion_code']),
            'Trabajo confirmado como completado.',
        );
    }

    public function dispute(
        StoreJobDisputeRequest $request,
        JobRequest $job,
        JobWorkflowService $workflow,
    ): JsonResponse {
        $this->authorize('create', [JobDispute::class, $job]);

        try {
            $workflow->openDispute(
                $job,
                $request->user(),
                $request->validated('reason'),
                $request->validated('description'),
            );
        } catch (DomainException $exception) {
            return $this->domainError($exception, 'DISPUTE_UNAVAILABLE');
        }

        $job->refresh()->load([
            'category',
            'service.category',
            'service.coverImage',
            'professional.user',
            'payment',
            'payments',
        ]);

        return response()->json([
            'data' => new JobRequestResource($job),
            'message' => 'Tu reporte fue enviado y será revisado.',
        ]);
    }

    public function review(
        StoreReviewRequest $request,
        JobRequest $job,
        ReviewService $reviews,
    ): JsonResponse {
        $this->authorize('create', [Review::class, $job]);
        try {
            $review = $reviews->create($job, $request->user(), (int) $request->validated('rating'), $request->validated('comment'));
        } catch (DomainException $exception) {
            return $this->domainError($exception, 'REVIEW_UNAVAILABLE');
        }

        return response()->json(['data' => new ReviewResource($review), 'message' => 'Reseña publicada correctamente.'], 201);
    }

    private function workflow(callable $transition, string $message): JsonResponse
    {
        try {
            $job = $transition();
        } catch (DomainException $exception) {
            $code = str_contains(mb_strtolower($exception->getMessage()), 'pago') ? 'PAYMENT_REQUIRED' : 'INVALID_JOB_TRANSITION';

            return $this->domainError($exception, $code);
        }

        return response()->json(['data' => new JobRequestResource($job), 'message' => $message]);
    }

    private function domainError(DomainException $exception, string $code): JsonResponse
    {
        if ($exception instanceof IdentityVerificationRequiredException) {
            $code = 'IDENTITY_VERIFICATION_REQUIRED';
        }

        return response()->json(['message' => $exception->getMessage(), 'errors' => (object) [], 'code' => $code], $exception instanceof IdentityVerificationRequiredException ? 403 : 409);
    }
}
