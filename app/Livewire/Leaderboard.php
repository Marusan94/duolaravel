<?php

namespace App\Livewire;

use App\Services\LeagueService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Liga')]
class Leaderboard extends Component
{
    public function render(LeagueService $leagues)
    {
        $user = auth()->user();

        return view('livewire.leaderboard', [
            'members' => $leagues->leaderboard($user),
            'weeklyXp' => $user->weeklyXp(),
            'totalXp' => $user->totalXp(),
            'streak' => $user->streak,
        ]);
    }
}
