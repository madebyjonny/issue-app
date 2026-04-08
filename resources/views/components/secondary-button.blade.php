<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-surface-300/60 border border-white/[0.06] rounded-lg font-medium text-[13px] text-gray-300 hover:bg-surface-300 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/10 focus:ring-offset-2 focus:ring-offset-surface-500 disabled:opacity-25 transition duration-150']) }}>
    {{ $slot }}
</button>
