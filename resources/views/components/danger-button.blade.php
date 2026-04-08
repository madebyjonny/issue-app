<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-red-500/15 border border-red-500/20 rounded-lg font-medium text-[13px] text-red-400 hover:bg-red-500/25 focus:outline-none focus:ring-2 focus:ring-red-500/40 focus:ring-offset-2 focus:ring-offset-surface-500 transition duration-150']) }}>
    {{ $slot }}
</button>
