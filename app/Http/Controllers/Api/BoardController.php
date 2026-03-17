<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBoardRequest;
use App\Http\Requests\UpdateBoardRequest;
use App\Models\Board;
use App\Services\BoardService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly BoardService $boardService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Board::class);

        $boards = $this->boardService->listBoards($request->user());

        return response()->json([
            'data' => $boards,
        ]);
    }

    public function store(CreateBoardRequest $request): JsonResponse
    {
        $board = $this->boardService->createBoard($request->user(), $request->validated());

        return response()->json([
            'message' => 'Board created successfully.',
            'data' => $board,
        ], 201);
    }

    public function show(Request $request, Board $board): JsonResponse
    {
        $this->authorize('view', $board);

        $board = $this->boardService->getBoard($board);

        return response()->json([
            'data' => $board,
        ]);
    }

    public function update(UpdateBoardRequest $request, Board $board): JsonResponse
    {
        $board = $this->boardService->updateBoard($board, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Board updated successfully.',
            'data' => $board,
        ]);
    }

    public function archive(Request $request, Board $board): JsonResponse
    {
        $this->authorize('update', $board);

        $board = $this->boardService->archiveBoard($board, $request->user());

        return response()->json([
            'message' => 'Board archived successfully.',
            'data' => $board,
        ]);
    }

    public function destroy(Request $request, Board $board): JsonResponse
    {
        $this->authorize('delete', $board);

        $this->boardService->deleteBoard($board, $request->user());

        return response()->json([
            'message' => 'Board deleted successfully.',
            'data' => null,
        ]);
    }
}
