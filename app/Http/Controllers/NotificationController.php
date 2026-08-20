<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(15),
        ]);
    }

    public function show(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless(
            $notification->notifiable_type === $request->user()->getMorphClass()
                && (string) $notification->notifiable_id === (string) $request->user()->getKey(),
            403,
        );

        $notification->markAsRead();
        $url = data_get($notification->data, 'url');
        abort_unless(is_string($url) && str_starts_with($url, url('/')), 404);

        return redirect()->to($url);
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'Notificaciones marcadas como leídas.');
    }
}
