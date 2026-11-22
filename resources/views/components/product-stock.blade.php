@props(['stock'])
<p {{ $attributes->class(['stock', 'available' => $stock > 0, 'unavailable' => $stock === 0]) }}>
    {{ $stock > 0 ? "Na stanju: {$stock} kom." : 'Rasprodato' }}
</p>
