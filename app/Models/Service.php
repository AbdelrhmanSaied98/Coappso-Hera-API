<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $table = 'services';

    protected $fillable = [
        'name',
        'price',
        'description',
        'is_offer',
        'duration',
        'new_price',
        'beauty_center_id',
        'category_id',
        'image',
        'durationOffer'
    ];
    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
    public function beauty_center()
    {
        return $this->belongsTo(Beauty_center::class,'beauty_center_id');
    }
    public function package()
    {
        return $this->hasOne(Package::class,'service_id');
    }
}
