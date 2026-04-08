@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-surface-400/50 border border-white/[0.06] text-gray-200 placeholder-gray-600 focus:border-accent/50 focus:ring-1 focus:ring-accent/30 rounded-lg shadow-sm text-sm transition']) }}>
