<?php

namespace App\Http\Requests;

use App\Models\Card;
use App\Policies\CardPolicy;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Card|null $card */
        $card = $this->route('card');

        return $user !== null && $card !== null && app(CardPolicy::class)->update($user, $card);
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'priority' => ['sometimes', 'integer', 'min:0', 'max:255'],
            'status' => ['sometimes', 'in:open,done,archived'],
            'blocked' => ['sometimes', 'boolean'],
            'blocked_reason' => ['nullable', 'string', 'required_if:blocked,true'],
            'due_at' => ['nullable', 'date'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
        ];
    }
}
