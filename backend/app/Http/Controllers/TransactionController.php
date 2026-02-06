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

        return $query->orderBy('transaction_date', 'desc')->get();
    }

    public function balance(Request $request)
    {
        // O saldo também precisa respeitar o filtro de data para mostrar "Saldo do Mês"
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
            'category_id' => 'nullable|exists:categories,id'
        ]);

        $installments = (int) $request->input('installments', 1);
        $totalAmount = $request->input('amount');
        $baseDate = Carbon::parse($request->input('transaction_date'));
        
        $batchId = $installments > 1 ? Str::uuid() : null;

        if ($installments > 1 && $request->type === 'expense') {
            // Calcula o valor da parcela (ex: 100 / 3 = 33.3333...)
            // Precisamos arredondar para 2 casas para não dar erro no banco
            $amountPerShare = round($totalAmount / $installments, 2);
            
            // Opcional: Ajuste de centavos na última parcela (ex: 33.33 + 33.33 + 33.34 = 100.00)
            // Se quiser simples, mantenha como está, mas pode dar diferença de centavos.
            
            for ($i = 0; $i < $installments; $i++) {
                $date = $baseDate->copy()->addMonthsNoOverflow($i);
                
                Transaction::create([
                    'description' => $request->description . " (" . ($i + 1) . "/$installments)",
                    'amount' => $amountPerShare,
                    'type' => $request->type,
                    'transaction_date' => $date,
                    'user_id' => Auth::id(),
                    'batch_id' => $batchId
                ]);
            }
            return response()->json(['message' => 'Compra parcelada registrada!'], 201);
        } else {
            $transaction = Transaction::create([
                'description' => $request->description,
                'amount' => $totalAmount,
                'type' => $request->type,
                'transaction_date' => $request->transaction_date,
                'user_id' => Auth::id(),
                'batch_id' => null,
                'category_id' => $request->category_id
            ]);
            return response()->json($transaction, 201);
        }
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);
        
        // Se o usuário pediu para atualizar TODAS as parcelas e existe um batch_id
        if ($request->boolean('update_all') && $transaction->batch_id) {
            
            // Atualiza todas que têm o mesmo batch_id
            // OBS: Não mudamos a data em lote para não encavalar tudo no mesmo mês
            Transaction::where('batch_id', $transaction->batch_id)
                ->where('user_id', Auth::id())
                ->update([
                    'description' => $request->description, // Cuidado: isso tira o (1/3) do nome se não tratar
                    'amount' => $request->amount,
                    'type' => $request->type,
                    'category_id' => $request->category_id
                ]);
                
            return response()->json(['message' => 'Lote atualizado']);
        }


        $transaction->update($request->all());
        return response()->json($transaction);
    }

    public function destroy(Request $request, $id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);

        // Se pediu para deletar tudo e tem grupo
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