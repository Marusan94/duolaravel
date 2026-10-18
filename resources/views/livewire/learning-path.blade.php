@php
    $unitTheme = [
        'green'  => ['bg' => '#58CC02', 'border' => '#58A700', 'text' => '#FFFFFF'],
        'blue'   => ['bg' => '#1CB0F6', 'border' => '#1899D6', 'text' => '#FFFFFF'],
        'purple' => ['bg' => '#CE82FF', 'border' => '#A560E8', 'text' => '#FFFFFF'],
        'yellow' => ['bg' => '#FFC800', 'border' => '#E5A900', 'text' => '#4B4B4B'],
        'orange' => ['bg' => '#FF9600', 'border' => '#E08600', 'text' => '#FFFFFF'],
    ];
    $offsets = [0, -52, 0, 52];
@endphp

<div class="duo-fade-up mx-auto max-w-2xl px-4 py-10 pb-28">
    @if (session('status'))
        <div class="duo-banner duo-banner--ok mb-6 animate-duo-pop">
            <x-duo-icon name="check" class="h-6 w-6 shrink-0" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if (! $course)
        <div class="duo-card text-center">
            <x-duo-owl class="mx-auto h-20 w-20" />
            <p class="mt-3 text-duo-gray">No tienes un curso activo todavía.</p>
        </div>
    @else
        {{-- ===== Encabezado del curso ===== --}}
        <div class="mb-8 flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[2px] text-duo-silver">Curso</p>
                <h1 class="text-3xl font-extrabold text-duo-ink">{{ $course->title }}</h1>
                <p class="mt-1 flex items-center gap-2 text-duo-gray">
                    <x-duo-icon name="bolt" class="h-5 w-5 text-duo-yellow" />
                    {{ $totalXp }} XP acumulados
                </p>
            </div>
            <x-duo-owl class="h-20 w-20 shrink-0" />
        </div>

        @foreach ($path as $unit)
            @php
                $theme = $unitTheme[$unit['color']] ?? $unitTheme['green'];
                $uDone = collect($unit['skills'])->sum('done');
                $uTotal = collect($unit['skills'])->sum('total');
                $uPct = $uTotal ? (int) round($uDone / $uTotal * 100) : 0;
            @endphp

            {{-- ===== Banner de unidad ===== --}}
            <section class="mb-8">
                <div class="flex items-center justify-between gap-3 rounded-2xl border-b-4 px-4 py-3"
                     style="background: {{ $theme['bg'] }}; border-color: {{ $theme['border'] }}; color: {{ $theme['text'] }};">
                    <div class="min-w-0">
                        <p class="text-[11px] font-extrabold uppercase tracking-[2px] opacity-90">
                            Unidad {{ $loop->iteration }}
                        </p>
                        <h2 class="truncate text-xl font-extrabold" style="color: {{ $theme['text'] }}">{{ $unit['title'] }}</h2>
                        @if ($unit['description'])
                            <p class="truncate text-sm opacity-90">{{ $unit['description'] }}</p>
                        @endif
                        @if ($uTotal > 0)
                            <div class="mt-2 flex items-center gap-2">
                                <div class="h-2 w-28 overflow-hidden rounded-full bg-black/20 sm:w-40">
                                    <span class="block h-full rounded-full bg-white/95 transition-all duration-500" style="width: {{ $uPct }}%"></span>
                                </div>
                                <span class="text-[11px] font-extrabold uppercase tracking-wider opacity-90">{{ $uDone }}/{{ $uTotal }}</span>
                            </div>
                        @endif
                    </div>
                    @if ($unit['guide'])
                        <div x-data="{ guia: false }" class="shrink-0">
                            <button type="button" @click="guia = true"
                                    class="duo-btn duo-btn--white duo-btn--sm shrink-0">
                                Guía
                            </button>

                            <div x-show="guia" x-cloak @keydown.escape.window="guia = false"
                                 class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-4 backdrop-blur-[2px] sm:items-center"
                                 role="dialog" aria-modal="true">
                                <div @click.outside="guia = false"
                                     class="max-h-[85vh] w-full max-w-lg overflow-y-auto rounded-3xl border-2 border-duo-border bg-duo-card p-6 shadow-xl">
                                    <div class="mb-4 flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-[11px] font-extrabold uppercase tracking-[2px] text-duo-silver">Guía de estudio</p>
                                            <h3 class="text-2xl font-extrabold text-duo-ink">{{ $unit['title'] }}</h3>
                                        </div>
                                        <button type="button" @click="guia = false"
                                                class="shrink-0 text-duo-silver transition hover:text-duo-ink" title="Cerrar">
                                            <x-duo-icon name="close" class="h-6 w-6" />
                                        </button>
                                    </div>

                                    <p class="mb-5 text-[15px] font-bold leading-relaxed text-duo-gray">{{ $unit['guide']['intro'] }}</p>

                                    @if (! empty($unit['guide']['points']))
                                        <h4 class="mb-2 text-xs font-extrabold uppercase tracking-[2px] text-duo-blue">Puntos clave</h4>
                                        <ul class="mb-5 space-y-2">
                                            @foreach ($unit['guide']['points'] as $point)
                                                <li class="flex gap-2 text-[15px] font-bold text-duo-ink">
                                                    <span class="mt-0.5 shrink-0 text-duo-green"><x-duo-icon name="check" class="h-5 w-5" /></span>
                                                    <span>{{ $point }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @if (! empty($unit['guide']['code']))
                                        <h4 class="mb-2 text-xs font-extrabold uppercase tracking-[2px] text-duo-purple">Código clave</h4>
                                        <pre class="mb-5 overflow-x-auto rounded-2xl bg-duo-soft p-4 text-[13px] font-bold leading-relaxed text-duo-ink"><code>{{ $unit['guide']['code'] }}</code></pre>
                                    @endif

                                    @if (! empty($unit['guide']['traps']))
                                        <h4 class="mb-2 text-xs font-extrabold uppercase tracking-[2px] text-duo-red">Trampas del examen</h4>
                                        <ul class="space-y-2">
                                            @foreach ($unit['guide']['traps'] as $trap)
                                                <li class="flex gap-2 text-[15px] font-bold text-duo-ink">
                                                    <span class="mt-0.5 shrink-0 text-duo-red"><x-duo-icon name="close" class="h-4 w-4" /></span>
                                                    <span>{{ $trap }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <span class="duo-btn duo-btn--white duo-btn--sm shrink-0 opacity-60">Guía</span>
                    @endif
                </div>

                {{-- ===== Nodos del camino (zigzag) ===== --}}
                <div class="mt-8 flex flex-col items-center gap-7">
                    @foreach ($unit['skills'] as $i => $skill)
                        @php
                            $offset = $offsets[$i % count($offsets)];
                            $stateClass = match ($skill['state']) {
                                'done' => 'duo-node--done',
                                'active' => 'duo-node--active',
                                default => 'duo-node--locked',
                            };
                            $ring = $skill['pct'] * 3.6;
                        @endphp

                        <div class="relative flex flex-col items-center gap-2"
                             style="transform: translateX({{ $offset }}px)">

                            {{-- Burbuja "EMPEZAR" sobre el nodo activo --}}
                            @if ($skill['state'] === 'active')
                                <span class="absolute -top-9 z-10 animate-duo-bounce rounded-xl border-2 border-duo-border bg-duo-card px-3 py-1 text-[13px] font-extrabold uppercase tracking-wider text-duo-green shadow-sm">
                                    Empezar
                                    <span class="absolute -bottom-[7px] left-1/2 h-2.5 w-2.5 -translate-x-1/2 rotate-45 border-b-2 border-r-2 border-duo-border bg-duo-card"></span>
                                </span>
                            @endif

                            <a href="{{ $skill['next_lesson'] ? route('lesson.play', $skill['next_lesson']) : '#' }}"
                               wire:navigate.hover
                               title="{{ $skill['title'] }} · {{ $skill['done'] }}/{{ $skill['total'] }} lecciones"
                               class="duo-skill-link grid h-[90px] w-[90px] place-items-center rounded-full p-[7px] hover:brightness-105 {{ $skill['state'] === 'active' ? 'duo-ring-pulse' : '' }}"
                               style="background: conic-gradient(#FFC800 {{ $ring }}deg, rgb(var(--duo-border)) 0deg);">
                                <span class="duo-node {{ $stateClass }}">
                                    @if ($skill['state'] === 'locked')
                                        <x-duo-icon name="lock" class="h-8 w-8" />
                                    @else
                                        <span>{{ $skill['icon'] }}</span>
                                    @endif
                                </span>
                            </a>

                            <span class="max-w-[180px] truncate text-center text-sm font-extrabold text-duo-gray">
                                {{ $skill['title'] }}
                            </span>

                            @if ($skill['crowns'] > 0)
                                <span class="duo-chip -mt-1 text-duo-yellow">
                                    <x-duo-icon name="crown" class="h-4 w-4" /> {{ $skill['crowns'] }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        @php
            $continueSkill = $path->pluck('skills')->flatten(1)->firstWhere('state', 'active');
            $continueLesson = is_array($continueSkill) ? ($continueSkill['next_lesson'] ?? null) : null;
        @endphp
        @if ($continueLesson)
            <div class="pointer-events-none fixed inset-x-0 bottom-20 z-40 flex justify-center px-4 md:bottom-8 md:justify-end md:pr-10">
                <a href="{{ route('lesson.play', $continueLesson) }}" wire:navigate.hover
                   class="pointer-events-auto duo-btn duo-btn--green shadow-lg">
                    <x-duo-icon name="bolt" class="h-5 w-5" />
                    {{ (is_array($continueSkill) ? ($continueSkill['done'] ?? 0) : 0) > 0 ? 'Seguir aprendiendo' : 'Empezar lección' }}
                </a>
            </div>
        @endif

        {{-- ===== Trofeo final ===== --}}
        <div class="mt-4 flex flex-col items-center pb-8">
            <div class="duo-node duo-node--legendary">
                <x-duo-icon name="trophy" class="h-9 w-9 text-white" />
            </div>
            <p class="mt-3 text-sm font-extrabold uppercase tracking-wider text-duo-purple">
                {{ $path->every(fn ($u) => $u['skills']->every(fn ($s) => $s['state'] === 'done')) ? '¡Curso completado!' : 'Meta final' }}
            </p>
        </div>
    @endif
</div>
