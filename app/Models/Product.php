<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';

    protected $fillable = [
        'name',
        'price',
        'description',
        'beauty_center_id',
        'category_id',
        'image'
    ];
    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
    public function beauty_center()
    {
        return $this->belongsTo(Beauty_center::class,'beauty_center_id');
    }

}
