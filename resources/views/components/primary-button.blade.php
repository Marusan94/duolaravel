<button {{ $attributes->merge(['type' => 'submit', 'class' => 'duo-btn duo-btn--green']) }}>
    {{ $slot }}
</button>
