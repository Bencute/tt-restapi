<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CategoryRequest;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(new CategoryCollection(Category::all()));
    }

    public function store(CategoryRequest $request)
    {
        $category = Category::query()->create($request->validated());
        return response()->json(new CategoryResource($category), 201);
    }

    public function show(Category $category)
    {
        return response()->json(new CategoryResource($category));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->updateOrFail($request->validated());
        return response()->json(new CategoryResource($category));
    }

    public function destroy(Category $category)
    {
        abort_if($category->products()->count(), 400, 'Category not empty');
        $category->deleteOrFail();
        return response()->json(null, 204);
    }
}
