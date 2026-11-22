<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Normalize the login email to lowercase before validation.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower($this->input('email'))]);
        }
    }

    /**
     * Validate the email and password used for administrator login.
     *
     * @return array<string, list<string|Closure>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['bail', 'required', 'string', self::passwordLength()],
        ];
    }

    /**
     * Return a rule that enforces the bcrypt byte limit and rejects null bytes.
     *
     * @return Closure(string, string, Closure): void
     */
    public static function passwordLength(): Closure
    {
        return /** Reject passwords longer than 72 bytes or containing a null byte. */ function (string $attribute, string $value, Closure $fail): void {
            if (strlen($value) > 72 || str_contains($value, "\0")) {
                $fail('Unesi lozinku do 72 bajta.');
            }
        };
    }
}
