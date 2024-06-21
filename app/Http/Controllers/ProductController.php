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
use Illuminate\Support\Facades\Lang;

class ProductController extends Controller
{

    public function index()
    {
        $products = Product::with(['productXCategory.category', 'inventoryXProducts'])->get();

        // Ocultar el atributo de la imagen de cada producto
        $products->each(function ($product) {
            $product->makeHidden(['image']);
        });

        return response()->json(['products' => $products], 200);
    }

    public function create()
    {
        $categories = Category::all();

        return response()->json(['categories' => $categories], 200);
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
        
            return response()->json(['title' => Lang::get('messages.alerts.title.success'), 
            'message' => Lang::get('messages.alerts.message.create', ['table' => 'Product'])], 201);

        } catch (ValidationException $e) {
            DB::rollBack();

            $errors = $e->validator->errors()->all();
            
            $errorMessages = implode('*', $errors);

            return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
            'message' => Lang::get('messages.alerts.message.error', ['error' => $errorMessages])], 400);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json(json_decode($e->getMessage()));
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
                'quantity' => 'required|integer|min:1',
                'available' => 'required|boolean'
            ]);

            DB::beginTransaction();
            
            $product = Product::find($request->id);

            if (!$product)
            {
                return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
                'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'Product'])], 404); 
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

            ///
            $requestIP = new Request([
                'quantity' => $request->quantity, 
                'available' => $request->available, 
                'product_id' => $product->id
            ]);

            $inventoryController = app(InventoryXProductController::class);
            $inventoryController->update($requestIP);

            DB::commit();
            return response()->json(['title' => Lang::get('messages.alerts.title.success'), 
            'message' => Lang::get('messages.alerts.message.update', ['table' => 'Product'])], 200);        
            
        } catch (ValidationException $e) {
            DB::rollBack();

            $errors = $e->validator->errors()->all();
            
            $errorMessages = implode('*', $errors);

            return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
            'message' => Lang::get('messages.alerts.message.error', ['error' => $errorMessages])], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(json_decode($e->getMessage()));
        }
    }

    public function destroy(Request $request)
    {   
        $product = Product::find($request->id);
        if($product){
            $productSale = Product::whereHas('inventoryXProducts', function ($query) use ($product) {
                $query->where('id', $product->id);
            })->with('inventoryXProducts.saleDetail')->get();

            $productS = $productSale[0]->inventoryXProducts[0]->saleDetail->isNotEmpty();

            if(!$productS){

                $this->destroyImage($product->image);
                $product->delete();
                return response()->json(['title' => Lang::get('messages.alerts.title.success'), 
                'message' => Lang::get('messages.alerts.message.delete', ['table' => 'Product'])], 200); 
            } else {
                return response()->json(['title' => Lang::get('messages.alerts.title.warning'), 
                'message' => Lang::get('messages.alerts.message.cancel', ['table' => 'Product'])], 200);
            }

        }
        return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
        'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'Category'])], 404); 
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
        if(File::exists($image_path))
        {
            unlink($image_path);
        }
    }
}
