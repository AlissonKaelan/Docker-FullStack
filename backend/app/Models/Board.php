<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use HasFactory;

    // Campos que podem ser salvos no banco
    protected $fillable = ['name', 'user_id'];

    // Um Quadro pertence a um Usuário
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Um Quadro tem várias Colunas
    public function columns()
    {
        return $this->hasMany(Column::class)->orderBy('order');
    }
}