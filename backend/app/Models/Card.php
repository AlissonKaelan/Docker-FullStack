<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    // Adicionado 'percentage'
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

    // NOVO: Relacionamento com Subtarefas
    public function subtasks()
    {
        return $this->hasMany(Subtask::class);
    }
}