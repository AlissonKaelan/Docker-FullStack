<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VaultItemController extends Controller
{   
    public function index(Request $request)
    {
        // O Laravel pega o usuário logado e busca APENAS os itens dele!
        $items = $request->user()->vaultItems()->get();

        // Retorna a lista no formato JSON (status 200 é o padrão)
        return response()->json($items);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:note,link'
        ]);

        // Criamos o item vinculado ao usuário autenticado
        $item = $request->user()->vaultItems()->create($validated);

        return response()->json($item, 201);
    }
}
