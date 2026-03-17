<?php

namespace App\Http\Requests;

use App\Models\Card;
use App\Policies\CardPolicy;
use Illuminate\Foundation\Http\FormRequest;

class ArchiveCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Card|null $card */
        $card = $this->route('card');

        return $user !== null && $card !== null && app(CardPolicy::class)->archive($user, $card);
    }

    public function rules(): array
    {
        return [];
    }
}
