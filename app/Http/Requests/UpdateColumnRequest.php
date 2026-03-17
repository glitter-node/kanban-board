<?php

namespace App\Http\Requests;

use App\Models\Column;
use App\Policies\ColumnPolicy;
use Illuminate\Foundation\Http\FormRequest;

class UpdateColumnRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Column|null $column */
        $column = $this->route('column');

        return $user !== null && $column !== null && app(ColumnPolicy::class)->update($user, $column);
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:120'],
            'type' => ['nullable', 'in:todo,doing,done,custom'],
            'wip_limit' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
