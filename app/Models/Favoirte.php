<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favoirte extends Model
{
    use HasFactory;
    protected $table = 'favoirties';

    protected $fillable = [
        'customer_id',
        'beauty_center_id',
    ];
    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }
    public function beauty_center()
    {
        return $this->belongsTo(Beauty_center::class,'beauty_center_id');
    }
}
