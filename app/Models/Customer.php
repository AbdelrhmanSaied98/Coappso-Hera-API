<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Customer extends Authenticatable implements JWTSubject
{
    use HasFactory;
    protected $table = 'customers';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'image',
        'device_token',
        'flag',
        'verification_code',
        'isBlocked',
        'ban_times',
        'refresh_token',
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
    public function book_servies()
    {
        return $this->hasMany(Book::class,'customer_id');
    }
    public function favoirtes()
    {
        return $this->hasMany(Favoirte::class,'customer_id');
    }
    public function reviews()
    {
        return $this->hasMany(Review::class,'customer_id');
    }
    public function messages()
    {
        return $this->hasMany(Message::class,'customer_id');
    }
    public function employee_reviews()
    {
        return $this->hasMany(Employee_review::class,'customer_id');
    }

}
