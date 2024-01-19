<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emp_attend extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'check_in',
        'check_out',
    ];

    // function getAtndn()
    // {
    //     return $this->hasMany('App\Models\User', 'id', 'user_id');
    // }
}
