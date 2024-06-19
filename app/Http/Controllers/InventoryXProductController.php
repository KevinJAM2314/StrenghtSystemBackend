<?php

namespace App\Http\Controllers;

use App\Models\InventoryXProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

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
        
        } catch (\Exception $e) {
            throw new \Exception(json_encode(['title' => Lang::get('messages.alerts.title.error'), 
            'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'Inventory'])
            ]));
        }
    }

    public function update(Request $request)
    {

        $inventoryXProduct = InventoryXProduct::where('inventory_id', 5)->where('product_id', $request->product_id)->first();
        if($inventoryXProduct){
            $inventoryXProduct->update([
                'quantity' => $request->quantity,
                'available' => $request->available,
            ]);
        } else {
            throw new \Exception(json_encode(['title' => Lang::get('messages.alerts.title.error'), 
            'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'Inventory'])
            ]));   
        }

    }
}
