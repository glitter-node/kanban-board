<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InviteBoardMemberRequest;
use App\Http\Requests\UpdateBoardMemberRoleRequest;
use App\Models\Board;
use App\Models\BoardMember;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\BoardMemberService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BoardMemberController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly BoardMemberService $boardMemberService,
        private readonly AnalyticsService $analyticsService,
    ) {}

    public function index(Request $request, Board $board): JsonResponse
    {
        $this->authorize('view', $board);

        $members = $this->boardMemberService->listMembers($board);

        return response()->json([
            'data' => $members,
        ]);
    }

    public function store(InviteBoardMemberRequest $request, Board $board): JsonResponse
    {
        $validated = $request->validated();

        $member = $this->boardMemberService->inviteMember(
            board: $board,
            user: User::query()->findOrFail($validated['user_id']),
            role: $validated['role'] ?? 'viewer',
            actor: $request->user(),
        );

        $this->analyticsService->record('member_added', $request->user(), [
            'board_id' => $board->getKey(),
            'member_user_id' => $member->user_id,
            'role' => $member->role,
        ]);

        return response()->json([
            'message' => 'Board member invited successfully.',
            'data' => $member,
        ], 201);
    }

    public function update(UpdateBoardMemberRoleRequest $request, Board $board, BoardMember $member): JsonResponse
    {
        $this->assertMemberBelongsToBoard($board, $member);

        $member = $this->boardMemberService->changeMemberRole(
            board: $board,
            member: $member,
            role: $request->validated('role'),
            actor: $request->user(),
        );

        return response()->json([
            'message' => 'Board member role updated successfully.',
            'data' => $member,
        ]);
    }

    public function destroy(Request $request, Board $board, BoardMember $member): JsonResponse
    {
        $this->authorize('delete', $board);
        $this->assertMemberBelongsToBoard($board, $member);

        $member = $this->boardMemberService->removeMember($board, $member, $request->user());

        return response()->json([
            'message' => 'Board member removed successfully.',
            'data' => $member,
        ]);
    }

    private function assertMemberBelongsToBoard(Board $board, BoardMember $member): void
    {
        abort_unless((int) $member->board_id === (int) $board->getKey(), 404);
    }
}
