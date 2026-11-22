<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Normalize the recipient email to lowercase before validation.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('recipient_email'))) {
            $this->merge(['recipient_email' => mb_strtolower($this->input('recipient_email'))]);
        }
    }

    /**
     * Validate delivery details and the total shown at checkout.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'expected_total_minor' => ['required', 'integer', 'min:0'],
            'recipient_email' => ['required', 'email', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[+()0-9 .\/-]{5,30}$/D'],
            'address' => ['required', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'regex:/^[0-9]{5}$/D'],
        ];
    }
}
