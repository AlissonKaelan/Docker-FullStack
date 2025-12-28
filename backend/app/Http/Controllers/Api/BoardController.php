<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Board; // <--- IMPORTANTE: Importar o Model

class BoardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Busca os quadros e carrega junto as colunas e tarefas
        $boards = Board::with('columns.tasks')->get();
        
        // Retorna o JSON para o Frontend
        return response()->json($boards);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Cria o quadro (fixando user_id 1 por enquanto)
        $board = Board::create([
            'name' => $request->name,
            'user_id' => 1, 
        ]);

        // Cria as colunas padrão
        $board->columns()->createMany([
            ['name' => 'A Fazer', 'order' => 1],
            ['name' => 'Em Progresso', 'order' => 2],
            ['name' => 'Concluído', 'order' => 3],
        ]);

        return response()->json($board->load('columns'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $board = Board::with('columns.tasks')->findOrFail($id);
        return response()->json($board);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Implementaremos depois
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Implementaremos depois
    }
}