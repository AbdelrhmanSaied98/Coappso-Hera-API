<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $table = 'employees';

    protected $fillable = [
        'name',
        'branch_id',
        'image',
        'title',
        'salary',
        'time_from',
        'time_to',
    ];
    public function book_servies()
    {
        return $this->hasMany(Book::class,'employee_id');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function employee_reviews()
    {
        return $this->hasMany(Employee_review::class,'employee_id');
    }
    public function attendances()
    {
        return $this->hasMany(Attendance::class,'employee_id');
    }
    public function finances()
    {
        return $this->hasMany(Finance::class,'employee_id');
    }
    public function vacations()
    {
        return $this->hasMany(Vacation::class,'employee_id');
    }
}
