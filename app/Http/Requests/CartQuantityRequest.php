<?php

namespace App\Http\Requests;

use App\Services\CartService;
use Illuminate\Foundation\Http\FormRequest;

class CartQuantityRequest extends FormRequest
{
    /**
     * Limit the requested quantity to the supported per-item range.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return ['quantity' => ['required', 'integer', 'min:1', 'max:'.CartService::MAX_QUANTITY]];
    }
}
