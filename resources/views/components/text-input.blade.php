@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-2xl border-2 border-duo-border bg-duo-soft px-4 py-3 font-bold text-duo-ink placeholder:text-duo-silver focus:border-duo-blue focus:ring-0 disabled:opacity-60']) }}>
