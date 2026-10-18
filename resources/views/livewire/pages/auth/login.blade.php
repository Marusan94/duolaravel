<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h1 class="mb-6 text-center text-2xl font-extrabold">Inicia sesión</h1>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input wire:model="form.email" id="email" class="mt-1 block w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Contraseña" />

            <x-text-input wire:model="form.password" id="password" class="mt-1 block w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="mt-4">
            <label for="remember" class="inline-flex cursor-pointer items-center gap-2">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-duo-border text-duo-blue focus:ring-0" name="remember">
                <span class="text-sm font-bold text-duo-gray">Recordarme</span>
            </label>
        </div>

        <div class="mt-6 flex flex-col items-stretch gap-4">
            <x-primary-button class="duo-btn--wide">
                Iniciar sesión
            </x-primary-button>

            <div class="flex items-center justify-between text-sm">
                @if (Route::has('password.request'))
                    <a class="font-bold text-duo-gray underline decoration-2 hover:text-duo-ink" href="{{ route('password.request') }}" wire:navigate>
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif

                <a class="font-bold text-duo-blue underline decoration-2 hover:text-duo-blue-dark" href="{{ route('register') }}" wire:navigate>
                    Crear cuenta
                </a>
            </div>
        </div>
    </form>

    <p class="mt-8 text-center text-xs font-bold text-duo-silver">
        Cuenta lista para probar: maru9419@hotmail.com · contraseña: password
    </p>
</div>
