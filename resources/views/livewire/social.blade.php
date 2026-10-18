@php
    $avatarPalette = [
        ['bg-duo-green-tint', 'text-duo-green-dark'],
        ['bg-duo-blue-tint', 'text-duo-blue-dark'],
        ['bg-duo-purple-tint', 'text-duo-purple-dark'],
        ['bg-duo-orange-tint', 'text-duo-orange-dark'],
        ['bg-duo-red-tint', 'text-duo-red-dark'],
        ['bg-duo-yellow-tint', 'text-duo-yellow-dark'],
    ];
@endphp

<div class="mx-auto max-w-2xl px-4 py-8">
    {{-- ===== Encabezado ===== --}}
    <div class="duo-fade-up mb-6 flex items-center justify-between gap-4">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[2px] text-duo-silver">Comunidad</p>
            <h1 class="text-3xl font-extrabold text-duo-ink">Amigos</h1>
        </div>
        <span class="duo-chip gap-2 bg-duo-green-tint px-4 py-1.5 text-duo-green-dark">
            <x-duo-icon name="users" class="h-5 w-5" />
            {{ $friends->count() }}
        </span>
    </div>

    @if ($message)
        <div class="duo-banner mb-6 animate-duo-pop {{ $messageType === 'bad' ? 'duo-banner--bad' : 'duo-banner--ok' }}">
            <x-duo-icon :name="$messageType === 'bad' ? 'close' : 'check'" class="h-6 w-6 shrink-0" />
            <span>{{ $message }}</span>
        </div>
    @endif

    {{-- ===== Agregar amigo ===== --}}
    <form wire:submit="addFriend" class="mb-10">
        <label for="friend-email" class="mb-2 block text-sm font-extrabold uppercase tracking-wide text-duo-gray">
            Agrega a un amigo por correo
        </label>
        <div class="flex gap-2">
            <input id="friend-email" type="email" wire:model="email" placeholder="correo@ejemplo.com"
                   class="w-full rounded-2xl border-2 border-duo-border bg-duo-soft px-4 py-3 font-bold text-duo-ink placeholder:text-duo-silver focus:border-duo-blue focus:ring-0">
            <button type="submit" class="duo-btn duo-btn--blue shrink-0">
                <x-duo-icon name="user" class="h-5 w-5" />
                Agregar
            </button>
        </div>
        <p class="mt-2 text-xs font-bold text-duo-silver">Prueba con correos existentes, p. ej. ana@example.com · luis@example.com</p>
    </form>

    {{-- ===== Logros ===== --}}
    <h2 class="mb-3 text-xl font-extrabold">Logros</h2>
    <div class="duo-stagger">
        @forelse ($achievements as $ua)
            <div class="duo-card duo-card-hover mb-3 flex items-center gap-3 !p-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-duo-yellow-tint text-xl">
                    {{ $ua->achievement->icon }}
                </span>
                <div class="min-w-0">
                    <p class="truncate font-extrabold">{{ $ua->achievement->name }}</p>
                    <p class="truncate text-xs font-bold text-duo-gray">{{ $ua->achievement->description }}</p>
                </div>
                @if ($ua->achieved_at)
                    <span class="ml-auto shrink-0 text-xs font-extrabold text-duo-silver">{{ $ua->achieved_at->diffForHumans() }}</span>
                @endif
            </div>
        @empty
            <div class="duo-card duo-card--soft mb-10 text-center font-bold text-duo-gray">
                Aún no tienes logros. ¡Completa tu primera lección!
            </div>
        @endforelse
    </div>

    {{-- ===== Amigos ===== --}}
    <h2 class="mb-3 mt-10 text-xl font-extrabold">Tus amigos ({{ $friends->count() }})</h2>
    <div class="duo-stagger space-y-3">
        @forelse ($friends as $friend)
            @php
                [$avatarBg, $avatarFg] = $avatarPalette[$loop->index % count($avatarPalette)];
                $friendStreak = $friend->streak?->current ?? 0;
            @endphp
            <div class="duo-card duo-card-hover flex items-center gap-3 !p-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full font-extrabold {{ $avatarBg }} {{ $avatarFg }}">
                    {{ strtoupper(mb_substr($friend->name, 0, 1)) }}
                </span>
                <p class="min-w-0 flex-1 truncate font-extrabold">{{ $friend->name }}</p>
                <span class="duo-chip gap-1 px-2.5 py-0.5 text-[13px] {{ $friendStreak > 0 ? 'bg-duo-orange-tint text-duo-orange-dark' : 'bg-duo-soft text-duo-silver' }}">
                    <x-duo-icon name="flame" class="h-4 w-4" />
                    {{ $friendStreak }}
                </span>
                <span class="shrink-0 text-sm font-extrabold text-duo-gray">{{ $friend->weeklyXp() }} XP</span>
            </div>
        @empty
            <div class="duo-card duo-card--soft flex flex-col items-center gap-2 text-center">
                <x-duo-owl class="h-14 w-14" />
                <p class="font-bold text-duo-gray">Sin amigos todavía. Agrega a alguien con su correo.</p>
            </div>
        @endforelse
    </div>

    {{-- ===== Actividad ===== --}}
    <h2 class="mb-3 mt-10 text-xl font-extrabold">Actividad reciente</h2>
    <div class="duo-card overflow-hidden !p-0">
        <div class="duo-stagger divide-y divide-duo-border">
            @forelse ($feed as $activity)
                @php [$avatarBg, $avatarFg] = $avatarPalette[$loop->index % count($avatarPalette)]; @endphp
                <div class="flex items-center gap-3 px-4 py-3 transition hover:bg-duo-soft">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full font-extrabold {{ $avatarBg }} {{ $avatarFg }}">
                        {{ strtoupper(mb_substr($activity->user->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-extrabold">
                            {{ $activity->user->name }}
                            <span class="font-bold text-duo-gray">{{ $activity->description }}</span>
                        </p>
                        <p class="text-xs font-bold text-duo-silver">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <div class="px-4 py-6 text-center font-bold text-duo-gray">Sin actividad por ahora.</div>
            @endforelse
        </div>
    </div>
</div>
