<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    protected $table = 'branches';

    protected $fillable = [
        'beauty_center_id',
        'location',
        'address',
        'name'
    ];
    public function beauty_center()
    {
        return $this->belongsTo(Beauty_center::class,'beauty_center_id');
    }
    public function employees()
    {
        return $this->hasMany(Employee::class,'branch_id');
    }
    public function manger()
    {
        return $this->hasOne(Manger::class,'branch_id');
    }
}
