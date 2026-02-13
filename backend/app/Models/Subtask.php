<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    use HasFactory;

    protected $fillable = [
        'description', 'amount', 'type', 'transaction_date', 
        'user_id', 'batch_id', 'category_id',
        'card_id', 
        'title', 
        'is_completed'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}