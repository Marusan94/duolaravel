<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public int $streak = 0;

    public int $hearts = 5;

    public int $gems = 0;

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $this->streak = (int) ($user->streak?->current ?? 0);
        $this->hearts = (int) ($user->heart?->current ?? 5);
        $this->gems = (int) ($user->gemsWallet?->balance ?? 0);
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

@php
    $navItems = [
        ['route' => 'learn', 'label' => 'Aprender', 'icon' => 'book', 'active' => ['learn', 'dashboard']],
        ['route' => 'leaderboard', 'label' => 'Liga', 'icon' => 'trophy', 'active' => ['leaderboard']],
        ['route' => 'shop', 'label' => 'Tienda', 'icon' => 'shop', 'active' => ['shop']],
        ['route' => 'social', 'label' => 'Amigos', 'icon' => 'users', 'active' => ['social']],
        ['route' => 'progress', 'label' => 'Progreso', 'icon' => 'user', 'active' => ['progress', 'profile']],
    ];
@endphp

{{-- Raíz única requerida por Livewire --}}
<div>
{{-- ================= SIDEBAR (escritorio) ================= --}}
<aside class="fixed inset-y-0 left-0 z-40 hidden w-[256px] flex-col border-r-2 border-duo-border bg-duo-surface px-4 py-6 md:flex">
    <a href="{{ route('learn') }}" wire:navigate class="mb-8 flex items-center gap-2 px-2">
        <x-duo-owl class="h-10 w-10" />
        <span class="text-[22px] font-extrabold lowercase tracking-tight text-duo-green">duoLaravel</span>
    </a>

    <nav class="flex flex-1 flex-col gap-2">
        @foreach ($navItems as $item)
            <a href="{{ route($item['route']) }}" wire:navigate.hover
               class="duo-nav-item {{ request()->routeIs(...$item['active']) ? 'duo-nav-item--active' : '' }}">
                <x-duo-icon :name="$item['icon']" class="h-7 w-7 shrink-0" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="border-t-2 border-duo-border pt-4">
        <div class="mb-3 flex items-center gap-3 px-2">
            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-duo-purple text-[17px] font-extrabold text-white">
                {{ strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="truncate text-[15px] font-extrabold text-duo-ink">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-duo-silver">{{ auth()->user()->email }}</p>
            </div>
        </div>
        <button wire:click="logout"
                class="w-full rounded-xl px-3 py-2 text-left text-[13px] font-extrabold uppercase tracking-wider text-duo-silver transition hover:bg-duo-soft hover:text-duo-ink">
            Salir
        </button>
    </div>
</aside>

{{-- ================= BARRA SUPERIOR (contadores) ================= --}}
<header class="sticky top-0 z-30 border-b-2 border-duo-border bg-duo-surface md:pl-[256px]">
    <div class="mx-auto flex h-[70px] max-w-5xl items-center justify-between gap-3 px-4">
        <a href="{{ route('learn') }}" wire:navigate class="flex min-w-0 items-center gap-2 md:hidden">
            <x-duo-owl class="h-8 w-8 shrink-0" />
            <span class="hidden truncate text-xl font-extrabold lowercase text-duo-green sm:inline">duoLaravel</span>
        </a>
        <div class="hidden md:block"></div>

        <div class="flex items-center gap-2 sm:gap-4">
            <span class="duo-chip text-duo-orange" title="Racha">
                <x-duo-icon name="flame" class="h-6 w-6 animate-duo-flicker" /> {{ $streak }}
            </span>
            <span class="duo-chip text-duo-blue" title="Gemas">
                <x-duo-icon name="gem" class="h-6 w-6" /> {{ $gems }}
            </span>
            <span class="duo-chip text-duo-red" title="Corazones">
                <x-duo-icon name="heart" class="h-6 w-6" /> {{ $hearts }}
            </span>
            <livewire:notification-bell />

            {{-- ===== Selector de tema ===== --}}
            <div x-data="{
                    abierto: false,
                    tema: localStorage.getItem('duo-theme') || 'clasico',
                    temas: [
                        { id: 'clasico', nombre: 'Clásico', punto: '#FFFFFF' },
                        { id: 'menta', nombre: 'Menta', punto: '#E4F7F2' },
                        { id: 'amanecer', nombre: 'Amanecer', punto: '#FFECE0' },
                        { id: 'noche', nombre: 'Noche', punto: '#131F24' },
                        { id: 'selva', nombre: 'Selva', punto: '#0A1A10' },
                        { id: 'uva', nombre: 'Uva', punto: '#170E25' },
                    ],
                    aplicar(id) {
                        document.documentElement.setAttribute('data-theme', id);
                        if (window.duoThemeAnim) window.duoThemeAnim();
                        if (window.duoSetThemeCookie) window.duoSetThemeCookie(id);
                        try { localStorage.setItem('duo-theme', id); } catch (e) {}
                        this.tema = id;
                        this.abierto = false;
                    },
                 }" class="relative">
                <button type="button" @click="abierto = !abierto"
                        class="inline-flex items-center rounded-full p-1 text-duo-purple transition hover:bg-duo-soft"
                        title="Cambiar tema">
                    <x-duo-icon name="palette" class="h-6 w-6" />
                </button>

                <div x-show="abierto" x-cloak @click.outside="abierto = false"
                     class="absolute right-0 top-full z-50 mt-2 w-48 rounded-2xl border-2 border-duo-border bg-duo-card p-2 shadow-lg">
                    <template x-for="t in temas" :key="t.id">
                        <button type="button" @click="aplicar(t.id)"
                                class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-[14px] font-extrabold text-duo-gray transition hover:bg-duo-soft hover:text-duo-ink">
                            <span class="h-5 w-5 shrink-0 rounded-full border-2 border-duo-border" :style="'background: ' + t.punto"></span>
                            <span class="flex-1" x-text="t.nombre"></span>
                            <span x-show="tema === t.id" class="text-duo-green">
                                <x-duo-icon name="check" class="h-5 w-5" />
                            </span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- ================= NAV INFERIOR (móvil) ================= --}}
<nav class="fixed inset-x-0 bottom-0 z-40 flex border-t-2 border-duo-border bg-duo-surface pb-[env(safe-area-inset-bottom)] md:hidden">
    @foreach ($navItems as $item)
        <a href="{{ route($item['route']) }}" wire:navigate.hover
           class="flex flex-1 flex-col items-center gap-1 py-2.5 transition {{ request()->routeIs(...$item['active']) ? 'text-duo-blue' : 'text-duo-silver' }}">
            <x-duo-icon :name="$item['icon']" class="h-6 w-6" />
            <span class="text-[10px] font-extrabold uppercase tracking-wide">{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
</div>
