<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn-secondary uppercase tracking-widest disabled:opacity-25']) }}>
    {{ $slot }}
</button>
