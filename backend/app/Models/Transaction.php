<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'amount',
        'type',
        'transaction_date',
        'category_id',
        'user_id',       
        'card_id',       
        'batch_id',
        'installments',
        'current_installment',
        'installment_id'
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    // (Opcional) Adicione a relação inversa se quiser
    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}