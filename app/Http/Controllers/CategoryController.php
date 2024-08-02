<?php

namespace App\Http\Controllers;


use App\Http\Requests\RequestCreateCategory;
use App\Models\Category;
use App\Services\CategoryService;
use App\Traits\APIResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use APIResponse;
    protected CategoryService $categoryService;
    public function __construct(CategoryService $categoryService){
        $this->categoryService = $categoryService;
    }
    public function add(RequestCreateCategory $request){
        return $this->categoryService->add($request);
    }
}
