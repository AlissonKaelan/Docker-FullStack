<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Card;
use App\Models\Transaction;

class CardController extends Controller
{
    /**
     * Cria um novo Card (Tarefa)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'column_id' => 'required|exists:columns,id',
            'description' => 'nullable|string',
            'order' => 'nullable|integer'
        ]);

        // Define a ordem automaticamente se não for enviada (joga pro final)
        if (!isset($validated['order'])) {
            $validated['order'] = Card::where('column_id', $request->column_id)->max('order') + 1;
        }

        $card = Card::create($validated);

        return response()->json($card, 201);
    }

    /**
     * Atualiza um Card (Mover de coluna, editar título, etc)
     */
    public function update(Request $request, $id)
    {
        $card = Card::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'column_id' => 'sometimes|required|exists:columns,id',
            'order' => 'nullable|integer',
            'percentage' => 'nullable|integer|min:0|max:100' // Para o slider de progresso
        ]);

        $card->update($validated);

        return response()->json($card);
    }

    /**
     * Apaga um Card
     */
    public function destroy($id)
    {
        $card = Card::findOrFail($id);
        $card->delete();
        return response()->json(['message' => 'Card deleted']);
    }

    /**
     * --- NOVA FUNCIONALIDADE FINANCEIRA ---
     * Lista os custos vinculados a este card
     */
    public function transactions($id)
    {
        $card = Card::findOrFail($id);

        // Retorna as transações ordenadas por data
        // Usa 'with' para trazer a cor da categoria junto
        return $card->transactions()
                    ->with('category')
                    ->orderBy('transaction_date', 'desc')
                    ->get();
    }
}