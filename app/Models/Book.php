<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    protected $table = 'book';

    protected $fillable = [
        'employee_id',
        'date',
        'services',
        'time',
        'payment_status',
        'customer_id',
        'attendance_status',
        'make_flag'
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
