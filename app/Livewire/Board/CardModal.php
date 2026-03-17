<?php

namespace App\Livewire\Board;

use Livewire\Component;

class CardModal extends Component
{
    public int $boardId;

    public array $users = [];

    public bool $canEdit = false;

    public function render()
    {
        return view('livewire.board.card-modal');
    }
}
