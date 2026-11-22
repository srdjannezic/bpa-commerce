<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Validate product fields and require a revision when editing a product.
     *
     * @return array<string, list<string|Closure>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['required', 'string', 'max:4000'],
            'price' => ['required', 'regex:/^[0-9]{1,7}([.,][0-9]{1,2})?$/D'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'image_url' => ['bail', 'nullable', 'string', 'max:500', /** Accept a local catalog image path or a valid HTTPS URL. */ function (string $attribute, string $value, Closure $fail): void {
                if (preg_match('~^/images/[a-zA-Z0-9_-]+\.(?:webp|jpg|png|jpeg)(?:#[a-z0-9-]+)?$~D', $value)) {
                    return;
                }
                if (! str_starts_with($value, 'https://') || filter_var($value, FILTER_VALIDATE_URL) === false) {
                    $fail('Unesi HTTPS adresu fotografije ili postojeću lokalnu putanju.');
                }
            }],
            'is_active' => ['required', 'boolean'],
            'revision' => [$this->routeIs('admin.products.update') ? 'required' : 'prohibited', 'integer', 'min:1'],
        ];
    }
}
