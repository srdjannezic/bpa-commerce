<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;

class UserService
{
    /**
     * Paginate user accounts from newest to oldest.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function all(): LengthAwarePaginator
    {
        return User::query()->latest('id')->paginate(20);
    }

    /**
     * Create a user with the requested role and handle a duplicate email as a validation error.
     *
     * @param  array{name: string, email: string, role: 'admin'|'customer', password?: string|null}  $data
     *
     * @throws ValidationException
     */
    public function create(array $data): void
    {
        $user = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['role'] === Role::Admin->value ? $data['password'] : null,
        ]);
        $user->role = Role::from($data['role']);

        try {
            $user->save();
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['email' => 'Email adresa je već u upotrebi.']);
        }
    }

    /**
     * Reuse a customer by email or create a customer account for checkout.
     *
     * @throws ValidationException If the email belongs to another role.
     */
    public function findOrCreateCustomer(string $email, string $name): User
    {
        User::query()->upsert([
            ['email' => $email, 'name' => $name, 'role' => Role::Customer->value],
        ], ['email'], ['email']);

        $customer = User::query()->where('email', $email)->lockForUpdate()->firstOrFail();

        if ($customer->role !== Role::Customer) {
            throw ValidationException::withMessages(['recipient_email' => 'Unesi drugu email adresu za potvrdu uplate.']);
        }

        return $customer;
    }
}
