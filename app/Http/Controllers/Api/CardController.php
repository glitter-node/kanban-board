<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArchiveCardRequest;
use App\Http\Requests\AssignCardRequest;
use App\Http\Requests\CreateCardRequest;
use App\Http\Requests\ListCardsRequest;
use App\Http\Requests\MoveCardRequest;
use App\Http\Requests\UpdateCardRequest;
use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\User;
use App\Services\CardMoveService;
use App\Services\CardService;
use DomainException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CardController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CardService $cardService,
        private readonly CardMoveService $cardMoveService,
    ) {}

    public function index(ListCardsRequest $request, Board $board): JsonResponse
    {
        $validated = $request->validated();
        $cards = $this->cardService->listCards($board, $validated, $validated['per_page'] ?? 50);

        return response()->json([
            'data' => $cards,
        ]);
    }

    public function store(CreateCardRequest $request, Board $board): JsonResponse
    {
        $column = Column::query()->findOrFail($request->validated('column_id'));
        $this->assertColumnBelongsToBoard($board, $column);
        try {
            $card = $this->cardService->createCard($board, $column, $request->user(), $request->validated());
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Card created successfully.',
            'data' => $card,
        ], 201);
    }

    public function show(Request $request, Board $board, Card $card): JsonResponse
    {
        $this->authorize('view', $board);
        $this->assertCardBelongsToBoard($board, $card);

        $card = $this->cardService->getCard($board, $card);

        return response()->json([
            'data' => $card,
        ]);
    }

    public function update(UpdateCardRequest $request, Board $board, Card $card): JsonResponse
    {
        $this->assertCardBelongsToBoard($board, $card);
        $card = $this->cardService->updateCard($board, $card, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Card updated successfully.',
            'data' => $card,
        ]);
    }

    public function archive(ArchiveCardRequest $request, Board $board, Card $card): JsonResponse
    {
        $this->assertCardBelongsToBoard($board, $card);

        $card = $this->cardService->archiveCard($board, $card, $request->user());

        return response()->json([
            'message' => 'Card archived successfully.',
            'data' => $card,
        ]);
    }

    public function assign(AssignCardRequest $request, Board $board, Card $card): JsonResponse
    {
        $this->assertCardBelongsToBoard($board, $card);
        $assigneeId = $request->validated('assigned_user_id');
        $assignee = $assigneeId !== null
            ? User::query()->findOrFail($assigneeId)
            : null;

        $card = $this->cardService->assignCard($board, $card, $assignee, $request->user());

        return response()->json([
            'message' => 'Card assignment updated successfully.',
            'data' => $card,
        ]);
    }

    public function move(MoveCardRequest $request, Board $board, Card $card): JsonResponse
    {
        $this->assertCardBelongsToBoard($board, $card);

        $destinationColumn = Column::query()->findOrFail($request->validated('column_id'));

        try {
            $movedCard = $this->cardMoveService->moveCardToOrderKey(
                board: $board,
                card: $card,
                destinationColumn: $destinationColumn,
                orderKey: $request->validated('order_key'),
                actor: $request->user(),
            );
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Card moved successfully.',
            'data' => $movedCard,
        ]);
    }

    private function assertColumnBelongsToBoard(Board $board, Column $column): void
    {
        abort_unless($column->belongsToBoard($board), 404);
    }

    private function assertCardBelongsToBoard(Board $board, Card $card): void
    {
        abort_unless($card->belongsToBoard($board), 404);
    }
}
