<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Finance extends Model
{
    use HasFactory;
    protected $table = 'finances';

    protected $fillable = [
        'month',
        'year',
        'base_salary',
        'pay_cut',
        'pay_cut_string',
        'final_salary',
        'employee_id'
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
}
