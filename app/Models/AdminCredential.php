<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminCredential extends Model
{
    protected $fillable = [
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}
