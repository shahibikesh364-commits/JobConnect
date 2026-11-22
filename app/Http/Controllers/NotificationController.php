<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;
use App\Models\Conversation;
use App\Models\JobApplication;

class NotificationController extends Controller
{
    public function recent(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->limit(5)->get();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => data_get($notification->data, 'title', 'Notification'),
                'message' => data_get($notification->data, 'message', ''),
                'read' => $notification->read_at !== null,
                'created_at' => $notification->created_at->toIso8601String(),
                'created_at_human' => $notification->created_at->diffForHumans(),
                'url' => route('notifications.open', $notification->id),
            ])->values(),
        ]);
    }

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

        $conversationId = $notification->data['conversation_id'] ?? null;

        if ($conversationId) {
            $user = $request->user();
            $conversation = Conversation::query()
                ->whereKey($conversationId)
                ->where(function ($query) use ($user) {
                    $query->where('job_seeker_id', $user->id)
                        ->orWhere('employer_id', $user->employerWorkspaceId());
                })
                ->first();

            if ($conversation) {
                return redirect()->route('messages.show', $conversation);
            }
        }

        $applicationId = $notification->data['application_id'] ?? null;
        $jobId = $notification->data['job_id'] ?? null;
        if ($request->user()->isEmployer()) {
            $employerApplication = JobApplication::with('job')
                ->whereKey($applicationId)
                ->whereHas('job', fn ($query) => $query->where('employer_id', $request->user()->employerWorkspaceId()))
                ->first();
            if ($employerApplication?->job) {
                return redirect()->route('employer.jobs.applicants', $employerApplication->job);
            }
        }
        $application = $request->user()->applications()
            ->with('job')
            ->whereKey($applicationId)
            ->first();

        if ($application && (string) $application->job_listing_id === (string) $jobId && $application->job) {
            return redirect()->route('jobs.show', $application->job);
        }

        return match ($request->user()->role) {
            'employer' => redirect()->route('employer.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect()->route('applications.mine'),
        };
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $this->ownedNotification($request, $notification)->markAsRead();

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        $updated = $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'All notifications marked as read.',
                'updated_count' => $updated,
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]);
        }

        return back();
    }

    private function ownedNotification(Request $request, string $id): DatabaseNotification
    {
        return $request->user()->notifications()->whereKey($id)->firstOrFail();
    }
}
