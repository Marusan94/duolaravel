@php
    $itemVisual = [
        'streak_freeze' => ['icon' => 'flame', 'tint' => 'bg-duo-blue-tint', 'fg' => 'text-duo-blue-dark'],
        'heart_refill'  => ['icon' => 'heart', 'tint' => 'bg-duo-red-tint', 'fg' => 'text-duo-red-dark'],
        'double_xp'     => ['icon' => 'bolt', 'tint' => 'bg-duo-purple-tint', 'fg' => 'text-duo-purple-dark'],
    ];
    $ownedByItem = $owned->keyBy('item_id');
    $balance = $wallet?->balance ?? 0;
@endphp

<div class="mx-auto max-w-2xl px-4 py-8">
    {{-- ===== Encabezado ===== --}}
    <div class="duo-fade-up mb-8 flex items-center justify-between gap-4">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[2px] text-duo-silver">Economía</p>
            <h1 class="text-3xl font-extrabold text-duo-ink">Tienda</h1>
        </div>
        <span class="duo-chip gap-2 bg-duo-blue-tint px-4 py-1.5 text-[17px] text-duo-blue-dark">
            <x-duo-icon name="gem" class="h-6 w-6" />
            {{ $balance }}
        </span>
    </div>

    @if (session('status'))
        <div class="duo-banner duo-banner--ok mb-6 animate-duo-pop">
            <x-duo-icon name="check" class="h-6 w-6 shrink-0" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="duo-banner duo-banner--bad mb-6 animate-duo-shake">
            <x-duo-icon name="close" class="h-6 w-6 shrink-0" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- ===== Artículos ===== --}}
    <div class="duo-stagger grid gap-4 sm:grid-cols-2">
        @foreach ($items as $item)
            @php
                $v = $itemVisual[$item->type] ?? ['icon' => 'gem', 'tint' => 'bg-duo-yellow-tint', 'fg' => 'text-duo-yellow-dark'];
                $qty = (int) ($ownedByItem[$item->id]->quantity ?? 0);
                $affordable = $balance >= $item->price;
            @endphp
            <div class="duo-card duo-card-hover relative flex flex-col items-center text-center">
                @if ($qty > 0)
                    <span class="absolute right-3 top-3 rounded-full bg-duo-green-tint px-2.5 py-0.5 text-[11px] font-extrabold uppercase tracking-wide text-duo-green-dark">
                        ×{{ $qty }}
                    </span>
                @endif
                <span class="grid h-16 w-16 place-items-center rounded-full {{ $v['tint'] }} {{ $v['fg'] }}">
                    <x-duo-icon :name="$v['icon']" class="h-8 w-8" />
                </span>
                <h2 class="mt-3 text-lg font-extrabold">{{ $item->name }}</h2>
                <p class="mt-1 min-h-[40px] text-sm font-bold text-duo-gray">{{ $item->description }}</p>
                <button wire:click="purchase({{ $item->id }})" class="duo-btn duo-btn--green duo-btn--wide mt-4">
                    <x-duo-icon name="gem" class="h-5 w-5" />
                    {{ $item->price }}
                </button>
                <p class="mt-2 min-h-[16px] text-xs font-extrabold {{ $affordable ? 'text-duo-green-dark' : 'text-duo-red-dark' }}">
                    @if ($qty > 0)
                        Ya tienes {{ $qty }} en tu mochila
                    @elseif (! $affordable)
                        Te faltan {{ $item->price - $balance }} gemas
                    @else
                        Listo para comprar
                    @endif
                </p>
            </div>
        @endforeach
    </div>

    {{-- ===== Inventario ===== --}}
    <h2 class="mb-3 mt-10 text-xl font-extrabold">Tu inventario</h2>
    <div class="duo-stagger space-y-3">
        @forelse ($owned as $userItem)
            <div class="duo-card duo-card-hover flex items-center justify-between gap-3 !p-3">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-duo-soft text-duo-gray">
                        <x-duo-icon :name="$itemVisual[$userItem->item->type]['icon'] ?? 'gem'" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <p class="truncate font-extrabold">{{ $userItem->item->name }}</p>
                        <p class="text-xs font-extrabold uppercase tracking-wide text-duo-silver">Cantidad: {{ $userItem->quantity }}</p>
                    </div>
                </div>
                <button wire:click="apply({{ $userItem->id }})" class="duo-btn duo-btn--blue duo-btn--sm shrink-0">Usar</button>
            </div>
        @empty
            <div class="duo-card duo-card--soft text-center">
                <x-duo-owl class="mx-auto h-14 w-14" />
                <p class="mt-2 font-bold text-duo-gray">Todavía no tienes artículos. ¡Compra uno con tus gemas!</p>
            </div>
        @endforelse
    </div>
</div>
