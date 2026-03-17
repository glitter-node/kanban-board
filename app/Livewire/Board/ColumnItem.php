<?php

namespace App\Livewire\Board;

use Livewire\Component;

class ColumnItem extends Component
{
    public array $column = [];

    public bool $canEdit = false;

    public function render()
    {
        return view('livewire.board.column-item');
    }
}
