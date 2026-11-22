<aside class="admin-sidebar">
    <a class="brand admin-brand" href="{{ route('admin.home') }}">
        <span class="brand-mark" aria-hidden="true"></span>{{ config('app.name') }}
    </a>
    <nav class="admin-nav" aria-label="Administracija">
        @foreach (['products' => 'Proizvodi', 'categories' => 'Kategorije', 'orders' => 'Porudžbine', 'users' => 'Korisnici'] as $section => $label)
            <a href="{{ route('admin.'.$section.'.index') }}"
                @if (request()->routeIs('admin.'.$section.'.*')) aria-current="page" @endif>
                {{ $label }}
            </a>
        @endforeach
    </nav>
    <form class="admin-signout" action="{{ route('admin.logout') }}" method="post">
        @csrf
        <button type="submit">Odjava</button>
    </form>
</aside>
