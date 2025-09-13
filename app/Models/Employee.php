<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'position',
        'salary',
        'start_date',
        'address',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'salary' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    protected $dates = [
        'start_date',
        'created_at',
        'updated_at'
    ];
}