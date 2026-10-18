@props(['value'])

<label {{ $attributes->merge(['class' => 'mb-1.5 block text-[13px] font-extrabold uppercase tracking-[1px] text-duo-gray']) }}>
    {{ $value ?? $slot }}
</label>
