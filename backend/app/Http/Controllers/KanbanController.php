<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Column;
use Illuminate\Http\Request;

class KanbanController extends Controller
{
    // 1. Listar tudo (Para carregar a tela inicial)
    public function index()
    {
        // Busca as colunas ordenadas e JÁ TRAZ os cartões de cada uma, também ordenados
        $columns = Column::with(['cards' => function ($query) {
            $query->orderBy('order_index');
        }])
        ->orderBy('order_index')
        ->get();

        return response()->json($columns);
    }

    // 2. Mover/Atualizar um Cartão (Para o Drag & Drop)
    public function updateCard(Request $request, $cardId)
    {
        $card = Card::findOrFail($cardId);

        // Validação simples
        $request->validate([
            'column_id' => 'required|exists:columns,id',
            'order_index' => 'integer'
        ]);

        // Atualiza a coluna e a posição
        $card->update([
            'column_id' => $request->column_id,
            'order_index' => $request->order_index ?? $card->order_index
        ]);

        return response()->json(['message' => 'Cartão movido com sucesso', 'card' => $card]);
    }
    
    // 3. Criar novo cartão
    public function storeCard(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'column_id' => 'required|exists:columns,id'
        ]);

        $card = Card::create([
            'title' => $request->title,
            'column_id' => $request->column_id,
            'order_index' => 999 // Joga pro final da lista por padrão
        ]);

        return response()->json($card, 201);
    }
}