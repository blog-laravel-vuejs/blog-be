<?php

namespace App\Services;

use App\Http\Requests\RequestCreateCategory;
use App\Http\Requests\RequestUpdateCategory;
use App\Models\Category;
use App\Repositories\CategoryInterface;
use App\Repositories\CategoryRepository;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Traits\APIResponse;
use Illuminate\Http\Request;

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


    public function all(Request $request)
    {
        try {
            $orderBy = $request->typesort ?? 'id';
            switch ($orderBy) {
                case 'name':
                    $orderBy = 'name';
                    break;
                case 'new':
                    $orderBy = 'id';
                    break;

                default:
                    $orderBy = 'id';
                    break;
            }
            $orderDirection = $request->sortlatest ?? 'true';
            switch ($orderDirection) {
                case 'true':
                    $orderDirection = 'DESC';
                    break;

                default:
                    $orderDirection = 'ASC';
                    break;
            }

            $filter = (object) [
                'search' => $request->search ?? '',
                'orderBy' => $orderBy,
                'orderDirection' => $orderDirection,
            ];
            if (!(empty($request->paginate))) {
                $categories =CategoryRepository::searchCategory($filter)->paginate($request->paginate);
            } else {
                $categories = CategoryRepository::getCategory($filter)->get();
            }

            return $this->responseSuccessWithData($categories, 'Get category information successfully!');
        } catch (Throwable $e) {
            return $this->responseError($e->getMessage());
        }
    }

    public function update(RequestUpdateCategory $request,$id_category){
        DB::beginTransaction();
        try {
            $category = Category::find($id_category);
            if($request->hasFile('thumbnail')){
                // upload file
                $image = $request->file('thumbnail');
                $thumbnail = Cloudinary::upload($image->getRealPath(), [
                    'folder' => 'thumbnail/categories',
                    'resource_type' => 'auto'
                    ])->getSecurePath();
                // delete old file
                if($category->thumbnail){
                    $id_file=explode('.', implode('/', array_slice(explode('/', $category->thumbnail), 7)))[0];
                    Cloudinary::destroy($id_file);
                }
                // update data
                $data = array_merge($request->all(), ['thumbnail' => $thumbnail]);
                $category->update($data);
            }
            else{
                $request['thumbnail']=$category->thumbnail;
                $category->update($request->all());
            }
            
            DB::commit();

            return $this->responseSuccessWithData($category, 'Update category successfully !');
        } catch (Throwable $e) {
            DB::rollback();

            return $this->responseError($e->getMessage());
        }
    }

    
}
