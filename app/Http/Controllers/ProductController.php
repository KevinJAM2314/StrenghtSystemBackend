<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductXCategory;
use App\Http\Controllers\InventoryXProductController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
                'quantity' => 'required|integer|min:1',
                'available' => 'required|boolean'
            ]);

            // Iniciar una transacción
            DB::beginTransaction();

            
            $image_name = $this->saveImage($request->image);

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'image' => $image_name,
            ]);

            ////
            $requestPC = new Request([
                'product_id' => $product->id, 
                'category_id' => $request->category_id
            ]);

            $productXCategoryController = app(ProductXCategoryController::class);
            $productXCategoryController->store($requestPC);

            ///
            $requestIP = new Request([
                'quantity' => $request->quantity, 
                'available' => $request->available, 
                'product_id' => $product->id
            ]);

            $inventoryController = app(InventoryXProductController::class);
            $inventoryController->store($requestIP);

            // Commit si todas las operaciones fueron exitosas
            DB::commit();
        
            return response()->json(['message' => 'Producto creado', $product], 201);

        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['error' => 'Error al crear el producto: ' . $e->getMessage()], 500);
        }

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
            return response()->json(['errors' => $e->validator->errors()], 422);
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

        return response()->json(['message' => 'Producto actualizado'],200);        
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
            unlink($image_path);
        }
    }
}
