<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return NotificationResource::collection($request->user()->notifications()->latest()->paginate(20));
    }

    public function read(Request $request, DatabaseNotification $notification): JsonResponse
    {
        abort_unless(
            $notification->notifiable_type === $request->user()->getMorphClass()
                && (string) $notification->notifiable_id === (string) $request->user()->getKey(),
            403,
        );
        $notification->markAsRead();

        return response()->json(['data' => new NotificationResource($notification->fresh()), 'message' => 'Notificación marcada como leída.']);
    }

    public function readAll(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['data' => null, 'message' => 'Notificaciones marcadas como leídas.']);
    }
}
