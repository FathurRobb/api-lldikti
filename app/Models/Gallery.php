<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'media',
        'type',
        'created_at',
        'updated_at'
    ];

    protected $hidden = ['category_id'];

    protected $with = ['category'];

    public function category()
    {
        return $this->belongsTo(GalleryCategory::class, 'category_id');
    }

}
