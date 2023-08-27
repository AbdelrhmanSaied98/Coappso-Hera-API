<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;
    protected $table = 'advertisements';

    protected $fillable = [
        'image',
        'beauty_center_id'
    ];
    public function beauty_center()
    {
        return $this->belongsTo(Beauty_center::class,'beauty_center_id');
    }
}
