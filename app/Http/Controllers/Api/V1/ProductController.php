<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProductCreateRequest;
use App\Http\Requests\Api\V1\ProductUpdateRequest;
use App\Http\Requests\Api\V1\ApiProductFilterRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductController extends Controller
{
    /**
     * Передача параметров в query string
     * Например: /api/v1/products?name=ember&price_from=40
     *
     * Input format:
     * {
     *  'name': {string}
     *  'price_from': {numeric}
     *  'price_to: {numeric}
     *  'published': {bool}
     *  'deleted': {bool}
     *  'id_categories': Array[{integer}]
     *  'name_category': {string}
     * }
     */
    public function index(ApiProductFilterRequest $request)
    {
        $query = Product::query();

        $query->when(
            $request->validated('name'),
            fn(Builder $query, $value) => $query->where('name', 'like', '%'.$value.'%')
        );

        $query->when(
            $request->validated('price_from'),
            fn(Builder $query, $value) => $query->where('price', '>=', $value)
        );

        $query->when(
            $request->validated('price_to'),
            fn(Builder $query, $value) => $query->where('price', '<=', $value)
        );

        $query->when(
            $request->has('published'),
            fn(Builder $query) => $query->where('published', $request->validated('published')),
        );

        $query->when(
            $request->validated('deleted'),
            fn(Builder $query) => $query->withTrashed()
        );

        $query->when(
            $request->validated('id_categories'),
            fn(Builder $query, $value) => $query->whereHas(
                'categories',
                fn(Builder $query) => $query->whereIn('id', $value)
            )
        );

        $query->when(
            $request->validated('name_category'),
            fn(Builder $query, $value) => $query->whereHas(
                'categories',
                fn(Builder $query) => $query->where('name', 'like', '%'.$value.'%')
            )
        );

        return response()->json(new ProductCollection($query->get()));
    }

    /**
     * Input format:
     * {
     *  'name': {string}, required
     *  'price': {numeric}, required
     *  'published': {bool}, required
     *  'categories': Array[{integer}], required, size: min-2, max-10
     * }
     */
    public function store(ProductCreateRequest $request)
    {
        /** @var Product $product */
        $product = Product::query()->create($request->validated());
        $product->categories()->sync($request->input('categories'));
        $product->refresh();
        return response()->json(new ProductResource($product), 201);
    }

    public function show(Product $product)
    {
        return response()->json(new ProductResource($product));
    }

    /**
     * Input format:
     * {
     *  'name': {string}
     *  'price': {numeric}
     *  'published': {bool}
     *  'categories': Array[{integer}], size: min-2, max-10
     * }
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $product->updateOrFail($request->validated());
        if ($request->has('categories')) {
            $product->categories()->sync($request->input('categories'));
            $product->refresh();
        }
        return response()->json(new ProductResource($product));
    }

    public function destroy(Product $product)
    {
        $product->deleteOrFail();
        return response()->json(null, 204);
    }
}
