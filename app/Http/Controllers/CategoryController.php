<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;

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
        if(Category::find($request->id)){
            $categorySale = Category::whereHas('productXcategories', function ($query) use ($request) {
                $query->where('id', $request->id);
            })->with('productXcategories.product.inventoryXProducts.saleDetail')->first();

            if($categorySale){
                $category = $categorySale->productXcategories[0]->product->inventoryXProducts[0]->saleDetail->isNotEmpty();
            } else {
                $category = FALSE;
            }

            if(!$category){
                Category::destroy($request->id);
                return response()->json(['title' => Lang::get('messages.alerts.title.success'),
                'message' => Lang::get('messages.alerts.message.delete', ['table' => 'Category'])], 200);
            } else {
                return response()->json(['title' => Lang::get('messages.alerts.title.warning'),
                'message' => Lang::get('messages.alerts.message.cancel', ['table' => 'Category'])], 200);
            }
        }
        return response()->json(['title' => Lang::get('messages.alerts.title.error'),
        'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'Category'])], 404);
    }
}
