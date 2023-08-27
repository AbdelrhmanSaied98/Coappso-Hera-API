<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Manger extends Authenticatable implements JWTSubject
{
    use HasFactory;
    protected $table = 'mangers';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'branch_id',
    ];

    protected $hidden = [
        'password',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class,'branch_id');
    }
}
