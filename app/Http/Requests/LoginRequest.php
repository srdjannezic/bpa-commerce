<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower($this->input('email'))]);
        }
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['bail', 'required', 'string', self::passwordLength()],
        ];
    }

    public static function passwordLength(): Closure
    {
        return function (string $attribute, string $value, Closure $fail): void {
            if (strlen($value) > 72 || str_contains($value, "\0")) {
                $fail('Unesi lozinku do 72 bajta.');
            }
        };
    }
}
