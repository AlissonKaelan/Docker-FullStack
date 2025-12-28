<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Column extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'order_index'];

    // ADICIONE ESTA FUNÇÃO:
    public function cards()
    {
        // Uma coluna "Tem Muitos" cartões
        return $this->hasMany(Card::class);
    }

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