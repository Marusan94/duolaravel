@php
    $cards = [
        ['label' => 'XP total', 'value' => $totalXp, 'icon' => 'bolt', 'tint' => 'bg-duo-yellow-tint', 'fg' => 'text-duo-yellow-dark'],
        ['label' => 'XP semanal', 'value' => $weeklyXp, 'icon' => 'star', 'tint' => 'bg-duo-blue-tint', 'fg' => 'text-duo-blue-dark'],
        ['label' => 'Racha', 'value' => $streak?->current ?? 0, 'icon' => 'flame', 'tint' => 'bg-duo-orange-tint', 'fg' => 'text-duo-orange-dark'],
        ['label' => 'Coronas', 'value' => $crowns, 'icon' => 'crown', 'tint' => 'bg-duo-yellow-tint', 'fg' => 'text-duo-yellow-dark'],
        ['label' => 'Lecciones', 'value' => $lessonsCompleted, 'icon' => 'book', 'tint' => 'bg-duo-green-tint', 'fg' => 'text-duo-green-dark'],
        ['label' => 'Logros', 'value' => $achievementsCount, 'icon' => 'trophy', 'tint' => 'bg-duo-purple-tint', 'fg' => 'text-duo-purple-dark'],
        ['label' => 'Gemas ganadas', 'value' => $gemsEarned, 'icon' => 'gem', 'tint' => 'bg-duo-blue-tint', 'fg' => 'text-duo-blue-dark'],
        ['label' => 'Liga', 'value' => 'N'.$leagueTier.($leagueRank ? ' · #'.$leagueRank : ''), 'icon' => 'users', 'tint' => 'bg-duo-red-tint', 'fg' => 'text-duo-red-dark'],
    ];
    $barColor = [
        'green' => 'bg-duo-green',
        'blue' => 'bg-duo-blue',
        'purple' => 'bg-duo-purple',
        'yellow' => 'bg-duo-yellow',
        'orange' => 'bg-duo-orange',
    ];
@endphp

<div class="mx-auto max-w-2xl px-4 py-8">
    {{-- ===== Encabezado ===== --}}
    <div class="duo-fade-up mb-8 flex items-center justify-between gap-4">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[2px] text-duo-silver">Estadísticas</p>
            <h1 class="text-3xl font-extrabold text-duo-ink">Tu progreso</h1>
            <p class="mt-1 font-bold text-duo-gray">Cada lección suma XP, coronas y gemas.</p>
        </div>
        <x-duo-owl class="h-20 w-20 shrink-0" />
    </div>

    {{-- ===== Tarjetas ===== --}}
    <div class="duo-stagger grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach ($cards as $card)
            <div class="duo-card flex flex-col items-center p-4 text-center">
                <span class="grid h-11 w-11 place-items-center rounded-full {{ $card['tint'] }} {{ $card['fg'] }}">
                    <x-duo-icon :name="$card['icon']" class="h-6 w-6" />
                </span>
                <p class="mt-2 text-2xl font-extrabold leading-none text-duo-ink">{{ $card['value'] }}</p>
                <p class="mt-1 text-[11px] font-extrabold uppercase tracking-[1px] text-duo-silver">{{ $card['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- ===== Tu camino ===== --}}
    <h2 class="mb-3 mt-10 text-xl font-extrabold">Tu camino</h2>
    <div class="duo-stagger space-y-3">
        @forelse ($unitProgress as $u)
            <div class="duo-card duo-card-hover !p-4">
                <div class="mb-2 flex items-center justify-between gap-3">
                    <p class="min-w-0 truncate font-extrabold">{{ $u['title'] }}</p>
                    <span class="shrink-0 text-xs font-extrabold uppercase tracking-wide text-duo-silver">
                        {{ $u['done'] }}/{{ $u['total'] }} lecciones
                    </span>
                </div>
                <div class="duo-progress !h-3">
                    <span class="{{ $barColor[$u['color']] ?? 'bg-duo-green' }}" style="width: {{ $u['pct'] }}%"></span>
                </div>
            </div>
        @empty
            <div class="duo-card duo-card--soft text-center font-bold text-duo-gray">
                Aún no hay unidades disponibles.
            </div>
        @endforelse
    </div>

    {{-- ===== Llamada a la acción ===== --}}
    <div class="duo-card duo-card--soft mt-8 flex flex-col items-center gap-4 text-center">
        <p class="text-lg font-extrabold">¡La constancia es la clave del búho! 🦉</p>
        <p class="-mt-2 text-sm font-bold text-duo-gray">Completa una lección hoy para mantener tu racha viva.</p>
        <a href="{{ route('learn') }}" wire:navigate.hover class="duo-btn duo-btn--green">
            <x-duo-icon name="bolt" class="h-5 w-5" />
            Seguir aprendiendo
        </a>
    </div>
</div>
