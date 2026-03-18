<?php

namespace App\Livewire\Board;

use App\Livewire\Concerns\InteractsWithExperiments;
use App\Models\Board;
use App\Services\ActivityService;
use App\Services\BoardMemberService;
use App\Services\BoardService;
use App\Services\CardService;
use App\Services\ColumnService;
use App\Services\FlowMetricsService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class BoardView extends Component
{
    use InteractsWithExperiments;

    public Board $board;

    public array $boardPayload = [];

    public array $columns = [];

    public array $members = [];

    public array $users = [];

    public array $activities = [];

    public array $notifications = [];

    public int $currentUserId;

    public string $currentUserName;

    public ?string $currentRole = null;

    public bool $canEdit = false;

    public function mount(
        Board $board,
        BoardService $boardService,
        ColumnService $columnService,
        CardService $cardService,
        BoardMemberService $boardMemberService,
        ActivityService $activityService,
        NotificationService $notificationService,
        FlowMetricsService $flowMetricsService,
    ): void {
        Gate::authorize('view', $board);

        $board = $boardService->getBoard($board);
        $user = auth()->user();

        $this->board = $board;
        $this->currentUserId = (int) $user->getKey();
        $this->currentUserName = $user->name;
        $this->currentRole = $board->roleFor($user);
        $this->canEdit = in_array($this->currentRole, ['owner', 'editor'], true);

        $cards = collect($cardService->listCards($board, [], 500)->items());
        $cardsByColumn = $cards->groupBy('column_id');
        $flowMetrics = $flowMetricsService->boardMetrics($board);

        $this->boardPayload = [
            'id' => $board->getKey(),
            'title' => $board->title,
            'description' => $board->description,
            'type' => $board->type,
            'experiments' => $this->experimentAssignments(),
            'metrics' => $flowMetrics,
        ];

        $this->columns = $columnService->listColumns($board)
            ->map(function ($column) use ($cardsByColumn) {
                $columnCards = collect($cardsByColumn->get($column->getKey(), []));

                return [
                    'id' => $column->getKey(),
                    'board_id' => $column->board_id,
                    'title' => $column->title,
                    'type' => $column->type,
                    'order_key' => $column->order_key,
                    'wip_limit' => $column->wip_limit,
                    'current_wip' => $columnCards->where('status', '!=', 'archived')->count(),
                    'average_time_hours' => data_get($flowMetrics, 'average_time_per_column.'.$column->getKey().'.average_hours', 0),
                    'is_archived' => (bool) $column->is_archived,
                    'updated_at' => optional($column->updated_at)->toISOString(),
                    'cards' => $columnCards
                        ->map(fn ($card) => [
                            'id' => $card->id,
                            'board_id' => $card->board_id,
                            'column_id' => $card->column_id,
                            'creator_user_id' => $card->creator_user_id,
                            'assigned_user_id' => $card->assigned_user_id,
                            'title' => $card->title,
                            'description' => $card->description,
                            'priority' => $card->priority,
                            'status' => $card->status,
                            'blocked' => (bool) $card->blocked,
                            'blocked_reason' => $card->blocked_reason,
                            'order_key' => $card->order_key,
                            'created_at' => optional($card->created_at)->toISOString(),
                            'due_at' => optional($card->due_at)->toISOString(),
                            'completed_at' => optional($card->completed_at)->toISOString(),
                            'moved_to_done_at' => optional($card->moved_to_done_at)->toISOString(),
                            'updated_at' => optional($card->updated_at)->toISOString(),
                        ])
                        ->sortBy('order_key')
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        $this->members = $boardMemberService->listMembers($board)
            ->map(fn ($member) => [
                'id' => $member->getKey(),
                'user_id' => $member->user_id,
                'name' => $member->user?->name,
                'email' => $member->user?->email,
                'role' => $member->role,
                'status' => $member->status,
            ])
            ->values()
            ->all();

        $this->users = collect($this->members)
            ->map(fn (array $member) => [
                'id' => $member['user_id'],
                'name' => $member['name'],
                'role' => $member['role'],
            ])
            ->values()
            ->all();

        $this->activities = collect($activityService->listBoardActivities($board, [], 50)->items())
            ->map(fn ($activity) => [
                'id' => $activity->id,
                'actor_user_id' => $activity->actor_user_id,
                'actor_name' => $activity->actor?->name,
                'entity_type' => $activity->entity_type,
                'entity_id' => $activity->entity_id,
                'action' => $activity->action,
                'metadata' => $activity->metadata_json,
                'created_at' => optional($activity->created_at)->toISOString(),
            ])
            ->values()
            ->all();

        $this->notifications = collect($notificationService->listNotifications($user, 25)->items())
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'board_id' => $notification->board_id,
                'card_id' => $notification->card_id,
                'payload' => $notification->payload_json,
                'read_at' => optional($notification->read_at)->toISOString(),
                'created_at' => optional($notification->created_at)->toISOString(),
            ])
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.board.board-view');
    }
}
