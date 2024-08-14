<?php

namespace App\Http\Controllers;

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
}
