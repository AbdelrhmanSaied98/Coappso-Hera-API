<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;
    protected $table = 'packages';

    protected $fillable = [
        'service_id',
        'services'
    ];
    public function service()
    {
        return $this->belongsTo(Service::class,'service_id');
    }
}
