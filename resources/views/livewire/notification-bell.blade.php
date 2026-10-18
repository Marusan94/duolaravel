<div class="relative" wire:poll.120s>
    <button type="button" wire:click="toggle"
            class="relative inline-flex items-center rounded-full p-2 text-duo-silver transition hover:bg-duo-soft hover:text-duo-ink focus:outline-none">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if ($unread > 0)
            <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center rounded-full bg-duo-red px-1.5 text-xs font-bold text-white">
                {{ $unread > 9 ? '9+' : $unread }}
            </span>
        @endif
    </button>

    @if ($open)
        <div class="absolute right-0 z-50 mt-2 w-80 rounded-2xl border-2 border-duo-border bg-duo-card shadow-lg">
            <div class="flex items-center justify-between border-b-2 border-duo-border px-4 py-2">
                <span class="text-sm font-extrabold text-duo-ink">Notificaciones</span>
                @if ($unread > 0)
                    <button wire:click="markAllRead" class="text-xs font-extrabold text-duo-blue hover:underline">Marcar todas</button>
                @endif
            </div>
            <div class="max-h-80 divide-y-2 divide-duo-border overflow-y-auto">
                @forelse ($items as $item)
                    <button wire:click="markRead({{ $item->id }})"
                            class="block w-full px-4 py-3 text-left transition hover:bg-duo-soft {{ $item->read_at ? 'opacity-60' : '' }}">
                        <span class="block text-sm font-extrabold text-duo-ink">{{ $item->title }}</span>
                        <span class="block text-sm font-bold text-duo-gray">{{ $item->body }}</span>
                        <span class="block text-xs font-bold text-duo-silver">{{ $item->created_at->diffForHumans() }}</span>
                    </button>
                @empty
                    <div class="px-4 py-3 text-sm font-bold text-duo-gray">Sin notificaciones.</div>
                @endforelse
            </div>
        </div>
    @endif
</div>
