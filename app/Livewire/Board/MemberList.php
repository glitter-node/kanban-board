<?php

namespace App\Livewire\Board;

use Livewire\Component;

class MemberList extends Component
{
    public bool $canEdit = false;

    public function render()
    {
        return view('livewire.board.member-list');
    }
}
