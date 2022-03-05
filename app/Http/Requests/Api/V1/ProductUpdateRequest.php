<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['string'],
            'price' => ['numeric'],
            'published' => ['bool'],
            'categories' => ['array', 'min:2', 'max:10'],
            'categories.*' => ['integer', 'exists:App\Models\Category,id'],
        ];
    }
}
