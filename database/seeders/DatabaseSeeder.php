<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Board;
use App\Models\BoardMember;
use App\Models\Card;
use App\Models\CardComment;
use App\Models\Column;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $users = User::factory(4)->create([
            'password' => bcrypt('password'),
        ]);

        $allUsers = $users->push($admin);

        $boardConfigs = [
            ['title' => 'Project Management', 'description' => 'Main project kanban board'],
            ['title' => 'Sprint #12', 'description' => 'Current sprint task tracking'],
            ['title' => 'Bug Tracker', 'description' => 'Bug fixes and issue tracking'],
        ];

        foreach ($boardConfigs as $index => $config) {
            $owner = $allUsers[$index % $allUsers->count()];
            $board = Board::factory()->create([
                'user_id' => $owner->id,
                'title' => $config['title'],
                'description' => $config['description'],
            ]);

            BoardMember::create([
                'board_id' => $board->id,
                'user_id' => $owner->id,
                'role' => 'owner',
            ]);

            $otherUsers = $allUsers->where('id', '!=', $owner->id)->random(min(2, $allUsers->count() - 1));
            foreach ($otherUsers as $u) {
                BoardMember::create([
                    'board_id' => $board->id,
                    'user_id' => $u->id,
                    'role' => fake()->randomElement(['editor', 'viewer']),
                ]);
            }

            $columnTitles = ['To Do', 'In Progress', 'In Review', 'Done'];
            $columns = collect();

            foreach ($columnTitles as $pos => $title) {
                $columns->push(Column::factory()->create([
                    'board_id' => $board->id,
                    'title' => $title,
                    'position' => $pos,
                ]));
            }

            foreach ($columns as $column) {
                $cardCount = fake()->numberBetween(2, 5);
                for ($i = 0; $i < $cardCount; $i++) {
                    $card = Card::factory()->create([
                        'column_id' => $column->id,
                        'assigned_user_id' => $allUsers->random()->id,
                        'position' => $i,
                    ]);

                    $commentCount = fake()->numberBetween(0, 3);
                    for ($j = 0; $j < $commentCount; $j++) {
                        CardComment::create([
                            'card_id' => $card->id,
                            'user_id' => $allUsers->random()->id,
                            'content' => fake()->sentence(fake()->numberBetween(3, 15)),
                        ]);
                    }

                    Activity::factory()->create([
                        'board_id' => $board->id,
                        'user_id' => $owner->id,
                        'action' => 'created',
                        'target_type' => 'card',
                        'target_id' => $card->id,
                        'metadata' => ['card_title' => $card->title],
                    ]);
                }
            }

            Activity::factory(3)->create([
                'board_id' => $board->id,
                'user_id' => $allUsers->random()->id,
            ]);
        }
    }
}
