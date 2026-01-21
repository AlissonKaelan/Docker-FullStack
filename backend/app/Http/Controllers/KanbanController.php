<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Column;
use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KanbanController extends Controller
{
    public function index()
    {
        // CORREÇÃO 1: Filtra pelo usuário logado e ordena por 'order'
        $columns = Column::where('user_id', Auth::id())
            ->with(['cards.subtasks' => function ($q) {
                $q->orderBy('id');
            }])
            ->with(['cards' => function ($query) {
                $query->orderBy('order'); // <--- Mudado de order_index para order
            }])
            ->orderBy('order') // <--- Mudado de order_index para order
            ->get();

        return response()->json($columns);
    }

    public function storeColumn(Request $request) 
    {
        $request->validate(['title' => 'required']);
        $slug = \Illuminate\Support\Str::slug($request->title);
        
        // CORREÇÃO 2: Pega o max 'order' apenas deste usuário
        $maxOrder = Column::where('user_id', Auth::id())->max('order');
        
        $column = Column::create([
            'title' => $request->title, 
            'slug' => $slug, 
            'order' => $maxOrder + 1, 
            'user_id' => Auth::id()   
        ]);
        
        return response()->json($column);
    }

    public function storeCard(Request $request)
    {
        $card = Card::create([
            'title' => $request->title,
            'column_id' => $request->column_id,
            'order' => 999
        ]);
        return response()->json($card, 201);
    }

    // --- LÓGICA DE MOVIMENTAÇÃO (Mantida a sua, apenas ajustado nomes se necessário) ---
    public function updateCard(Request $request, $cardId)
    {
        $card = Card::findOrFail($cardId);
        $data = $request->all();

        // Se estiver atualizando a ordem via Drag and Drop
        if (isset($data['order'])) {
             $card->order = $data['order'];
        }

        // CENÁRIO 1: ARRASTOU (Mudança de Coluna)
        if (isset($data['column_id']) && $data['column_id'] != $card->column_id) {
            $targetColumn = Column::find($data['column_id']);
            
            if ($targetColumn) {
                // > Para DOING (Em Progresso)
                if ($targetColumn->slug === 'doing') {
                    if ($card->percentage == 0) {
                        $data['percentage'] = 10;
                    }
                }
                // > Para DONE (Concluído)
                elseif ($targetColumn->slug === 'done') {
                    $data['percentage'] = 100;
                    $card->subtasks()->update(['is_completed' => true]);
                }
                // > Para TODO (A Fazer)
                elseif ($targetColumn->slug === 'todo') {
                    $data['percentage'] = 0;
                }
            }
        }
        
        // CENÁRIO 2: SLIDER (Mudança de Porcentagem)
        elseif (isset($data['percentage'])) {
            $percentage = intval($data['percentage']);
            
            if ($percentage === 0) {
                $targetSlug = 'todo';
            } elseif ($percentage === 100) {
                $targetSlug = 'done';
                $card->subtasks()->update(['is_completed' => true]);
            } else {
                $targetSlug = 'doing';
            }

            if (isset($targetSlug)) {
                // Busca a coluna alvo APENAS DO USUÁRIO ATUAL
                $autoCol = Column::where('user_id', Auth::id())
                                 ->where('slug', $targetSlug)
                                 ->first();
                                 
                if ($autoCol && $card->column_id !== $autoCol->id) {
                    $data['column_id'] = $autoCol->id;
                }
            }
        }

        $card->update($data);

        return response()->json(['message' => 'Atualizado', 'card' => $card]);
    }

    public function storeSubtask(Request $request)
    {
        $subtask = Subtask::create([
            'card_id' => $request->card_id,
            'title' => $request->title
        ]);
        return response()->json($subtask);
    }

    public function updateSubtask(Request $request, $id)
    {
        $subtask = Subtask::findOrFail($id);
        $subtask->is_completed = $request->boolean('is_completed');
        $subtask->save();
        
        return response()->json($subtask);
    }
    public function deleteCard($id)
    {
        // Garante que o card pertence a uma coluna do usuário logado (Segurança)
        $card = Card::whereHas('column', function($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($id);

        $card->delete();
        
        return response()->json(['message' => 'Tarefa deletada']);
    }
    public function deleteColumn($id)
    {
        $column = Column::where('user_id', Auth::id())->findOrFail($id);
        $column->delete();
        return response()->json(['message' => 'Deletada']);
    }
}