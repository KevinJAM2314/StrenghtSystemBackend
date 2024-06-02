<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductXCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProductController extends Controller
{

    public function index()
    {
        $products = Product::with(['productXCategory.category'])->get();

        // Ocultar el atributo de la imagen de cada producto
        $products->each(function ($product) {
            $product->makeHidden(['image']);
        });

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
                'category_id' => 'required|integer',
                'image' => 'required|image',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()]);
        }
        
        $image_name = $this->saveImage($request->image);

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $image_name,
        ]);

        ProductXCategory::create([
            'product_id' => $product->id,
            'category_id' => $request->category_id
        ]);

        return response()->json(['message' => 'Producto creado'], 201);
    }

    public function update(Request $request)
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

        $image_name = null;
        if($request->image)
        {
            $this->destroyImage($product->image);
            $image_name = $this->saveImage($request->image);
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $image_name ?? $product->image
        ]);

        $productXcategory = ProductXCategory::where('product_id', $product->id)->where('category_id', $request->category_id_old);
        $productXcategory->update([
            'category_id' => $request->category_id_new
        ]);

        return response()->json(['message' => 'Producto actualizado']);        
    }

    public function destroy(Request $request)
    {   
        $product = Product::find($request->id);
        if($product){

            $this->destroyImage($product->image);

            $product->delete();
            return response()->json(['message' => 'Producto eliminado con exito'], 204); 
        }
        return response()->json(['message' => 'Producto no encontrado']); 
    }

    private function saveImage($image)
    {
        $image_name = Str::uuid() . "." . $image->extension();
        $image->storeAs('public/products', $image_name);

        return $image_name;
    }

    private function destroyImage($image)
    {
        $image_path = public_path('storage/products/' . $image);
        if(File::exists($image_path)){
            unlink($image_path); // elimina archivo
        }
    }
}
