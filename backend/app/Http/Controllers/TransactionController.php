<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::where('user_id', Auth::id());

        // --- FILTRO DE DATA (Mês e Ano) ---
        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('transaction_date', $request->month)
                  ->whereYear('transaction_date', $request->year);
        } else {
            // Padrão: Se não enviar filtro, pega o mês atual
            $query->whereMonth('transaction_date', Carbon::now()->month)
                  ->whereYear('transaction_date', Carbon::now()->year);
        }

        // Traz a categoria e o card junto para mostrar cores/nomes se precisar
        return $query->with(['category', 'card'])->orderBy('transaction_date', 'desc')->get();
    }

    public function balance(Request $request)
    {
        $query = Transaction::where('user_id', Auth::id());

        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('transaction_date', $request->month)
                  ->whereYear('transaction_date', $request->year);
        } else {
            $query->whereMonth('transaction_date', Carbon::now()->month)
                  ->whereYear('transaction_date', Carbon::now()->year);
        }

        $transactions = $query->get();

        return response()->json([
            'income' => $transactions->where('type', 'income')->sum('amount'),
            'expense' => $transactions->where('type', 'expense')->sum('amount'),
            'balance' => $transactions->where('type', 'income')->sum('amount') - $transactions->where('type', 'expense')->sum('amount')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required',
            'amount' => 'required|numeric',
            'type' => 'required',
            'transaction_date' => 'required|date',
            'category_id' => 'nullable|exists:categories,id',
            'installments' => 'nullable|integer|min:1',
            'card_id' => 'nullable|exists:cards,id', // Validando
        ]);

        $installments = (int) $request->input('installments', 1);
        $totalAmount = $request->input('amount');
        $baseDate = Carbon::parse($request->input('transaction_date'));
        
        // Gera um ID único para o grupo de parcelas
        $batchId = $installments > 1 ? Str::uuid() : null;

        // --- LÓGICA DE PARCELAMENTO ---
        if ($installments > 1 && $request->type === 'expense') {
            
            $amountPerShare = round($totalAmount / $installments, 2);
            
            // Corrige diferença de centavos na primeira parcela
            $diff = round($totalAmount - ($amountPerShare * $installments), 2);
            
            for ($i = 0; $i < $installments; $i++) {
                $date = $baseDate->copy()->addMonthsNoOverflow($i);
                
                // Adiciona a diferença de centavos na primeira parcela
                $currentAmount = ($i == 0) ? $amountPerShare + $diff : $amountPerShare;

                Transaction::create([
                    'description' => $request->description . " (" . ($i + 1) . "/$installments)",
                    'amount' => $currentAmount,
                    'type' => $request->type,
                    'transaction_date' => $date,
                    'user_id' => Auth::id(),
                    'batch_id' => $batchId,
                    'category_id' => $request->category_id,
                    'card_id' => $request->card_id // <--- ADICIONADO AQUI
                ]);
            }
            return response()->json(['message' => 'Compra parcelada registrada!'], 201);
        
        } else {
            // --- COMPRA À VISTA / ÚNICA ---
            $transaction = Transaction::create([
                'description' => $request->description,
                'amount' => $totalAmount,
                'type' => $request->type,
                'transaction_date' => $request->transaction_date,
                'user_id' => Auth::id(), // Garante o ID do usuário
                'batch_id' => null,
                'category_id' => $request->category_id,
                'card_id' => $request->card_id // <--- ADICIONADO AQUI (Era isso que faltava!)
            ]);
            
            return response()->json($transaction, 201);
        }
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);
        
        // Se pediu para atualizar TODAS as parcelas
        if ($request->boolean('update_all') && $transaction->batch_id) {
            
            Transaction::where('batch_id', $transaction->batch_id)
                ->where('user_id', Auth::id())
                ->update([
                    // Atualiza campos comuns, inclusive Card e Categoria
                    'description' => $request->description, 
                    'amount' => $request->amount,
                    'type' => $request->type,
                    'category_id' => $request->category_id,
                    'card_id' => $request->card_id // <--- Adicionado para atualizar card em lote também
                ]);
                
            return response()->json(['message' => 'Lote atualizado']);
        }

        $transaction->update($request->all());
        return response()->json($transaction);
    }

    public function destroy(Request $request, $id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);

        if ($request->boolean('delete_all') && $transaction->batch_id) {
            Transaction::where('batch_id', $transaction->batch_id)
                ->where('user_id', Auth::id())
                ->delete();
            return response()->json(['message' => 'Todas as parcelas excluídas']);
        }

        $transaction->delete();
        return response()->json(['message' => 'Deleted']);
    }
}