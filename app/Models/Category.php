<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'description_category',
        'thumbnail',
        'search_number',
    ];

    public function articles()
    {
        return $this->hasMany(Article::class);
    }  
}
