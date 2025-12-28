<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'column_id', 'order_index'];

    // ADICIONE ESTA FUNÇÃO:
    public function column()
    {
        // Um cartão "Pertence A" uma coluna
        return $this->belongsTo(Column::class);
    }
}