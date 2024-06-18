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

        return response()->json(['categories' => $categories]); 
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
            'message' => Lang::get('messages.alerts.message.error', ['error' => $errorMessages])], 201);
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

        return response()->json(['category' => $category]); 
    }

    public function update(Request $request)
    {
        try{
            $request->validate([
                'name' => 'required|string|max:20',
                'duration' => 'integer',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()]);
        }

        $category = Category::find($request->id);

        if (!$category) {
            return response()->json(['error' => 'Categoria no encontrada'], 404);
        }

        $category-> update([
            'name' => $request->name,
            'duration' => $request->duration ?? null,
        ]);

        return response()->json(['message' => 'Categoria Actualizada correctamente']); 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        if(Category::find($request->id)){
            Category::destroy($request->id);
            return response()->json(['message' => 'Categoria eliminada con exito'], 204); 
        }
        return response()->json(['message' => 'Categoria no encontrada']); 
    }
}
