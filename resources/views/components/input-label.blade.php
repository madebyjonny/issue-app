@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-[13px] text-gray-600']) }}>
    {{ $value ?? $slot }}
</label>
