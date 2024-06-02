<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
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
                'duration' => 'nullable|integer',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()]);
        }

        Category::create([
            'name' => $request->name,
            'duration' => $request->duration ?? null,
        ]);

        return response()->json(['message' => 'Categoria creada correctamente'], 201); 
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
