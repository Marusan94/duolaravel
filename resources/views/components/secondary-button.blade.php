<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-duo-card border border-duo-border rounded-md font-semibold text-xs text-duo-ink uppercase tracking-widest shadow-sm hover:bg-duo-soft focus:outline-none focus:ring-2 focus:ring-duo-blue focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
