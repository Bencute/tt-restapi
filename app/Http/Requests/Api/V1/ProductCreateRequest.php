<?php

namespace App\Http\Requests\Api\V1;

class ProductCreateRequest extends ProductUpdateRequest
{
    public function rules()
    {
        // Объединяем правила и удаляем дубликаты
        return array_map(
                fn($item)=>array_unique($item),
                array_merge_recursive([
                        'name' => ['required'],
                        'price' => ['required'],
                        'published' => ['required'],
                        'categories' => ['required'],
                    ],
                    parent::rules()
                )
        );
    }
}
