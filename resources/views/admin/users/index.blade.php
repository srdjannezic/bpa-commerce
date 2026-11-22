<x-layout area="admin" title="Administracija korisnika">
    <div class="page-heading">
        <h1>Korisnici</h1>
        <a class="button" href="{{ route('admin.users.create') }}">Dodaj korisnika</a>
    </div>
    <section class="panel">
        <div class="table-scroll">
            <table>
                <thead><tr><th>Ime</th><th>Email</th><th>Uloga</th></tr></thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <th scope="row">{{ $user->name }}</th>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role === \App\Enums\Role::Admin ? 'Administrator' : 'Kupac' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination :items="$users" />
    </section>
</x-layout>
