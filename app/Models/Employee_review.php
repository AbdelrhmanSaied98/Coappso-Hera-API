<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee_review extends Model
{
    use HasFactory;
    protected $table = 'empolyee_reviews';

    protected $fillable = [
        'rate',
        'feedback',
        'customer_id',
        'employee_id',
    ];
    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
}
