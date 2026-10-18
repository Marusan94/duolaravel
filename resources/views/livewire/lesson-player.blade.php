@php
    $progress = $total ? (int) round($index / $total * 100) : 0;
    $type = $exercise?->type;
    $isOption = in_array($type, ['select_translation', 'select_image', 'listen_select'], true);
    $isText = in_array($type, ['translate', 'type_translation', 'fill_blank'], true);
    $isOrder = in_array($type, ['order_words', 'order_tokens'], true);
    $isMatch = $type === 'match_pairs';
    $hasAnswer = $this->hasAnswer();
@endphp

<div class="flex min-h-screen flex-col bg-duo-surface"
     x-data="{
        sonido: (window.duoFx ? window.duoFx.isEnabled() : true),
        enfoque: (function () { try { return localStorage.getItem('duo-focus') === '1'; } catch (e) { return false; } })(),
        toggleSonido() {
            this.sonido = !this.sonido;
            if (window.duoFx) window.duoFx.setEnabled(this.sonido);
        },
        toggleEnfoque() {
            this.enfoque = !this.enfoque;
            try { localStorage.setItem('duo-focus', this.enfoque ? '1' : '0'); } catch (e) {}
        },
        onKey(e) {
            const tag = e.target && e.target.tagName ? e.target.tagName : '';
            if (tag === 'SELECT' || tag === 'TEXTAREA') return;
            if ((tag === 'BUTTON' || tag === 'A') && (e.key === 'Enter' || e.key === ' ')) return;
            if (tag === 'INPUT' && e.key !== 'Enter') return;
            if (/^[1-9]$/.test(e.key)) {
                e.preventDefault();
                const opciones = Array.from(document.querySelectorAll('.duo-option:not([disabled])'));
                const opcion = opciones[parseInt(e.key, 10) - 1];
                if (opcion) opcion.click();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const boton = document.querySelector('[data-duo-primary]:not([disabled])');
                if (boton) boton.click();
            }
        },
     }"
     @keydown.window="onKey($event)">
    {{-- ================= Barra superior ================= --}}
    <header class="mx-auto flex w-full max-w-2xl items-center gap-4 px-4 py-4">
        <a href="{{ route('learn') }}" wire:navigate.hover
           class="shrink-0 text-duo-silver transition hover:text-duo-gray" title="Salir">
            <x-duo-icon name="close" class="h-7 w-7" />
        </a>

        <div class="duo-progress flex-1" role="progressbar" aria-valuenow="{{ $progress }}">
            <span style="width: {{ $progress }}%"></span>
        </div>

        <button type="button" @click="toggleEnfoque()" class="shrink-0 transition hover:scale-110"
                :class="enfoque ? 'text-duo-green' : 'text-duo-silver'"
                :title="enfoque ? 'Modo enfoque activado' : 'Modo enfoque: oculta distracciones'">
            <x-duo-icon name="focus" class="h-6 w-6" />
        </button>

        <button type="button" @click="toggleSonido()" class="shrink-0 transition hover:scale-110"
                :class="sonido ? 'text-duo-blue' : 'text-duo-silver'"
                :title="sonido ? 'Silenciar' : 'Activar sonido'">
            <x-duo-icon name="sound" x-show="sonido" x-cloak class="h-6 w-6" />
            <x-duo-icon name="sound-off" x-show="!sonido" x-cloak class="h-6 w-6" />
        </button>

        <span x-show="!enfoque" class="flex shrink-0 items-center gap-1 font-extrabold text-duo-red {{ $correct === false ? 'animate-duo-shake' : '' }}" title="Corazones">
            <x-duo-icon name="heart" class="h-6 w-6" />
            {{ $hearts ?? '—' }}
        </span>
    </header>

    {{-- ================= Contenido ================= --}}
    <main class="mx-auto w-full max-w-2xl flex-1 px-4 pb-64">
        @if ($finished || ! $exercise)
            <div class="duo-card mt-10 text-center">
                <x-duo-owl class="mx-auto h-20 w-20" />
                <p class="mt-4 text-lg font-extrabold text-duo-ink">Esta lección no tiene ejercicios todavía.</p>
                <a href="{{ route('learn') }}" wire:navigate.hover class="duo-btn duo-btn--green mt-6 inline-flex">
                    Volver al camino
                </a>
            </div>
        @else
            <div class="mb-2 flex items-center justify-between">
                <span class="text-[13px] font-extrabold uppercase tracking-[2px] text-duo-silver">
                    Pregunta {{ $index + 1 }} de {{ $total }}
                </span>
                <span x-show="!enfoque" class="duo-chip bg-duo-soft text-duo-gray">{{ $score }} correctas</span>
            </div>

            <h1 wire:key="q-{{ $index }}" class="duo-fade-up mb-7 text-balance text-[26px] font-extrabold leading-tight text-duo-ink">
                {{ $exercise->prompt }}
            </h1>

            {{-- ===== Opción múltiple ===== --}}
            @if ($isOption)
                <div class="grid gap-3">
                    @foreach ($exercise->options as $option)
                        @php
                            $isSelected = $selected == $option->id;
                            $stateClass = '';
                            if ($correct !== null) {
                                if ($isSelected) {
                                    $stateClass = $correct ? 'duo-option--correct' : 'duo-option--wrong';
                                } elseif ($option->is_correct && ! $correct) {
                                    $stateClass = 'duo-option--correct';
                                }
                            } elseif ($isSelected) {
                                $stateClass = 'duo-option--selected';
                            }
                            if ($stateClass !== '') {
                                $stateClass .= ' animate-duo-popin';
                            }
                        @endphp
                        <button type="button"
                                wire:click="$set('selected', {{ $option->id }})"
                                @disabled($correct !== null)
                                class="duo-option {{ $stateClass }}">
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-md border-2 border-current text-xs font-extrabold">
                                {{ chr(65 + $loop->index) }}
                            </span>
                            <span>{{ $option->text }}</span>
                        </button>
                    @endforeach
                </div>

            {{-- ===== Texto libre ===== --}}
            @elseif ($isText)
                <textarea wire:key="texto-{{ $exercise->id }}" wire:model.live="text" rows="2" @disabled($correct !== null) autofocus
                          placeholder="Escribe tu respuesta…"
                          class="w-full resize-none rounded-2xl border-2 border-duo-border bg-duo-card p-4 text-[17px] font-bold text-duo-ink placeholder:font-bold placeholder:text-duo-silver focus:border-duo-blue focus:ring-0"></textarea>

            {{-- ===== Ordenar tokens ===== --}}
            @elseif ($isOrder)
                @php
                    $tokens = $tokensMap[$exercise->id] ?? (array) ($exercise->payload['answer'] ?? []);
                    $used = array_count_values($order);
                    $pool = [];
                    foreach ($tokens as $token) {
                        if (($used[$token] ?? 0) > 0) {
                            $used[$token]--;
                            continue;
                        }
                        $pool[] = $token;
                    }
                @endphp
                <div class="mb-4 flex min-h-[64px] flex-wrap content-start gap-2 rounded-2xl border-2 border-duo-border bg-duo-soft p-3">
                    @forelse ($order as $i => $token)
                        <button type="button" wire:click="removeToken({{ $i }})"
                                class="rounded-xl border-2 border-duo-border bg-duo-card px-3 py-2 text-[15px] font-extrabold text-duo-ink">
                            {{ $token }}
                        </button>
                    @empty
                        <span class="self-center text-sm font-bold text-duo-silver">
                            Toca las opciones en orden…
                        </span>
                    @endforelse
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($pool as $token)
                        <button type="button" wire:click="addToken(@js($token))" @disabled($correct !== null)
                                class="duo-btn duo-btn--white duo-btn--sm">
                            {{ $token }}
                        </button>
                    @endforeach
                </div>

            {{-- ===== Emparejar ===== --}}
            @elseif ($isMatch)
                @php
                    $pairs = (array) ($exercise->payload['pairs'] ?? []);
                    $rights = $rightsMap[$exercise->id] ?? array_values($pairs);
                @endphp
                <div class="grid gap-3">
                    @foreach ($pairs as $left => $right)
                        <div class="flex items-center gap-3">
                            <span class="w-36 shrink-0 rounded-xl bg-duo-soft px-3 py-2 text-center text-[15px] font-extrabold text-duo-ink">
                                {{ $left }}
                            </span>
                            <select wire:model.live="matches.{{ $left }}" @disabled($correct !== null)
                                    class="flex-1 rounded-xl border-2 border-duo-border bg-duo-card py-2 text-[15px] font-bold text-duo-ink focus:border-duo-blue focus:ring-0">
                                <option value="">— elegir —</option>
                                @foreach ($rights as $r)
                                    <option value="{{ $r }}">{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

            {{-- ===== Fallback ===== --}}
            @else
                <input type="text" wire:key="input-{{ $exercise->id }}" wire:model.live="text" @disabled($correct !== null) autofocus
                       placeholder="Respuesta…"
                       class="w-full rounded-2xl border-2 border-duo-border bg-duo-card p-4 text-[17px] font-bold text-duo-ink focus:border-duo-blue focus:ring-0">
            @endif
        @endif
    </main>

    {{-- ================= Barra inferior fija ================= --}}
    @if (! $finished && $exercise)
        <footer class="fixed inset-x-0 bottom-0 border-t-2 border-duo-border bg-duo-surface">
            <div class="mx-auto max-w-2xl px-4 py-4">
                @if ($correct !== null)
                    <div class="duo-banner {{ $correct ? 'duo-banner--ok' : 'duo-banner--bad' }} mb-3 animate-duo-pop">
                        <x-duo-icon :name="$correct ? 'check' : 'close'" class="h-7 w-7 shrink-0" />
                        <div>
                            <p class="text-lg">{{ $correct ? '¡Excelente!' : 'No exactamente…' }}</p>
                            <p class="text-sm font-bold opacity-80">
                                {{ $correct ? '+5 XP' : 'Sigue intentando en la próxima.' }}
                            </p>
                        </div>
                    </div>
                @endif

                @if ($correct === null)
                    <button wire:click="submit" @disabled(! $hasAnswer) data-duo-primary
                            class="duo-btn duo-btn--green duo-btn--wide {{ $hasAnswer ? 'duo-ready-pulse' : '' }}">
                        Comprobar
                    </button>
                @else
                    <button wire:click="next" data-duo-primary class="duo-btn duo-btn--wide {{ $correct ? 'duo-btn--green' : 'duo-btn--red' }}">
                        {{ $index < $total - 1 ? 'Continuar' : 'Terminar' }}
                    </button>
                @endif

                <p class="mt-3 hidden text-center text-[12px] font-bold text-duo-silver sm:block">
                    <span class="rounded-md border border-duo-border px-1.5 py-0.5">1–4</span> elegir
                    <span class="mx-1.5">·</span>
                    <span class="rounded-md border border-duo-border px-1.5 py-0.5">Enter</span> comprobar / continuar
                </p>
            </div>
        </footer>
    @endif
</div>
