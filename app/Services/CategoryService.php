<?php

namespace App\Services;

use App\Http\Requests\RequestCreateCategory;
use App\Models\Category;
use App\Repositories\CategoryInterface;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Traits\APIResponse;
class CategoryService
{
    use APIResponse;
    protected CategoryInterface $categoryRepository;

    public function __construct(
        CategoryInterface $categoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
    }
    public function add(RequestCreateCategory $request)
    {
        DB::beginTransaction();
        try {
            if($request->hasFile('thumbnail')){
                $image = $request->file('thumbnail');
                $thumbnail = Cloudinary::upload($image->getRealPath(), [
                    'folder' => 'thumbnail/categories',
                    'resource_type' => 'auto'
                    ])->getSecurePath();
            }
            $data=array_merge($request->all(),['thumbnail'=>$thumbnail]);
            $category = Category::create($data);
            DB::commit();

            return $this->responseSuccessWithData($category, 'Add category successfully !');
        } catch (Throwable $e) {
            DB::rollback();

            return $this->responseError($e->getMessage());
        }
    }

    
}
