<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ApiProductFilterRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'string',
            'price_from' => 'numeric',
            'price_to' => 'numeric',
            'published' => 'bool',
            'deleted' => 'bool',
            'id_categories' => 'array',
            'id_categories.*' => 'integer',
            'name_category' => 'string',
        ];
    }
}
