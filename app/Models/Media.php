<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;
    protected $table = 'medias';

    protected $fillable = [
        'file',
        'beauty_center_id',
    ];
    public function beauty_center()
    {
        return $this->belongsTo(Category::class,'beauty_center_id');
    }
}
