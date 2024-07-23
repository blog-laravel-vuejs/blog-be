<?php

namespace App\Repositories;

use App\Models\Article;
use Illuminate\Support\Facades\DB;
use Throwable;

class ArticleRepository extends BaseRepository implements ArticleInterface
{
    public function getModel()
    {
        return Article::class;
    }

    public static function getArticle($filter)
    {
        $filter = (object) $filter;
        $data = (new self)->model
            ->when(!empty($filter->id_category), function ($q) use ($filter) {
                $q->where('id_category', $filter->id_category);
            })
            ->when(!empty($filter->list_id), function ($q) use ($filter) {
                $q->whereIn('id_category', $filter->list_id);
            });

        return $data;
    }

    public static function updateArticle($result, $data)
    {
        DB::beginTransaction();
        try {
            $result->update($data);
            DB::commit();

            return $result;
        } catch (Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }

    public static function createArticle($data)
    {
        DB::beginTransaction();
        try {
            $new = (new self)->model->create($data);
            DB::commit();

            return $new;
        } catch (Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }

    public static function findById($id)
    {
        return (new self)->model->find($id);
    }

    

    
}
