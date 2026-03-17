<?php

namespace App\Livewire\Board;

use Livewire\Component;

class ColumnList extends Component
{
    public int $boardId;

    public bool $canEdit = false;

    public function render()
    {
        return view('livewire.board.column-list');
    }
}
