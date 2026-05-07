<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        return Category::where('workspace_id', $request->workspace_id)->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'type' => 'required|in:income,expense',
            'color' => 'required|string'
        ]);

        $category = Category::create([
            'name' => $request->name,
            'type' => $request->type,
            'color' => $request->color,
            'workspace_id' => $request->workspace_id,
            'user_id' => $request->user()->id
        ]);

        return response()->json($category, 201);
    }

    public function destroy(Request $request, $id)
    {
        $category = Category::where('workspace_id', $request->workspace_id)->findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Categoria excluída']);
    }
}