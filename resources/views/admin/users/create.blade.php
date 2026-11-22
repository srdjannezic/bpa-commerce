<x-layout area="admin" title="Dodaj korisnika">
    <a class="back-link" href="{{ route('admin.users.index') }}">Svi korisnici</a>
    <section class="form-panel edit-panel">
        <h1>Dodaj korisnika</h1>
        <form method="post" action="{{ route('admin.users.store') }}" class="fields">
            @csrf
            <label>Ime i prezime<input name="name" autocomplete="off" value="{{ old('name') }}" maxlength="120" required autofocus></label>
            <label>Email adresa<input name="email" type="email" autocomplete="off" value="{{ old('email') }}" maxlength="255" required></label>
            <label>Uloga
                <select name="role" required>
                    <option value="customer" @selected(old('role') === 'customer')>Kupac</option>
                    <option value="admin" @selected(old('role') === 'admin')>Administrator</option>
                </select>
            </label>
            <p class="muted" id="admin-password-hint">Lozinka se podešava samo za administratora.</p>
            <label>Lozinka administratora<input name="password" type="password" autocomplete="new-password" minlength="8" aria-describedby="admin-password-hint"></label>
            <label>Potvrda lozinke<input name="password_confirmation" type="password" autocomplete="new-password" minlength="8"></label>
            <button class="button" type="submit">Sačuvaj korisnika</button>
        </form>
    </section>
</x-layout>
