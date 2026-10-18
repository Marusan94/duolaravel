@php
    $tierNames = [1 => 'Bronce', 2 => 'Plata', 3 => 'Oro', 4 => 'Zafiro', 5 => 'Rubí', 6 => 'Esmeralda', 7 => 'Amatista', 8 => 'Perla', 9 => 'Obsidiana', 10 => 'Diamante'];
    $tier = $members->first()?->league?->tier ?? 1;
    $tierName = $tierNames[$tier] ?? 'Diamante';
    $count = $members->count();
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
            <p class="text-xs font-extrabold uppercase tracking-[2px] text-duo-silver">Clasificación semanal</p>
            <h1 class="text-3xl font-extrabold text-duo-ink">Liga {{ $tierName }}</h1>
        </div>
        <span class="grid h-16 w-16 place-items-center rounded-full bg-duo-yellow-tint text-duo-yellow-dark">
            <x-duo-icon name="trophy" class="h-8 w-8" />
        </span>
    </div>

    {{-- ===== Resumen ===== --}}
    <div class="duo-stagger mb-6 flex flex-wrap gap-2">
        <span class="duo-chip gap-2 bg-duo-yellow-tint px-4 py-1.5 text-duo-yellow-dark">
            <x-duo-icon name="bolt" class="h-5 w-5" />
            {{ $weeklyXp }} XP esta semana
        </span>
        <span class="duo-chip gap-2 bg-duo-blue-tint px-4 py-1.5 text-duo-blue-dark">
            <x-duo-icon name="star" class="h-5 w-5" />
            {{ $totalXp }} XP total
        </span>
        <span class="duo-chip gap-2 bg-duo-orange-tint px-4 py-1.5 text-duo-orange-dark">
            <x-duo-icon name="flame" class="h-5 w-5" />
            {{ $streak?->current ?? 0 }} días de racha
        </span>
    </div>

    {{-- ===== Tabla ===== --}}
    <div class="duo-card overflow-hidden !p-0">
        <div class="duo-stagger divide-y divide-duo-border">
            @foreach ($members as $i => $member)
                @php
                    $rank = $i + 1;
                    $isMe = $member->user_id === auth()->id();
                    $rankChip = match (true) {
                        $rank <= 2 => 'bg-duo-green text-white',
                        $count >= 5 && $rank > $count - 2 => 'bg-duo-red text-white',
                        default => 'bg-duo-soft text-duo-gray',
                    };
                    [$avatarBg, $avatarFg] = $avatarPalette[$i % count($avatarPalette)];
                @endphp
                @if ($count >= 5 && $rank === 3)
                    <div class="flex items-center gap-2 bg-duo-green-tint px-4 py-1.5 text-[11px] font-extrabold uppercase tracking-[1.5px] text-duo-green-dark">
                        <span class="h-2 w-2 rounded-full bg-duo-green"></span>
                        Zona de ascenso
                    </div>
                @endif
                @if ($count >= 5 && $rank === $count - 1)
                    <div class="flex items-center gap-2 bg-duo-red-tint px-4 py-1.5 text-[11px] font-extrabold uppercase tracking-[1.5px] text-duo-red-dark">
                        <span class="h-2 w-2 rounded-full bg-duo-red"></span>
                        Zona de descenso
                    </div>
                @endif
                <div class="flex items-center gap-3 px-4 py-3 {{ $isMe ? 'bg-duo-blue-tint' : 'transition hover:bg-duo-soft' }}">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-sm font-extrabold {{ $rankChip }}">
                        {{ $rank }}
                    </span>
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full font-extrabold {{ $avatarBg }} {{ $avatarFg }}">
                        {{ strtoupper(mb_substr($member->user->name, 0, 1)) }}
                    </span>
                    <p class="min-w-0 flex-1 truncate font-extrabold">
                        {{ $member->user->name }}
                        @if ($isMe)
                            <span class="ml-1 rounded-full bg-duo-blue px-2 py-0.5 text-[11px] font-extrabold uppercase tracking-wide text-white">Tú</span>
                        @endif
                    </p>
                    <span class="shrink-0 font-extrabold text-duo-gray">{{ $member->weekly_xp }} XP</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ===== Leyenda ===== --}}
    <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm font-bold text-duo-gray">
        <span class="flex items-center gap-2">
            <span class="h-3 w-3 rounded-full bg-duo-green"></span>
            Los 2 primeros ascienden de liga
        </span>
        <span class="flex items-center gap-2">
            <span class="h-3 w-3 rounded-full bg-duo-red"></span>
            Los 2 últimos descienden
        </span>
    </div>

    <p class="mt-6 text-center text-sm font-bold text-duo-silver">
        La liga se cierra cada domingo a las 23:00 · Gana XP completando lecciones para subir posiciones.
    </p>
</div>
