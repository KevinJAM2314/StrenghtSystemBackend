<?php

namespace App\Http\Controllers;

use App\Models\InventoryXProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryXProductController extends Controller
{
    public function store(Request $request)
    {
        try {
            InventoryXProduct::create([
                'quantity' => $request->quantity,
                'available' => $request->available,
                'product_id' => $request->product_id,
                'inventory_id' => 1
            ]);
            return response()->json(['message' => 'Relación inventario-producto creada correctamente']);
        } catch (\Exception $e) {
            throw new \Exception('Error al crear la relación inventario-producto:');
        }
    }

    public function update(Request $request)
    {
        try {
            $inventoryXProduct = InventoryXProduct::where('inventory_id', 1)->where('product_id', $request->product_id);
            $inventoryXProduct->update([
                'quantity' => $request->quantity,
                'available' => $request->available,
            ]);

            return response()->json(['message' => 'Relación inventario-producto creada correctamente']);
        } catch (\Exception $e) {
            throw new \Exception('Error al crear la relación inventario-producto:');
        }
    }
}
