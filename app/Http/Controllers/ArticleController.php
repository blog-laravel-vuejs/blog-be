<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestChangeIsAccept;
use App\Http\Requests\RequestChangeIsShow;
use App\Http\Requests\RequestCreateArticle;
use App\Http\Requests\RequestUpdateArticle;
use App\Services\ArticleService;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
class ArticleController extends Controller
{
    protected ArticleService $articleService;
    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }
    public function add(RequestCreateArticle $request)
    {
        return $this->articleService->add($request);
    }
    public function myArticle(Request $request){
        return $this->articleService->myArticle($request);
    }
    public function update(RequestUpdateArticle $request, $id){
        return $this->articleService->update($request, $id);
    }
    public function changeIsShow(RequestChangeIsShow $request, $id_article){
        return $this->articleService->changeIsShow($request, $id_article);
    }
    public function delete($id_article)
    {
        return $this->articleService->delete($id_article);
    }
    public function getAll(Request $request)
    {
        return $this->articleService->getAll($request);
    }
    public function changeIsAccept(RequestChangeIsAccept $request, $id_article){
        return $this->articleService->changeIsAccept($request, $id_article);
    }
    public function articleHome(Request $request)
    {
        return $this->articleService->articleHome($request);
    }
    public function detail(Request $request,$id_article)
    {
        return $this->articleService->detail($request,$id_article);
    }

}
