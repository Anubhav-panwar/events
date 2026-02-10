<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-primary uppercase tracking-widest']) }}>
    {{ $slot }}
</button>
