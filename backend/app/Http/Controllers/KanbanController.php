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
        $card = Card::with('column')->findOrFail($cardId);
        $data = $request->all();

        // Se estiver atualizando a ordem via Drag and Drop
        if (isset($data['order'])) {
             $card->order = $data['order'];
        }

        // CENÁRIO 1: ARRASTOUO PARA OUTRA COLUNA (Mudança de Coluna)
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
        
        // CENÁRIO 2: SLIDER OU EDIÇÃO DE TEXTO (Mudança de Porcentagem/Dados)
        elseif (isset($data['percentage'])) {
            $percentage = intval($data['percentage']);
            
            // Define o alvo baseado na porcentagem
            if ($percentage === 0) {
                $targetSlug = 'todo';
            } elseif ($percentage === 100) {
                $targetSlug = 'done';
                $card->subtasks()->update(['is_completed' => true]);
            } else {
                $targetSlug = 'doing';
            }

            // Verifica se a coluna atual é uma das "Padrão"
            $isStandardColumn = in_array($card->column->slug, ['todo', 'doing', 'done']);

            // Se a tarefa está em uma COLUNA PERSONALIZADA (ex: "Ideias", "Revisão")
            // E a porcentagem é menor que 100, NÃO movemos ela automaticamente.
            // Só movemos se for 100% (para Concluído), pois isso faz sentido globalmente.
            if (!$isStandardColumn && $percentage < 100) {
                unset($targetSlug); // Cancela a mudança de coluna
            }
            if (isset($targetSlug)) {
                // Busca a coluna alvo APENAS DO USUÁRIO ATUAL
                $autoCol = Column::where('user_id', Auth::id())
                                ->where('slug', $targetSlug)
                                ->first();
                                 
                // Só muda a coluna se a coluna alvo existir e for diferente da atual
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
        // Validar é importante para evitar erros de SQL brutos
        $request->validate([
            'card_id' => 'required|exists:cards,id',
            'title' => 'required|string'
        ]);

        $subtask = Subtask::create([
            'card_id' => $request->card_id,
            'title' => $request->title,
            // 'is_completed'
        ]);
        
        return response()->json($subtask);
    }

    // --- ATUALIZAR (Permitir mudar status E título) ---
    public function updateSubtask(Request $request, $id)
    {
        $subtask = Subtask::findOrFail($id);

        if ($request->has('is_completed')) {
            $subtask->is_completed = $request->boolean('is_completed');
        }

        if ($request->has('title')) {
            $subtask->title = $request->title;
        }

        $subtask->save();
        
        return response()->json($subtask);
    }

    // --- NOVO MÉTODO: DELETAR SUBTAREFA ---
    public function deleteSubtask($id)
    {
        $subtask = Subtask::findOrFail($id);
        $subtask->delete();
        return response()->json(['message' => 'Subtarefa deletada']);
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