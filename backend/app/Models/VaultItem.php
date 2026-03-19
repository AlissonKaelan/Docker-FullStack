<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VaultItem extends Model
{   
    public function vaultItems() { return $this->hasMany(VaultItem::class); }
    protected $fillable = ['user_id', 'title', 'content', 'type'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
