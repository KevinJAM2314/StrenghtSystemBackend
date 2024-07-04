<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return response()->json(['categories' => $categories], 200);
    }

    public function store(Request $request)
    {
        try{
            $request->validate([
                'name' => 'required|string|max:20',
                'duration' => 'nullable|integer|min:1',
            ]);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();

            $errorMessages = implode('*', $errors);

            return response()->json(['title' => Lang::get('messages.alerts.title.error'),
            'message' => Lang::get('messages.alerts.message.error', ['error' => $errorMessages])], 400);
        }

        Category::create([
            'name' => $request->name,
            'duration' => $request->duration ?? null,
        ]);

        return response()->json(['title' => Lang::get('messages.alerts.title.success'),
        'message' => Lang::get('messages.alerts.message.create', ['table' => 'Category'])], 201);
    }

    public function show(Request $request)
    {
        $category = Category::where('id', $request->id)->select('id', 'name', 'duration')->get();

        return response()->json(['category' => $category], 200);
    }

    public function update(Request $request)
    {
        try{
            $request->validate([
                'name' => 'required|string|max:20',
                'duration' => 'integer',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 400);
        }

        $category = Category::find($request->id);

        if (!$category) {
            return response()->json(['title' => Lang::get('messages.alerts.title.error'),
            'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'Category'])], 404);
        }

        $category-> update([
            'name' => $request->name,
            'duration' => $request->duration ?? null,
        ]);

        return response()->json(['title' => Lang::get('messages.alerts.title.success'),
        'message' => Lang::get('messages.alerts.message.update', ['table' => 'Category'])], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $category = Category::find($request->id);
        if($category){
            $categorySale = Category::whereHas('productXcategories', function ($query) use ($request) {
                $query->where('id', $request->id);
            })->with('productXcategories.product.inventoryXProducts.saleDetail')->first();

            if($categorySale){
                $categoryisSale = $categorySale->productXcategories[0]->product->inventoryXProducts[0]->saleDetail->isNotEmpty();
            } else {
                $categoryisSale = FALSE;
            }

            if(!$categoryisSale){
                try {
                    DB::beginTransaction();
    
                    // Obtener los IDs de productos asociados a la categoría
                    $productsToDelete = Category::whereHas('productXcategories', function ($query) use ($request) {
                        $query->where('id', $request->id);
                    })->with('productXcategories')->get()
                    ->pluck('productXcategories.*.product_id')
                    ->flatten()
                    ->toArray();
    
                    $productController = app(ProductController::class);
    
                    foreach ($productsToDelete as $id) {
                        try {
                            $response = $productController->destroy(new Request(['id' => $id]));
    
                            if ($response->getStatusCode() !== 200) {
                                // Si la eliminación del producto falla, revertir la transacción
                                DB::rollBack();
                                return response()->json([
                                    'error' => "Failed to delete product with ID: $id",
                                ], 500);
                            }
                        } catch (\Exception $e) {
                            DB::rollBack();
                            return response()->json([
                                'error' => "Failed to delete product with ID: $id. Error: " . $e->getMessage(),
                            ], 500);
                        }
                    }
                    
                    // Eliminar la categoría
                    $category->delete();
    
                    DB::commit();
    
                    return response()->json([
                        'title' => Lang::get('messages.alerts.title.success'),
                        'message' => Lang::get('messages.alerts.message.delete', ['table' => 'Category']),
                    ], 200);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'error' => "Failed to delete category. Error: " . $e->getMessage(),
                    ], 500);
                }
            } else {
                return response()->json(['title' => Lang::get('messages.alerts.title.warning'),
                'message' => Lang::get('messages.alerts.message.cancel', ['table' => 'Category'])], 200);
            }
        }
        return response()->json(['title' => Lang::get('messages.alerts.title.error'),
        'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'Category'])], 404);
    }
}
