<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::where('user_id', Auth::id())->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'type' => 'required|in:income,expense',
            'color' => 'required|string' // Ex: #ff0000
        ]);

        $category = Category::create([
            'name' => $request->name,
            'type' => $request->type,
            'color' => $request->color,
            'user_id' => Auth::id()
        ]);

        return response()->json($category, 201);
    }

    public function destroy($id)
    {
        $category = Category::where('user_id', Auth::id())->findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Categoria excluída']);
    }
}