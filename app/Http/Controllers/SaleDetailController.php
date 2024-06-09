<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SaleDetailController extends Controller
{
    public function store(Request $request)
    {
        try{
            $request->validate([
                'quantity' => 'required|integer',
                'total' => 'required|numeric|between:100,999999',
                'sale_id' => 'required|exists:sales,id',
                'inventory_x_products_id' => 'required|exists:inventory_x_products,id'
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()]);
        }

        SaleDetail::create([
            'total' => $request->total,
            'quantity' => $request->quantity,
            'sale_id' => $request->sale_id,
            'inventory_x_products_id' => $request->inventory_x_products_id
        ]);
        
        return response()->json(['message' => 'Detalles Venta registrada correctamente'], 201); 
    }

    public function update(Request $request)
    {
        try{
            $request->validate([
                'quantity' => 'required|integer',
                'total' => 'required|numeric|between:100,999999',
                'sale_id' => 'required|exists:sales,id',
                'inventory_x_products_id' => 'required|exists:inventory_x_products,id'
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()]);
        }

        $saleDetail = SaleDetail::find($request->id);

        if (!$saleDetail) {
            return response()->json(['error' => 'Detalle Venta nunca registrado'], 404);
        }

        $saleDetail-> update([
            'total' => $request->total,
            'quantity' => $request->quantity,
            'sale_id' => $request->sale_id,
            'inventory_x_products_id' => $request->inventory_x_products_id
        ]);
        
        return response()->json(['message' => 'Detalles Venta actualizado correctamente'], 201); 
    }
}
