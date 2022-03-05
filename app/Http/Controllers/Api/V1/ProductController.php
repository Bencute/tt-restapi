<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProductCreateRequest;
use App\Http\Requests\Api\V1\ProductUpdateRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return new ProductCollection(Product::all());
    }

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

    public function update(ProductUpdateRequest $request, Product $product)
    {
        $product->update($request->validated());
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
