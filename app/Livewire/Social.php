<?php

namespace App\Livewire;

use App\Models\ActivityFeed;
use App\Models\User;
use App\Models\UserAchievement;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Amigos')]
class Social extends Component
{
    public string $email = '';

    public string $message = '';

    public string $messageType = 'ok';

    public function addFriend(): void
    {
        $me = auth()->user();
        $friend = User::where('email', trim($this->email))->first();

        if (! $friend || $friend->id === $me->id) {
            $this->message = 'Usuario no encontrado.';
            $this->messageType = 'bad';

            return;
        }

        $me->friendships()->firstOrCreate(['friend_id' => $friend->id], ['status' => 'accepted']);
        $friend->friendships()->firstOrCreate(['friend_id' => $me->id], ['status' => 'accepted']);

        ActivityFeed::create([
            'user_id' => $me->id,
            'type' => 'friend_added',
            'description' => 'Ahora sigue a '.$friend->name,
        ]);

        $this->email = '';
        $this->message = 'Amigo agregado.';
        $this->messageType = 'ok';
    }

    public function render()
    {
        $me = auth()->user();

        return view('livewire.social', [
            'friends' => $me->friends()->get(),
            'feed' => ActivityFeed::with('user')->latest()->limit(20)->get(),
            'achievements' => UserAchievement::with('achievement')
                ->where('user_id', $me->id)
                ->latest('achieved_at')
                ->get(),
        ]);
    }
}
