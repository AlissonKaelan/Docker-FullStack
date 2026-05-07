<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTask extends Model
{
    protected $fillable = ['title', 'is_completed', 'is_recurring', 'user_id', 'workspace_id'];

    protected $casts = [
        'is_completed' => 'boolean',
        'is_recurring' => 'boolean',
    ];
}
