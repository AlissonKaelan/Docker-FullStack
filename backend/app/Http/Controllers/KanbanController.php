<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Column;
use App\Models\Subtask;
use Illuminate\Http\Request;

class KanbanController extends Controller
{
    public function index()
    {
        $columns = Column::with(['cards.subtasks' => function ($q) {
            $q->orderBy('id');
        }])->with(['cards' => function ($query) {
            $query->orderBy('order_index');
        }])
        ->orderBy('order_index')
        ->get();

        return response()->json($columns);
    }

    public function storeColumn(Request $request) 
    {
        $request->validate(['title' => 'required']);
        $slug = \Illuminate\Support\Str::slug($request->title);
        $maxOrder = Column::max('order_index');
        $column = Column::create([
            'title' => $request->title, 'slug' => $slug, 'order_index' => $maxOrder + 1
        ]);
        return response()->json($column);
    }

    public function storeCard(Request $request)
    {
        $card = Card::create([
            'title' => $request->title,
            'column_id' => $request->column_id,
            'order_index' => 999
        ]);
        return response()->json($card, 201);
    }

    // --- CORREÇÃO DA LÓGICA DE MOVIMENTAÇÃO ---
    public function updateCard(Request $request, $cardId)
    {
        $card = Card::findOrFail($cardId);
        $data = $request->all();

        // CENÁRIO 1: ARRASTOU (Mudança de Coluna)
        if (isset($data['column_id']) && $data['column_id'] != $card->column_id) {
            $targetColumn = Column::find($data['column_id']);
            
            if ($targetColumn) {
                // > Para DOING (Em Progresso)
                if ($targetColumn->slug === 'doing') {
                    // Só muda a porcentagem para 10 se estava zerada.
                    // JAMAIS toca nas subtarefas aqui.
                    if ($card->percentage == 0) {
                        $data['percentage'] = 10;
                    }
                }
                // > Para DONE (Concluído)
                elseif ($targetColumn->slug === 'done') {
                    $data['percentage'] = 100;
                    // Aqui sim, conclui tudo
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
                // Slider em 100% = Conclui subtarefas
                $card->subtasks()->update(['is_completed' => true]);
            } else {
                $targetSlug = 'doing';
            }

            if (isset($targetSlug)) {
                $autoCol = Column::where('slug', $targetSlug)->first();
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

    // --- CORREÇÃO DO CHECKBOX (UPDATE EXPLÍCITO) ---
    public function updateSubtask(Request $request, $id)
    {
        $subtask = Subtask::findOrFail($id);
        
        // Recebe true ou false diretamente do frontend
        $subtask->is_completed = $request->boolean('is_completed');
        $subtask->save();
        
        return response()->json($subtask);
    }
    
    public function deleteColumn($id)
    {
        $column = Column::findOrFail($id);
        $column->delete();
        return response()->json(['message' => 'Deletada']);
    }
}