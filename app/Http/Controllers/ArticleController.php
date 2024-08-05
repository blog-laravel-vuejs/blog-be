<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestCreateArticle;
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
}
