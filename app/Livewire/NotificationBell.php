<?php

namespace App\Livewire;

use App\Models\UserNotification;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    #[On('notification-created')]
    public function refresh(): void
    {
        // Re-render to update the unread badge.
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function markAllRead(): void
    {
        UserNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markRead(int $id): void
    {
        UserNotification::where('user_id', auth()->id())
            ->whereKey($id)
            ->update(['read_at' => now()]);
    }

    public function render()
    {
        $userId = auth()->id();

        return view('livewire.notification-bell', [
            'unread' => UserNotification::where('user_id', $userId)->whereNull('read_at')->count(),
            'items' => UserNotification::where('user_id', $userId)->latest()->limit(8)->get(),
        ]);
    }
}
