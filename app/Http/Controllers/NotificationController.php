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
        $filter = $request->validate([
            'filter' => ['nullable', 'in:all,unread'],
        ])['filter'] ?? 'all';

        $notificationQuery = $request->user()->notifications()->latest();

        if ($filter === 'unread') {
            $notificationQuery->whereNull('read_at');
        }

        $notifications = $notificationQuery->paginate(15)->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'totalCount' => $request->user()->notifications()->count(),
            'unreadCount' => $request->user()->unreadNotifications()->count(),
            'filter' => $filter,
        ]);
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $notification = $this->ownedNotification($request, $notification);

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        $applicationId = $notification->data['application_id'] ?? null;
        $jobId = $notification->data['job_id'] ?? null;
        $application = $request->user()->applications()
            ->with('job')
            ->whereKey($applicationId)
            ->first();

        if ($application && (string) $application->job_listing_id === (string) $jobId && $application->job) {
            return redirect()->route('jobs.show', $application->job);
        }

        return match ($request->user()->role) {
            'employer' => redirect()->route('employer.jobs.index'),
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect()->route('applications.mine'),
        };
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $this->ownedNotification($request, $notification)->markAsRead();

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }

    private function ownedNotification(Request $request, string $id): DatabaseNotification
    {
        return $request->user()->notifications()->whereKey($id)->firstOrFail();
    }
}
