<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * Require a short, unique category name.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:80', 'unique:categories,name']];
    }
}
