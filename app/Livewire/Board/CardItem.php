<?php

namespace App\Livewire\Board;

use Livewire\Component;

class CardItem extends Component
{
    public array $card = [];

    public function render()
    {
        return view('livewire.board.card-item');
    }
}
