@props(['product', 'eager' => false])
@php
    $images = ['ranac', 'termos', 'torbica', 'podloga'];
    [$source, $fragment] = array_pad(explode('#', $product->image_url ?? '', 2), 2, '');
    $sheet = $source === '/images/oprema-dodatna.webp' && preg_match('/^([0-3])-([0-4])$/D', $fragment, $tile);
    $sprite = $source === '/images/oprema.webp' && in_array($fragment, $images, true);
@endphp
<div {{ $attributes->class(['product-picture', 'sprite-picture sprite-'.$fragment => $sprite, 'catalog-sheet' => $sheet]) }}>
    @if ($product->image_url)
        <img src="{{ ($sprite || $sheet) ? asset(ltrim($source, '/')) : $product->image_url }}" alt="{{ $product->name }}" loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async"
            @if ($sheet) style="--column: {{ $tile[1] }}; --row: {{ $tile[2] }};" @endif>
    @else
        <span class="brand-mark" aria-hidden="true"></span>
    @endif
</div>
