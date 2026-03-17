<?php

namespace App\Services;

use App\Models\Board;
use App\Models\BoardMember;
use App\Models\User;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BoardMemberService
{
    public function __construct(
        private readonly ActivityService $activityService,
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Add or reactivate a board member for a collaborative board.
     */
    public function inviteMember(Board $board, User $user, string $role = 'viewer', ?User $actor = null): BoardMember
    {
        return DB::transaction(function () use ($board, $user, $role, $actor): BoardMember {
            if ($board->type !== 'collaborative') {
                throw new DomainException('Members can only be invited to collaborative boards.');
            }

            if ($board->isOwnedBy($user)) {
                throw new DomainException('The board owner is already a member.');
            }

            $member = BoardMember::query()->firstOrNew([
                'board_id' => $board->getKey(),
                'user_id' => $user->getKey(),
            ]);

            $member->fill([
                'role' => $role,
                'status' => 'active',
                'joined_at' => $member->joined_at ?? now(),
            ]);
            $member->save();

            $this->activityService->logActivity(
                board: $board,
                actor: $actor,
                action: 'board_member.invited',
                entityType: 'board_member',
                entityId: $member->getKey(),
                metadata: $this->activityService->buildActivityPayload(
                    entity: $member,
                    extra: [
                        'invited_user_id' => $user->getKey(),
                        'role' => $role,
                    ],
                ),
            );

            $this->notificationService->notifyBoardInvite($member, $actor);

            return $member->load('user');
        });
    }

    /**
     * Change the effective role of an existing member.
     */
    public function changeMemberRole(Board $board, BoardMember $member, string $role, ?User $actor = null): BoardMember
    {
        return DB::transaction(function () use ($board, $member, $role, $actor): BoardMember {
            $this->assertMemberBelongsToBoard($board, $member);

            if ($member->isOwner()) {
                throw new DomainException('The owner role cannot be reassigned through this method.');
            }

            $previousRole = $member->role;
            $member->forceFill(['role' => $role])->save();

            $this->activityService->logActivity(
                board: $board,
                actor: $actor,
                action: 'board_member.role_changed',
                entityType: 'board_member',
                entityId: $member->getKey(),
                metadata: $this->activityService->buildActivityPayload(
                    entity: $member,
                    changes: [
                        'before' => ['role' => $previousRole],
                        'after' => ['role' => $role],
                    ],
                ),
            );

            return $member->refresh();
        });
    }

    /**
     * Remove a member by marking the membership as removed.
     */
    public function removeMember(Board $board, BoardMember $member, ?User $actor = null): BoardMember
    {
        return DB::transaction(function () use ($board, $member, $actor): BoardMember {
            $this->assertMemberBelongsToBoard($board, $member);

            if ($member->isOwner()) {
                throw new DomainException('The owner cannot be removed from the board.');
            }

            $member->forceFill([
                'status' => 'removed',
                'joined_at' => $member->joined_at,
            ])->save();

            $this->activityService->logActivity(
                board: $board,
                actor: $actor,
                action: 'board_member.removed',
                entityType: 'board_member',
                entityId: $member->getKey(),
                metadata: $this->activityService->buildActivityPayload(
                    entity: $member,
                    extra: ['removed_user_id' => $member->user_id],
                ),
            );

            return $member->refresh();
        });
    }

    /**
     * Read-only member listing for a board.
     */
    public function listMembers(Board $board): Collection
    {
        return $board->memberships()
            ->with('user')
            ->active()
            ->orderByRaw("FIELD(role, 'owner', 'editor', 'viewer')")
            ->orderBy('joined_at')
            ->get();
    }

    private function assertMemberBelongsToBoard(Board $board, BoardMember $member): void
    {
        if ((int) $member->board_id !== (int) $board->getKey()) {
            throw new DomainException('The member does not belong to the given board.');
        }
    }

}
