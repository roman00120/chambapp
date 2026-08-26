<?php

namespace App\Jobs;

use App\Models\DiditWebhookEvent;
use App\Services\DiditIdentityVerificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDiditWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 120, 300];

    public function __construct(public readonly int $eventId) {}

    public function handle(DiditIdentityVerificationService $service): void
    {
        $event = DiditWebhookEvent::query()->find($this->eventId);
        if ($event) {
            $service->processWebhookEvent($event);
        }
    }
}
