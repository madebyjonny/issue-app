@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-[13px] text-gray-400']) }}>
    {{ $value ?? $slot }}
</label>
