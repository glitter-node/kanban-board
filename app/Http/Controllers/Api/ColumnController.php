<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateColumnRequest;
use App\Http\Requests\ReorderColumnsRequest;
use App\Http\Requests\UpdateColumnRequest;
use App\Models\Board;
use App\Models\Column;
use App\Services\ColumnService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ColumnService $columnService,
    ) {}

    public function index(Request $request, Board $board): JsonResponse
    {
        $this->authorize('view', $board);

        $columns = $this->columnService->listColumns($board);

        return response()->json([
            'data' => $columns,
        ]);
    }

    public function store(CreateColumnRequest $request, Board $board): JsonResponse
    {
        $column = $this->columnService->createColumn($board, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Column created successfully.',
            'data' => $column,
        ], 201);
    }

    public function update(UpdateColumnRequest $request, Board $board, Column $column): JsonResponse
    {
        $this->assertColumnBelongsToBoard($board, $column);

        $column = $this->columnService->updateColumn($board, $column, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Column updated successfully.',
            'data' => $column,
        ]);
    }

    public function archive(Request $request, Board $board, Column $column): JsonResponse
    {
        $this->authorize('update', $board);
        $this->assertColumnBelongsToBoard($board, $column);

        $column = $this->columnService->archiveColumn($board, $column, $request->user());

        return response()->json([
            'message' => 'Column archived successfully.',
            'data' => $column,
        ]);
    }

    public function reorder(ReorderColumnsRequest $request, Board $board): JsonResponse
    {
        $columns = $this->columnService->reorderColumns(
            board: $board,
            orderedColumnIds: $request->validated('column_ids'),
            actor: $request->user(),
        );

        return response()->json([
            'message' => 'Columns reordered successfully.',
            'data' => $columns,
        ]);
    }

    private function assertColumnBelongsToBoard(Board $board, Column $column): void
    {
        abort_unless($column->belongsToBoard($board), 404);
    }
}
