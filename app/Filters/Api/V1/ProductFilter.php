<?php


namespace App\Filters\Api\V1;


use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ProductFilter extends Filter
{
    protected ?string $nameFilter = null;

    public function getFilters(): array
    {
        return [
            'name' => self::FILTER_TYPE_LIKE,
            'price_from' => fn(Builder $query, $column, $value) => $query->where('price', '>=', $value),
            'price_to' => fn(Builder $query, $column, $value) => $query->where('price', '<=', $value),
            'published' => self::FILTER_TYPE_EXACT,
            'deleted' => fn(Builder $query, $column, $value) => $query->when(
                $value,
                fn(Builder $query) => $query->withTrashed()
            ),
            'id_categories' => fn(Builder $query, $column, $value) => $query->whereHas(
                'categories',
                fn(Builder $query) => $query->whereIn('id', $value)
            ),
            'name_category' => fn(Builder $query, $column, $value) => $query->whereHas(
                'categories',
                fn(Builder $query) => $query->where('name', 'like', '%'.$value.'%')
            ),
        ];
    }

    protected function validateRule(): array
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
