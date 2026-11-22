<?php

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class CreateUserRequest extends FormRequest
{
    /**
     * Normalize the account email to lowercase before validation.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower($this->input('email'))]);
        }
    }

    /**
     * Validate account details and require a password for administrators.
     *
     * @return array<string, list<string|Enum|\Closure>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::enum(Role::class)],
            'password' => ['bail', 'required_if:role,admin', 'nullable', 'string', 'min:8', 'confirmed', LoginRequest::passwordLength()],
        ];
    }
}
