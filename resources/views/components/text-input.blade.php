@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-white border border-gray-200 text-gray-800 placeholder-gray-400 focus:border-accent/50 focus:ring-1 focus:ring-accent/30 rounded-lg shadow-sm text-sm transition']) }}>
