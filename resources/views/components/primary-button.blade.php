<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2 bg-accent border border-accent/20 rounded-lg font-medium text-sm text-white shadow-sm shadow-accent/10 hover:bg-accent-hover active:bg-accent-muted focus:outline-none focus:ring-2 focus:ring-accent/40 focus:ring-offset-2 focus:ring-offset-surface-500 transition duration-150']) }}>
    {{ $slot }}
</button>
