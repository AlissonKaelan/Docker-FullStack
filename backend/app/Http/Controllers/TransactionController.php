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

    public function balance() // Removi o Request $request pois não estamos usando dados de entrada
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Total de Entradas
        $income = $user->transactions()
            ->where('type', 'income')
            ->sum('amount');

        // Total de Saídas (CORREÇÃO AQUI: Troquei - por ->)
        $expense = $user->transactions()
            ->where('type', 'expense')
            ->sum('amount');

        // Cálculo Matemático
        $balance = $income - $expense;

        return response()->json([
            'income' => floatval($income), // Garante que seja número
            'expense' => floatval($expense),
            'balance' => floatval($balance)
        ]);
    }

    public function update(Request $request, Transaction $transaction)
    {
        // 1. SEGURANÇA (Authorization)
        // Verifica se a transação pertence ao usuário logado.
        // Se eu tentar editar a transação do vizinho, recebo erro 403 (Proibido).
        if ($transaction->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 2. VALIDAÇÃO (Reaproveitamos a mesma lógica do store)
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'transaction_date' => 'required|date'
        ]);

        // 3. ATUALIZAÇÃO
        // O método update() pega o array validado e troca os valores no banco
        $transaction->update($validated);

        return response()->json($transaction);
    }

    public function destroy(Transaction $transaction)
    {
        // 1. SEGURANÇA (Authorization)
        if ($transaction->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 2. DELEÇÃO
        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}