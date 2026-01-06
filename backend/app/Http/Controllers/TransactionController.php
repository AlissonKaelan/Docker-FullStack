<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return response()->json(
            $user->transactions()->orderBy('transaction_date', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        // 1. VALIDAÇÃO (A "Porta de Aço")
        // Garante que o dado só entra se estiver perfeito
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01', // Evita valores negativos ou zero
            'type' => 'required|in:income,expense', // Só aceita esses dois valores
            'transaction_date' => 'required|date'
        ]);

        // 2. CRIAÇÃO SEGURA
        // Usamos o relacionamento para criar. O Laravel preenche o 'user_id' sozinho!
        // Não precisa fazer $data['user_id'] = auth()->id();
        $transaction = auth()->user()->transactions()->create($validated);

        return response()->json($transaction, 201);
    }
}