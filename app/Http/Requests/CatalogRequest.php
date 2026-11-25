<?php

namespace App\Http\Requests;

use App\Services\CategoryService;
use App\Support\Money;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class CatalogRequest extends FormRequest
{
    public function rules(): array
    {
        $price = ['bail', 'nullable', 'string', 'regex:/^[0-9]{1,7}([.,][0-9]{1,2})?$/D'];

        return [
            'category' => ['nullable', 'integer', 'min:1'],
            'q' => ['bail', 'nullable', 'string', 'max:100'],
            'min_price' => $price,
            'max_price' => $price,
            'stock' => ['nullable', 'in:available,sold_out'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $data = $validator->getData();

                if ($validator->errors()->isEmpty() && isset($data['min_price'], $data['max_price'])
                    && Money::fromDecimal($data['min_price']) > Money::fromDecimal($data['max_price'])) {
                    $validator->errors()->add('max_price', 'Najviša cena mora biti veća od ili jednaka najnižoj.');
                }
            },
        ];
    }

    protected function failedValidation(ValidatorContract $validator): void
    {
        $response = response()->view('catalog.index', [
            'filters' => array_filter($this->only(array_keys($this->rules())), is_string(...)),
            'categories' => app(CategoryService::class)->options(),
            'filterErrors' => $validator->errors()->all(),
        ], 422);

        throw new HttpResponseException($response);
    }
}
