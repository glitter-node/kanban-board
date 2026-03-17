<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCommentRequest;
use App\Http\Requests\ListCommentsRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Board;
use App\Models\Card;
use App\Models\CardComment;
use App\Services\CommentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CommentService $commentService,
    ) {}

    public function index(ListCommentsRequest $request, Board $board, Card $card): JsonResponse
    {
        $this->assertCardBelongsToBoard($board, $card);
        $comments = $this->commentService->listComments($board, $card, $request->validated('per_page') ?? 50);

        return response()->json([
            'data' => $comments,
        ]);
    }

    public function store(CreateCommentRequest $request, Board $board, Card $card): JsonResponse
    {
        $this->assertCardBelongsToBoard($board, $card);
        $comment = $this->commentService->createComment($board, $card, $request->user(), $request->validated());

        return response()->json([
            'message' => 'Comment created successfully.',
            'data' => $comment,
        ], 201);
    }

    public function update(UpdateCommentRequest $request, Board $board, Card $card, CardComment $comment): JsonResponse
    {
        $this->assertCardBelongsToBoard($board, $card);
        $this->assertCommentBelongsToCard($card, $comment);
        $comment = $this->commentService->updateComment($board, $card, $comment, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Comment updated successfully.',
            'data' => $comment,
        ]);
    }

    public function destroy(Request $request, Board $board, Card $card, CardComment $comment): JsonResponse
    {
        $this->authorize('update', $board);
        $this->assertCardBelongsToBoard($board, $card);
        $this->assertCommentBelongsToCard($card, $comment);

        $this->commentService->deleteComment($board, $card, $comment, $request->user());

        return response()->json([
            'message' => 'Comment deleted successfully.',
            'data' => null,
        ]);
    }

    private function assertCardBelongsToBoard(Board $board, Card $card): void
    {
        abort_unless($card->belongsToBoard($board), 404);
    }

    private function assertCommentBelongsToCard(Card $card, CardComment $comment): void
    {
        abort_unless((int) $comment->card_id === (int) $card->getKey(), 404);
    }
}
