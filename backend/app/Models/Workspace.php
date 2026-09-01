<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Define que um Workspace tem muitos Usuários
    public function users(): BelongsToMany
    {
        // O segundo parâmetro força o nome da tabela
        return $this->belongsToMany(User::class, 'workspace_user')->withPivot('role')->withTimestamps();
    }

    public function columns()
    {
        return $this->hasMany(Column::class);
    }
}