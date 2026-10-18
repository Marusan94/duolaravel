@props(['class' => 'h-10 w-10'])

{{-- Mascota original estilo Duolingo (dibujo propio, no copiado) --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <ellipse cx="17" cy="58" rx="9" ry="19" fill="#58A700"/>
    <ellipse cx="83" cy="58" rx="9" ry="19" fill="#58A700"/>
    <ellipse cx="50" cy="54" rx="34" ry="37" fill="#58CC02"/>
    <ellipse cx="50" cy="67" rx="21" ry="22" fill="#89E219"/>
    <circle cx="36" cy="41" r="16" fill="#FFFFFF"/>
    <circle cx="64" cy="41" r="16" fill="#FFFFFF"/>
    <circle cx="37" cy="42" r="7.5" fill="#4B4B4B"/>
    <circle cx="63" cy="42" r="7.5" fill="#4B4B4B"/>
    <circle cx="39.5" cy="39" r="2.8" fill="#FFFFFF"/>
    <circle cx="65.5" cy="39" r="2.8" fill="#FFFFFF"/>
    <path d="M50 51 L58 61 L42 61 Z" fill="#FF9600"/>
    <path d="M41 91 v-7 M59 91 v-7" stroke="#FF9600" stroke-width="4.5" stroke-linecap="round"/>
</svg>
