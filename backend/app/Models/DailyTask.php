<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTask extends Model
{
    protected $fillable = ['title', 'is_completed', 'user_id'];

    protected $casts = [
        'is_completed' => 'boolean',
        'is_recurring' => 'boolean',
    ];
}
