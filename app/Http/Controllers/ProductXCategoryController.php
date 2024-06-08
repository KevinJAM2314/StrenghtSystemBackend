<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductXCategory;
use Illuminate\Support\Facades\DB;

class ProductXCategoryController extends Controller
{
    public function store (Request $request)
    {
        try {
            ProductXCategory::create([
                'product_id' => $request->product_id,
                'category_id' => $request->category_id
            ]);
            return response()->json(['message' => 'Relación producto-categoría creada correctamente']);
        } catch (\Exception $e) {
            throw new \Exception('Error al crear la relación producto-categoría');
        }
    }
}
