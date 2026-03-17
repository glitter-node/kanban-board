<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Notifications\DueDateApproaching;
use Illuminate\Console\Command;

class SendDueDateNotifications extends Command
{
    protected $signature = 'kanban:notify-due-dates';

    protected $description = 'Send notifications for cards with upcoming due dates';

    public function handle(): int
    {
        $tomorrow = now()->addDay()->startOfDay();

        $cards = Card::with(['assignedUser', 'column.board'])
            ->whereNotNull('assigned_user_id')
            ->whereDate('due_date', $tomorrow)
            ->get();

        $count = 0;
        foreach ($cards as $card) {
            $board = $card->column->board;
            $card->assignedUser->notify(new DueDateApproaching($card, $board->id, $board->title));
            $count++;
        }

        $this->info("Sent {$count} upcoming due date notifications.");

        return Command::SUCCESS;
    }
}
