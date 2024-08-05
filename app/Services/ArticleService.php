<?php

namespace App\Services;

use App\Http\Requests\RequestCreateArticle;
use App\Http\Requests\RequestUpdateArticle;
use App\Models\Article;
use App\Models\Category;
use App\Repositories\ArticleInterface;
use App\Repositories\ArticleRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\InforDoctorRepository;
use App\Repositories\UserRepository;
use App\Traits\APIResponse;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class ArticleService
{
    use APIResponse;
    protected ArticleInterface $articleRepository;

    public function __construct(ArticleInterface $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }
    public function add(RequestCreateArticle $request)
    {
        DB::beginTransaction();
        try {
            $category = Category::find($request->id_category);
            if (empty($category)) {
                return $this->responseError('Category not found');
            }

            $image = $request->file('thumbnail');
            $thumbnail = Cloudinary::upload($image->getRealPath(), [
                'folder' => 'thumbnail/articles',
                'resource_type' => 'auto'
            ])->getSecurePath();
            $id_user = Auth::user()->id;
            $data = [
                'thumbnail' => $thumbnail,
                'is_accept' => false,
                'is_show' => true,
                'id_user' => $id_user,
            ];
            $data = array_merge($request->all(), $data);
            $article = Article::create($data);
            DB::commit();
            return $this->responseSuccessWithData($article, 'Add article successfully !');
        } catch (Throwable $e) {
            DB::rollback();
            return $this->responseError($e->getMessage());
        }
    }

   
}
