<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductXCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{

    public function index()
    {
        $products = Product::with(['productXCategory.category'])->get();

        return response()->json(['products' => $products]);
    }

    public function create()
    {
        $categories = Category::all();

        return response()->json(['categories' => $categories]);
    }

    public function store(Request $request)
    {
        try{
            $request->validate([
                'name' => 'required|string|max:20',
                'description' => 'required|string|max:100',
                'price' => 'required|numeric|between:100,99999',
                'category_id' => 'required|integer'
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()]);
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
        ]);

        ProductXCategory::create([
            'product_id' => $product->id,
            'category_id' => $request->category_id
        ]);

        return response()->json(['message' => 'Producto creado']);
    }

    public function update(Request $request, Product $product)
    {
        try{
            $request->validate([
                'name' => 'required|string|max:20',
                'description' => 'required|string|max:100',
                'price' => 'required|numeric|between:100,99999',
                'category_id_new' => 'required|integer',
                'category_id_old' => 'required|integer',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()]);
        }

        $product = Product::find($request->id);

        if (!$product) {
            return response()->json(['error' => 'Product no encontrado'], 404);
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price
        ]);

        $productXcategory = ProductXCategory::where('product_id', $product->id)->where('category_id', $request->category_id_old);
        $productXcategory->update([
            'category_id' => $request->category_id_new
        ]);

        return response()->json(['message' => 'Producto actualizado']);        
    }

    public function destroy(Request $request)
    {
        if(Product::find($request->id)){
            Product::destroy($request->id);
            return response()->json(['message' => 'Producto eliminado con exito']); 
        }
        return response()->json(['message' => 'Producto no encontrado']); 
    }
}
