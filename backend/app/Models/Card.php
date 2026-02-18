<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'description', 
        'column_id', 
        'order', 
        'percentage'
    ];

    public function column()
    {
        return $this->belongsTo(Column::class);
    }

    public function subtasks()
    {
        return $this->hasMany(Subtask::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}