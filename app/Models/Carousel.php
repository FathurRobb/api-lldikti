<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carousel extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'media',
        'desc',
        'have_button',
        'button_action',
        'created_at',
        'updated_at'
    ];
}
