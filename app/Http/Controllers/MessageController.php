<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageReceived;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'filter' => ['nullable', 'in:all,unread,applications,general,archived'],
            'sort' => ['nullable', 'in:recent,oldest'],
        ]);
        $search = trim($filters['search'] ?? '');
        $filter = $filters['filter'] ?? 'all';
        $sort = $filters['sort'] ?? 'recent';
        $archiveColumn = $this->archiveColumn($user);
        $employerId = $user->employerWorkspaceId();

        $participantScope = fn(Builder $query) => $query->where(function ($query) use ($user, $employerId) {
            $query->where('job_seeker_id', $user->id)
                ->orWhere('employer_id', $employerId);
        });
        $unreadMessages = fn(Builder $query) => $query->whereNull('read_at')
            ->where('sender_id', '!=', $user->id);

        $conversationQuery = Conversation::query()
            ->where($participantScope)
            ->addSelect([
                'application_status' => JobApplication::query()
                    ->select('status')
                    ->whereColumn('job_listing_id', 'conversations.job_listing_id')
                    ->whereColumn('user_id', 'conversations.job_seeker_id')
                    ->limit(1),
            ])
            ->with([
                'jobSeeker.jobSeekerProfile',
                'employer.jobSeekerProfile',
                'employer.employerSetting',
                'job',
                'latestMessage',
            ])
            ->withCount(['messages as unread_count' => $unreadMessages]);

        if ($filter === 'archived') {
            $conversationQuery->whereNotNull($archiveColumn);
        } else {
            $conversationQuery->whereNull($archiveColumn);
        }

        if ($filter === 'unread') {
            $conversationQuery->whereHas('messages', $unreadMessages);
        } elseif ($filter === 'applications') {
            $conversationQuery->whereNotNull('job_listing_id');
        } elseif ($filter === 'general') {
            $conversationQuery->whereNull('job_listing_id');
        }

        if ($search !== '') {
            $otherParticipantRelation = $user->isJobSeeker() ? 'employer' : 'jobSeeker';
            $conversationQuery->where(function ($query) use ($search, $otherParticipantRelation) {
                $query->whereHas($otherParticipantRelation, fn($person) => $person->where('name', 'like', "%{$search}%"))
                    ->orWhereHas($otherParticipantRelation . '.employerSetting', fn($company) => $company->where('company_name', 'like', "%{$search}%"))
                    ->orWhereHas('job', fn($job) => $job->where('title', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%"))
                    ->orWhereHas('latestMessage', fn($message) => $message->where('body', 'like', "%{$search}%"));
            });
        }

        $conversations = $conversationQuery
            ->orderBy('updated_at', $sort === 'oldest' ? 'asc' : 'desc')
            ->paginate(12)
            ->withQueryString();
        $conversationCount = Conversation::query()->where($participantScope)->whereNull($archiveColumn)->count();
        $archivedConversationCount = Conversation::query()->where($participantScope)->whereNotNull($archiveColumn)->count();
        $unreadConversationCount = Conversation::query()
            ->where($participantScope)
            ->whereNull($archiveColumn)
            ->whereHas('messages', $unreadMessages)
            ->count();
        $applicationConversationCount = Conversation::query()
            ->where($participantScope)
            ->whereNull($archiveColumn)
            ->whereNotNull('job_listing_id')
            ->count();
        $generalConversationCount = Conversation::query()
            ->where($participantScope)
            ->whereNull($archiveColumn)
            ->whereNull('job_listing_id')
            ->count();

        return view('messages.index', compact(
            'conversations',
            'conversationCount',
            'archivedConversationCount',
            'unreadConversationCount',
            'applicationConversationCount',
            'generalConversationCount',
            'search',
            'filter',
            'sort',
        ));
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $user = $request->user();
        $this->ensureParticipant($conversation, $user);

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->load([
            'jobSeeker.jobSeekerProfile',
            'employer.jobSeekerProfile',
            'employer.employerSetting',
            'job',
        ]);
        $applicationStatus = JobApplication::query()
            ->where('job_listing_id', $conversation->job_listing_id)
            ->where('user_id', $conversation->job_seeker_id)
            ->value('status');

        $messages = $conversation->messages()
            ->withTrashed()
            ->with('sender')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();
        $messages->setCollection($messages->getCollection()->reverse()->values());

        $archiveColumn = $this->archiveColumn($user);
        $unreadMessages = fn(Builder $query) => $query->whereNull('read_at')
            ->where('sender_id', '!=', $user->id);
        $sidebarConversations = Conversation::query()
            ->where(fn(Builder $query) => $query->where('job_seeker_id', $user->id)
                ->orWhere('employer_id', $user->employerWorkspaceId()))
            ->where(fn(Builder $query) => $query->whereNull($archiveColumn)
                ->orWhere('conversations.id', $conversation->getKey()))
            ->addSelect([
                'application_status' => JobApplication::query()
                    ->select('status')
                    ->whereColumn('job_listing_id', 'conversations.job_listing_id')
                    ->whereColumn('user_id', 'conversations.job_seeker_id')
                    ->limit(1),
            ])
            ->with([
                'jobSeeker.jobSeekerProfile',
                'employer.jobSeekerProfile',
                'employer.employerSetting',
                'job',
                'latestMessage',
            ])
            ->withCount(['messages as unread_count' => $unreadMessages])
            ->latest('updated_at')
            ->limit(20)
            ->get();

        return view('messages.show', compact('conversation', 'messages', 'applicationStatus', 'sidebarConversations'));
    }
    /**
     * Display registered job seekers for a new direct conversation.
     */
    public function createDirect(Request $request): View
    {
        $employer = $request->user();
        abort_unless($employer->isEmployer(), 403);

        abort_unless(
            in_array($employer->employerWorkspaceRole(), ['admin', 'recruiter'], true),
            403
        );
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $search = trim($filters['search'] ?? '');
        $employerId = $employer->employerWorkspaceId();

        $candidates = User::query()
            ->where('role', 'job_seeker')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->with('jobSeekerProfile')
            ->withExists([
                'conversationsAsJobSeeker as has_direct_conversation' =>
                fn(Builder $query) => $query
                    ->where('employer_id', $employerId)
                    ->whereNull('job_listing_id'),
            ])
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('messages.create', compact('candidates', 'search'));
    }
    /**
     * Start or reop0en a directy employer-to-candidates conversation.
     */

    public function startDirect(Request $request): RedirectResponse
    {
        $employer = $request->user();

        abort_unless($employer->isEmployer(), 403);

        abort_unless(
            in_array($employer->employerWorkspaceRole(), ['admin', 'recruiter'], true),
            403
        );
        $validated = $request->validate([
            'job_seeker_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where(fn($query) => $query->where('role', 'job_seeker')),
            ],
        ]);

        $employerId = $employer->employerWorkspaceId();
        $conversation = DB::transaction(function () use (
            $employer,
            $employerId,
            $validated
        ) {
            // Serialize creation for this employer to prevent duplicate chats.
            User::query()
                ->whereKey($employerId)
                ->lockForUpdate()
                ->firstOrFail();

            $conversation = Conversation::query()
                ->where('employer_id', $employerId)
                ->where('job_seeker_id', $validated['job_seeker_id'])
                ->whereNull('job_listing_id')
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'employer_id' => $employerId,
                    'job_seeker_id' => $validated['job_seeker_id'],
                    'job_listing_id' => null,
                ]);
            }
            // Restore tyhe conversation if this employer previously archived it.
            if ($conversation->archivedAtFor($employer)) {
                $conversation->setArchivedFor($employer, false);
            }
            return $conversation;
        });

        return redirect()->route('messages.show', $conversation);
    }
    /**
     * Display registered employers for a new direct conversation.
     */
    public function findEmployers(Request $request): View
    {
        $jobSeeker = $request->user();
        abort_unless($jobSeeker->isJobSeeker(), 403);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
        ]);
        $search = trim($filters['search'] ?? '');

        $employers = User::query()
            ->where('role', 'employer')
            ->whereNull('employer_workspace_id')
            ->where(function ($query) {
                $query->where('is_active', true)
                    ->orWhereNull('is_active');
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereExists(function ($subquery) use ($search) {
                            $subquery->selectRaw('1')
                                ->from('job_listings')
                                ->whereColumn('job_listings.employer_id', 'users.id')
                                ->where('job_listings.company_name', 'like', "%{$search}%");
                        });
                });
            })
            ->withExists([
                'conversationsAsEmployer as has_direct_conversation' =>
                fn(Builder $query) => $query
                    ->where('job_seeker_id', $jobSeeker->id)
                    ->whereNull('job_listing_id'),
            ])

            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();
        return view('messages.find-employers', compact(
            'employers',
            'search'
        ));
    }
    /**
     * Start or reopen a direct conversatiom with an employer.
     */
    public function startDirectForJobSeeker(Request $request): RedirectResponse
    {
        $jobSeeker = $request->user();
        abort_unless($jobSeeker->isJobSeeker(), 403);
        $validated = $request->validate([
            'employer_id' => [
                'required',
                'integer',

                Rule::exists('users', 'id')
                    ->where(fn($query) => $query
                        ->where('role', 'employer')
                        ->whereNull('employer_workspace_id')
                        ->where(function ($query) {
                            $query->where('is_active', true)
                                ->orWhereNull('is_active');
                        })),

            ],
        ]);
        $employer = User::query()
            ->whereKey($validated['employer_id'])
            ->where('role', 'employer')
            ->whereNull('employer_workspace_id')
            ->where(function ($query) {
                $query->where('is_active', true)
                    ->orWhereNull('is_active');
            })
            ->firstOrFail();
        $conversation = DB::transaction(function () use (
            $jobSeeker,
            $employer
        ) {
            User::query()
                ->whereKey($employer->id)
                ->lockForUpdate()
                ->firstOrFail();
            $conversation = Conversation::query()
                ->where('job_seeker_id', $jobSeeker->id)
                ->where('employer_id', $employer->id)
                ->whereNull('job_listing_id')
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'job_seeker_id' => $jobSeeker->id,
                    'employer_id' => $employer->id,
                    'job_listing_id' => null,
                ]);
            }
            if ($conversation->archivedAtFor($jobSeeker)) {
                $conversation->setArchivedFor($jobSeeker, false);
            }
            return $conversation;
        });
        return redirect()->route('messages.show', $conversation);
    }

    public function start(Request $request, JobListing $job): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isJobSeeker(), 403);
        abort_unless($job->employer_id && (int) $job->employer_id !== $user->employerWorkspaceId(), 404);

        abort_unless(
            $job->applications()->where('user_id', $user->id)->exists(),
            403,
            'Apply for this role before contacting the employer.'
        );

        $conversation = Conversation::firstOrCreate([
            'job_seeker_id' => $user->id,
            'employer_id' => $job->employer_id,
            'job_listing_id' => $job->id,
        ]);

        if ($conversation->job_seeker_archived_at) {
            $conversation->setArchivedFor($user, false);
        }

        return redirect()->route('messages.show', $conversation);
    }

    public function startForApplicant(Request $request, JobListing $job, User $applicant): RedirectResponse
    {
        $employer = $request->user();
        abort_unless($employer->isEmployer() && (int) $job->employer_id === $employer->employerWorkspaceId(), 403);
        abort_unless(in_array($employer->employerWorkspaceRole(), ['admin', 'recruiter'], true), 403);
        abort_unless($applicant->isJobSeeker(), 404);
        abort_unless($job->applications()->where('user_id', $applicant->id)->exists(), 404);

        $conversation = Conversation::firstOrCreate([
            'job_seeker_id' => $applicant->id,
            'employer_id' => $employer->employerWorkspaceId(),
            'job_listing_id' => $job->id,
        ]);

        if ($conversation->employer_archived_at) {
            $conversation->setArchivedFor($employer, false);
        }

        return redirect()->route('messages.show', $conversation);
    }

    public function store(Request $request, Conversation $conversation): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $this->ensureParticipant($conversation, $user);
        if ($user->isEmployer()) abort_unless(in_array($user->employerWorkspaceRole(), ['admin', 'recruiter'], true), 403);
        abort_if(
            $conversation->archivedAtFor($user),
            403,
            'Restore this conversation before sending a message.'
        );

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:5000'],
        ]);
        $recipient = (int) $conversation->job_seeker_id === (int) $user->id
            ? $conversation->employer
            : $conversation->jobSeeker;

        abort_unless($recipient, 404, 'The other participant is no longer available.');

        $message = DB::transaction(function () use ($conversation, $user, $recipient, $validated) {
            $message = $conversation->messages()->create([
                'sender_id' => $user->id,
                'body' => trim($validated['body']),
            ]);

            $conversation->touch();
            $recipient->notify(new MessageReceived(
                $message,
                $user->name,
                $conversation->job?->title,
            ));

            return $message;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'sender_name' => $user->name,
                    'body' => $message->body,
                    'created_at' => $message->created_at->format('M j, g:i A'),
                ],
            ], 201);
        }

        return redirect()
            ->route('messages.show', $conversation)
            ->with('success', 'Message sent.')
            ->withFragment('message-' . $message->getKey());
    }

    public function update(Request $request, Conversation $conversation, Message $message): RedirectResponse
    {
        $user = $request->user();
        $this->ensureParticipant($conversation, $user);
        $this->ensureMessageOwner($conversation, $message, $user);
        abort_if($message->created_at->lt(now()->subMinutes(15)), 403, 'Messages can only be edited for 15 minutes after sending.');

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:5000'],
        ]);

        $message->forceFill([
            'body' => trim($validated['body']),
            'edited_at' => now(),
        ])->save();

        return redirect()->route('messages.show', $conversation)
            ->with('success', 'Message updated.')
            ->withFragment('message-' . $message->getKey());
    }

    public function destroy(Request $request, Conversation $conversation, Message $message): RedirectResponse
    {
        $user = $request->user();
        $this->ensureParticipant($conversation, $user);
        $this->ensureMessageOwner($conversation, $message, $user);

        $message->delete();

        return redirect()->route('messages.show', $conversation)
            ->with('success', 'Message deleted.');
    }

    public function archive(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();
        $this->ensureParticipant($conversation, $user);
        $validated = $request->validate(['archived' => ['required', 'boolean']]);

        $conversation->setArchivedFor($user, (bool) $validated['archived']);

        return redirect()->route('messages.index')
            ->with('success', $validated['archived'] ? 'Conversation archived.' : 'Conversation restored.');
    }

    private function ensureParticipant(Conversation $conversation, User $user): void
    {
        abort_unless(
            (int) $user->id === (int) $conversation->job_seeker_id
                || ($user->isEmployer() && $user->employerWorkspaceId() === (int) $conversation->employer_id),
            403
        );
    }

    private function ensureMessageOwner(Conversation $conversation, Message $message, User $user): void
    {
        abort_unless((int) $message->conversation_id === (int) $conversation->id, 404);
        abort_unless((int) $message->sender_id === (int) $user->id, 403);
    }

    private function archiveColumn(User $user): string
    {
        return $user->isJobSeeker()
            ? 'job_seeker_archived_at'
            : 'employer_archived_at';
    }
}
