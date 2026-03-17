<?php

namespace App\Livewire\Board;

use Livewire\Component;

class NotificationBell extends Component
{
    public int $userId;

    public array $initialNotifications = [];

    public function render()
    {
        return view('livewire.board.notification-bell');
    }
}
