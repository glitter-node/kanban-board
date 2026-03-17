<?php

namespace App\Http\Requests;

use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Policies\CardPolicy;
use Illuminate\Foundation\Http\FormRequest;

class MoveCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Board|null $board */
        $board = $this->route('board');
        /** @var Card|null $card */
        $card = $this->route('card');
        /** @var Column|null $destinationColumn */
        $destinationColumn = $this->route('column');

        $policyAllows = $user !== null && $card !== null && app(CardPolicy::class)->move($user, $card);

        if (! $user || ! $board || ! $card || ! $policyAllows) {
            return false;
        }

        if (! $card->belongsToBoard($board)) {
            return false;
        }

        if ($destinationColumn instanceof Column && ! $destinationColumn->belongsToBoard($board)) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'column_id' => ['required', 'integer', 'exists:columns,id'],
            'order_key' => ['required', 'string', 'max:64'],
        ];
    }
}
