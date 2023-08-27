<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Beauty_center extends Authenticatable implements JWTSubject
{
    use HasFactory;
    protected $table = 'beauty_centers';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'image',
        'time_to',
        'time_from',
        'device_token',
        'off_day',
        'scheduler_duration',
        'offDates',
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
    public function advertisements()
    {
        return $this->hasMany(Advertisement::class,'beauty_center_id');
    }
    public function favoirtes()
    {
        return $this->hasMany(Favoirte::class,'beauty_center_id');
    }
    public function media()
    {
        return $this->hasMany(Media::class,'beauty_center_id');
    }
    public function products()
    {
        return $this->hasMany(Product::class,'beauty_center_id');
    }
    public function reviews()
    {
        return $this->hasMany(Review::class,'beauty_center_id');
    }
    public function services()
    {
        return $this->hasMany(Service::class,'beauty_center_id');
    }
    public function branches()
    {
        return $this->hasMany(Branch::class,'beauty_center_id');
    }
    public function messages()
    {
        return $this->hasMany(Message::class,'beauty_center_id');
    }

}
