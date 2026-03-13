<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetCateRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return response()->json(['message' => 'Category created successfully'], 201);
    }

    public function find($cateId)
    {
        $cate = Category::find($cateId);
        if (! $cate) {
            abort(404, 'Category not found');
        }

        return $cate;
    }

    public function show($cateId)
    {
        $cate = $this->find($cateId);

        return response()->json(['data' => new CategoryResource($cate)], 200);
    }

    public function update(UpdateCategoryRequest $request, $cateId)
    {
        $this->find($cateId)->update($request->validated());

        return response()->json(['message' => 'Category updated successfully'], 200);
    }

    public function delete($cateId)
    {
        $this->find($cateId)->delete();

        return response()->json(['message' => 'Category deleted successfully'], 200);
    }

    public function index(GetCateRequest $request)
    {
        $cate = Category::search($request->search)->paginate(10);

        return CategoryResource::collection($cate);
    }
}
