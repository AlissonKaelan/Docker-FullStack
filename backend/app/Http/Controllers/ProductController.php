<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index() {
        // Retorna JSON para o Front-end
        return response()->json(Product::all());
    }

    public function store(Request $request) {
        $product = Product::create($request->all());
        return response()->json($product, 201);
    }
}
