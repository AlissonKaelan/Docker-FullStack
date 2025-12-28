<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'board_id', 'order'];

    // Uma Coluna pertence a um Quadro
    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    // Uma Coluna tem várias Tarefas
    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('order');
    }
}