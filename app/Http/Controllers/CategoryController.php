<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetCateRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create-category', ['only' => ['store']]);
        $this->middleware('permission:update-category', ['only' => ['update']]);
        $this->middleware('permission:view-category', ['only' => ['show']]);
        $this->middleware('permission:show-all-category', ['only' => ['index']]);
        $this->middleware('permission:delete-category', ['only' => ['delete']]);
    }

    public function store(StoreCategoryRequest $request) // admin
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

    public function show($cateId) // both
    {
        $cate = $this->find($cateId);

        return response()->json(['data' => new CategoryResource($cate)], 200);
    }

    public function update(UpdateCategoryRequest $request, $cateId) // admin
    {
        $this->find($cateId)->update($request->validated());

        return response()->json(['message' => 'Category updated successfully'], 200);
    }

    public function delete($cateId) // admin
    {
        $this->find($cateId)->delete();

        return response()->json(['message' => 'Category deleted successfully'], 200);
    }

    public function index(GetCateRequest $request) // both
    {
        $cate = Category::search($request->search)->paginate(10);

        return CategoryResource::collection($cate);
    }
}
