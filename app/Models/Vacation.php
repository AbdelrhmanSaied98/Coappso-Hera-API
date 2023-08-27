<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacation extends Model
{
    use HasFactory;
    protected $table = 'vacations';

    protected $fillable = [
        'date',
        'type',
        'employee_id'
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
}
