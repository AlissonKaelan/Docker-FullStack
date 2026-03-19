<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VaultItemController extends Controller
{
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
