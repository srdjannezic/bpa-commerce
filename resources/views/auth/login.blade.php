<x-layout area="admin-login" title="Prijava administratora">
    <section class="form-panel auth-panel">
        <h1>Prijava administratora</h1>
        <form method="post" action="{{ route('admin.login.store') }}" class="fields">
            @csrf
            <label>Email adresa
                <input name="email" type="email" autocomplete="username" value="{{ old('email') }}" maxlength="255" required autofocus>
            </label>
            <label>Lozinka
                <input name="password" type="password" autocomplete="current-password" required>
            </label>
            <button class="button" type="submit">Prijavi se</button>
        </form>
    </section>
</x-layout>
