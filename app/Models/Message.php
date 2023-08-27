<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $table = 'messages';

    protected $fillable = [
        'content_type',
        'content',
        'customer_id',
        'beauty_center_id',
        'sender_type'
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
