<?php

namespace App\Services;

use App\Http\Requests\RequestChangeIsShow;
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
                'search_number' => 0,
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
    public function myArticle(Request $request)
    {
        try {
            $user = UserRepository::findUserById(auth('user_api')->user()->id);
            if(empty($user)){
                return $this->responseError('User not found');
            }
            $orderBy = $request->typesort ?? 'articles.id';
            switch ($orderBy) {
                case 'title':
                    $orderBy = 'title';
                    break;
                case 'name':
                    $orderBy = 'categories.name';
                    break;
                case 'new':
                    $orderBy = 'id';
                    break;
                case 'search_number': // sắp xếp theo bài viết nổi bật
                    $orderBy = 'search_number_article';
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
                'name_category' => $request->name_category ?? '',
                'orderBy' => $orderBy,
                'orderDirection' => $orderDirection,
                'is_accept' => $request->is_accept ?? 'both',
                'is_show' => $request->is_show ?? 'both',
                'id_user' => auth('user_api')->user()->id,
            ];
            if (!(empty($request->paginate))) {
                $articles = $this->articleRepository->searchAll($filter)->paginate($request->paginate);
            } else {
                $articles = $this->articleRepository->searchAll($filter)->get();
            }

            return $this->responseSuccessWithData($articles, 'Get article information successfully!');
        } catch (Throwable $e) {
            return $this->responseError($e->getMessage());
        }
    }

    public function update(RequestUpdateArticle $request,$id){
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $article = Article::find($id);
            if (empty($article)) {
                return $this->responseError('Article not found');
            }
            if ($article->id_user != $user->id) {
                return $this->responseError('You do not have permission to update this article');
            }
            if($request->hasFile('thumbnail')){
                //upload new image
                $image = $request->file('thumbnail');
                $thumbnail = Cloudinary::upload($image->getRealPath(), [
                    'folder' => 'thumbnail/articles',
                    'resource_type' => 'auto'
                ])->getSecurePath();
                //delete old image
                if($article->thumbnail){
                    $id_file = explode('.', implode('/', array_slice(explode('/', $article->thumbnail), 7)))[0];
                    Cloudinary::destroy($id_file);
                }
                $data = array_merge($request->all(), ['thumbnail' => $thumbnail]);
                $article->update($data);
            }
            else{
                $request['thumbnail']=$article->thumbnail;
                $article->update($request->all());
            }
            DB::commit();
            return $this->responseSuccessWithData($article, 'Update article successfully !');
        } catch (Throwable $e) {
            DB::rollback();
            return $this->responseError($e->getMessage());
        }
    }
    public function changeIsShow(RequestChangeIsShow $request, $id_article)
    {
        DB::beginTransaction();
        try {
            $article = Article::find($id_article);
            if (empty($article)) {
                return $this->responseError('Article not found');
            }
            $user = Auth::user();
            if ($article->id_user != $user->id) {
                return $this->responseError('You do not have permission to change this article');
            }
            $article->update(['is_show'=>$request->is_show]);
            DB::commit();
            return $this->responseSuccessWithData($article, 'Change is show article successfully !');
        } catch (Throwable $e) {
            DB::rollback();
            return $this->responseError($e->getMessage());
        }
    }

    public function delete($id_article)
    {
        DB::beginTransaction();
        try {
            $article = Article::find($id_article);
            if (empty($article)) {
                return $this->responseError('Article not found');
            }
            $user = Auth::user();
            if ($article->id_user != $user->id) {
                return $this->responseError('You do not have permission to change this article');
            }
            if ($article->thumbnail) {
                $id_file = explode('.', implode('/', array_slice(explode('/', $article->thumbnail), 7)))[0];
                Cloudinary::destroy($id_file);
            }
            $article->delete();
            DB::commit();
            return $this->responseSuccess('Delete article successfully !');
        } catch (Throwable $e) {
            DB::rollback();

            return $this->responseError($e->getMessage());
        }
    }
    //admin
    public function getAll(Request $request){
        try {
            $orderBy = $request->typesort ?? 'articles.id';
            switch ($orderBy) {
                case 'title':
                    $orderBy = 'title';
                    break;
                case 'name':
                    $orderBy = 'categories.name';
                    break;
                case 'new':
                    $orderBy = 'id';
                    break;
                case 'search_number': // sắp xếp theo bài viết nổi bật
                    $orderBy = 'search_number_article';
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
                'name_category' => $request->name_category ?? '',
                'orderBy' => $orderBy,
                'orderDirection' => $orderDirection,
                'is_accept' => $request->is_accept ?? 'both',
                'is_show' => $request->is_show ?? 'both',
            ];
            if (!(empty($request->paginate))) {
                $articles = $this->articleRepository->searchAll($filter)->paginate($request->paginate);
            } else {
                $articles = $this->articleRepository->searchAll($filter)->get();
            }

            return $this->responseSuccessWithData($articles, 'Get article information successfully!');
        } catch (Throwable $e) {
            return $this->responseError($e->getMessage());
        }
    }
   
}
