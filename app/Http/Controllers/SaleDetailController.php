<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\InvoiceDetail;
use App\Models\InventoryXProduct;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Lang;

class SaleDetailController extends Controller
{
    public function store(Request $request, $invoiceId)
    {
        try{
            $request->validate([
                'quantity' => 'required|integer',
                'sale_id' => 'required',
                'inventory_x_products_id' => 'required|exists:inventory_x_products,id'
            ]);

            $inventoryXProduct = InventoryXProduct::findOrFail($request->inventory_x_products_id);

            if(!$inventoryXProduct->validateQuantity($request->quantity)){
                throw new \Exception(json_encode(['title' => Lang::get('messages.alerts.title.warning'), 
                'message' => Lang::get('messages.alerts.message.cancel', ['table' => 'Inventory'])
                ]));   
            }

            $saleDetail = SaleDetail::create([
                'total' => $this->calculateTotal($inventoryXProduct, $request->quantity),
                'quantity' => $request->quantity,
                'sale_id' => $request->sale_id,
                'inventory_x_products_id' => $request->inventory_x_products_id
            ]);
            
            $this->saveInvoiceDetail($saleDetail, $invoiceId);
            $inventoryXProduct->update([
                'quantity' => $inventoryXProduct->quantity - $saleDetail->quantity
            ]);

            return $saleDetail->total; 
        } catch (\Exception $e) {
            throw new \Exception(json_encode(['title' => Lang::get('messages.alerts.title.warning'), 
            'message' => Lang::get('messages.alerts.message.cancel', ['table' => 'Sale Detail'])
            ]));
        }
    }

    public function update(Request $request)
    {
        try{
            $request->validate([
                'id' => 'required|exists:sale_details,id',
                'quantity' => 'required|integer',
                'sale_id' => 'required|exists:sales,id',
                'inventory_x_products_id' => 'required|exists:inventory_x_products,id'
            ]);

            $saleDetail = SaleDetail::find($request->id);

            if (!$saleDetail) {
                throw new \Exception(json_encode(['title' => Lang::get('messages.alerts.title.error'), 
                'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'Sale Detail'])
                ])); 
            }

            $this->updateInventoryXProduct($saleDetail);

            $inventoryXProduct = InventoryXProduct::findOrFail($request->inventory_x_products_id);
            if(!$inventoryXProduct->validateQuantity($request->quantity)){
                throw new \Exception(json_encode(['title' => Lang::get('messages.alerts.title.warning'), 
                'message' => Lang::get('messages.alerts.message.cancel', ['table' => 'Inventory'])
                ]));   
            }

            $saleDetail-> update([
                'total' => $this->calculateTotal($inventoryXProduct, $request->quantity),
                'quantity' => $request->quantity,
                'sale_id' => $request->sale_id,
                'inventory_x_products_id' => $request->inventory_x_products_id
            ]);

            $inventoryXProduct->update([
                'quantity' => $inventoryXProduct->quantity - $saleDetail->quantity
            ]);
            
            return $saleDetail->total; 
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            
            $errorMessages = implode('*', $errors);

            throw new \Exception(json_encode(['title' => Lang::get('messages.alerts.title.error'), 
            'message' => Lang::get('messages.alerts.message.error', ['error' => $errorMessages])]));
        }
    }

    public function show()
    {
        $saleDetails = SaleDetail::where('sale_id', 1)->get();
        return $saleDetails;
    }

    public function destroy (Request $request)
    {
        $saleDetail = SaleDetail::find($request->id);
        if($saleDetail)
        {
            $this->updateInventoryXProduct($saleDetail);
            $saleDetail->delete();
            return; 
        }
        throw new \Exception(json_encode(['title' => Lang::get('messages.alerts.title.error'), 
        'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'Inventory'])
        ]));   
    }

    public function cancel (Request $request)
    {      
        $saleDetail = SaleDetail::find($request->id);
        if($saleDetail){
            $inventoryXProduct = InventoryXProduct::find($saleDetail->inventory_x_products_id);
            if($inventoryXProduct){
                $inventoryXProduct->update([
                    'quantity' => $inventoryXProduct->quantity + $saleDetail->quantity
                ]);
            }else{
                throw new \Exception(json_encode(['title' => Lang::get('messages.alerts.title.error'), 
                'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'Inventory'])
                ]));   
            }
        } else {
            throw new \Exception(json_encode(['title' => Lang::get('messages.alerts.title.error'), 
            'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'Sale Detail'])
            ]));      
        }
    }

    private function calculateTotal($inventoryXProduct, $quantity)
    {
        
        $product = Product::findOrFail($inventoryXProduct->id);

        return $product->calculateTotal($quantity);
    }

    private function updateInventoryXProduct($saleDetail)
    {
        $inventoryXProduct = InventoryXProduct::findOrFail($saleDetail->inventory_x_products_id);
        $quantityOld = $saleDetail->quantity;
        $inventoryXProduct->update([
            'quantity' => $inventoryXProduct->quantity + $quantityOld
        ]);
    }

    private function saveInvoiceDetail($saleDetail, $invoiceId)
    {
        $inventory = Inventory::find(1);   
        InvoiceDetail::create([
            'quantity' => $saleDetail->quantity,
            'total' => $saleDetail->total,
            'inventory_name' => $inventory->name,
            'invoice_id' => $invoiceId
        ]);
    }
}
