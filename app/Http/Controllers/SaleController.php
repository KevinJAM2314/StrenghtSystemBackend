<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Sale;
use App\Models\Person;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::all();

        return response()->json(['sales' => $sales]); 
    }

    public function create()
    {
        $clients = Person::where('type_person_id', 2)
        ->select('id', 'firstName', 'secondName', 'firstLastName', 'secondLastName')
        ->get();

        return response()->json(['clients' => $clients]);
    }
    
    public function show(Request $request)
    {
        $sale = Sale::where('id', $request->id)->get();

        return response()->json(['sale' => $sale]); 
    }

    public function store(Request $request)
    {
        try{
            $request->validate([
                'person_id' => 'required|exists:people,id',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()]);
        }

        

        $sale = Sale::create([
            'person_id' => $request->person_id,
        ]);

        return response()->json(['message' => 'Venta registrada correctamente'], 201); 
    }

    public function update(Request $request)
    {
        try{
            $request->validate([
                'person_id' => 'required|exists:people,id',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()]);
        }

        $sale = Sale::find($request->id);

        if (!$sale) {
            return response()->json(['error' => 'Venta nunca registrada'], 404);
        }

        $sale-> update([
            'person_id' => $request->person_id,
        ]);

        return response()->json(['message' => 'Venta Actualizada correctamente']); 
    }

    public function destroy(Request $request)
    {
        if(Sale::find($request->id)){
            Sale::destroy($request->id);
            return response()->json(['message' => 'Venta eliminada con exito'], 204); 
        }
        return response()->json(['message' => 'Venta no encontrado']); 
    }




       

}
