@props(['title' => 'Oprema za boravak napolju', 'area' => 'store', 'staticCatalog' => false])
@php
    $adminShell = $area === 'admin';
    $adminLogin = $area === 'admin-login';
    $adminTheme = $adminShell || $adminLogin;
@endphp
<!doctype html>
<html lang="sr-Latn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('staza.css') }}">
    @if ($adminTheme)
        <link rel="stylesheet" href="{{ asset('admin.css') }}">
    @endif
</head>
<body @class(['admin-theme' => $adminTheme, 'admin-shell' => $adminShell, 'admin-login-shell' => $adminLogin])>
<a class="skip" href="#sadrzaj">Preskoči na sadržaj</a>
@if ($adminShell)
    <x-admin-sidebar />
    <header class="admin-bar">
        <span>Administracija</span>
        <a href="{{ route('catalog') }}">Otvori prodavnicu</a>
    </header>
@elseif ($adminLogin)
    <header class="admin-login-bar">
        <a class="brand admin-brand" href="{{ route('catalog') }}">
            <span class="brand-mark" aria-hidden="true"></span>{{ config('app.name') }}
        </a>
        <a href="{{ route('catalog') }}">Prodavnica</a>
    </header>
@else
    <header class="site-header">
        <div class="wrap topbar">
            <a class="brand" href="{{ route('catalog') }}"><span class="brand-mark" aria-hidden="true"></span>{{ config('app.name') }}</a>
            <nav aria-label="Prodavnica">
                <a class="{{ $staticCatalog || request()->routeIs('products.show') ? 'current' : '' }}" href="{{ route('catalog') }}">Prodavnica</a>
            </nav>
            <nav class="nav-right" aria-label="Kupovina">
                <a href="{{ route('orders.history') }}">Moje porudžbine</a>
                <a class="bag-link" href="{{ route('cart.index') }}"><span class="bag-icon" aria-hidden="true"></span> Korpa</a>
            </nav>
        </div>
    </header>
@endif
<main id="sadrzaj" @class([
    'page-main',
    'wrap inner-page' => ! $staticCatalog && ! $adminTheme,
    'admin-main' => $adminShell,
    'admin-login-main' => $adminLogin,
])>
    @unless ($staticCatalog)
        @if (session('status'))
            <p class="notice" role="status">{{ session('status') }}</p>
        @endif
        @if ($errors->any())
            <div class="notice error" role="alert">
                <ul>@foreach (array_unique($errors->all()) as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
    @endunless
    {{ $slot }}
</main>
@unless ($adminTheme)
    <footer class="site-footer">
        <div class="wrap footer-row">
            <a class="brand small-brand" href="{{ route('catalog') }}">{{ config('app.name') }}</a>
            <span>Oprema za boravak napolju</span>
            <a href="{{ route('admin.home') }}">Administracija</a>
        </div>
    </footer>
@endunless
</body>
</html>
