<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Sale;
use App\Models\Person;
use App\Models\Product;
use App\Models\InventoryXProduct;
use App\Http\Controllers\SaleDetailController;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['saleDetails.inventoryXProducts.product'])->get();

        return response()->json(['sales' => $sales]); 
    }

    public function create()
    {
        $clients = Person::where('type_person_id', 2)
        ->select('id', 'firstName', 'secondName', 'firstLastName', 'secondLastName')
        ->get();

        $products = Product::whereHas('inventoryXProducts')->select('id', 'name', 'price', 'image')->with('inventoryXProducts')->get();

        $products->each(function ($product) {
            $product->makeHidden(['image']);
        });

        return response()->json(['clients' => $clients, 'products' => $products]);
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
                'sale_details' => 'required|array|min:1'
            ]);

            DB::beginTransaction();

            $sale = Sale::create([
                'person_id' => $request->person_id,
                'totalAmount' => 0
            ]);
            $invoiceId =  $this->saveInvoice($sale);
            $total = $this->saleDetails($request, $sale->id, $invoiceId);
            
            $sale->update([
                'totalAmount' => $total
            ]);

            $this->updateInvoice($invoiceId, $sale->totalAmount);
            DB::commit();
            return response()->json(['message' => 'Venta registrada correctamente'], 201); 
        } catch (ValidationException $e) {
            DB::rollBack();

            $errors = $e->validator->errors()->all();
            
            $errorMessages = implode('*', $errors);

            return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
            'message' => Lang::get('messages.alerts.message.error', ['error' => $errorMessages])]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(json_decode($e->getMessage()));
        }
    }

    public function update(Request $request)
    {
        try{
            $request->validate([
                'id' => 'required|integer|exists:sales,id',
                'person_id' => 'required|exists:people,id',
                'sale_details' => 'required|array|min:1'
            ]);

            $sale = Sale::find($request->id);

            if (!$sale) {
                return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
                'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'Sale'])]);
            }

            DB::beginTransaction();

            $total = $this->saleDetails($request);
            $this->destroySaleDetails($request);

            $sale->update([
                'person_id' => $request->person_id,
                'totalAmount' => $total
            ]); 

            DB::commit();
            return response()->json(['title' => Lang::get('messages.alerts.title.success'), 
            'message' => Lang::get('messages.alerts.message.update', ['table' => 'Sale'])]); 
        } catch (ValidationException $e) {
            DB::rollBack();
            
            $errors = $e->validator->errors()->all();
            
            $errorMessages = implode('*', $errors);

            return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
            'message' => Lang::get('messages.alerts.message.error', ['error' => $errorMessages])]);
        }
    }

    public function destroy(Request $request)
    {
        if(Sale::find($request->id)){
            Sale::destroy($request->id);
            return response()->json(['title' => Lang::get('messages.alerts.title.success'), 
            'message' => Lang::get('messages.alerts.message.delete', ['table' => 'Sale'])]); 
        }
        return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
        'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'Sale'])]); 
    }

    private function saleDetails($request, $sale_id=null, $invoiceId=null)
    {
        $saleDetailController = app(SaleDetailController::class);
        $total = 0;
        foreach ($request->sale_details as $detail)
        {
            $inventoryXPproduct = InventoryXProduct::where('product_id', $detail['product_id'])
                                    ->where('inventory_id', 1)->first();
                                                
            $saleD = new Request([
                'id' => $detail['id'] ?? null,
                'quantity' => $detail['quantity'],
                'sale_id' => $sale_id ?? $request->id,
                'inventory_x_products_id' => $inventoryXPproduct->id
            ]);
            
            $total += $this->storeOrUpdateSaleDetails($saleDetailController, $saleD, $sale_id, $invoiceId);   
        }
        return $total;
    }

    private function storeOrUpdateSaleDetails($saleDetailController, $saleD, $sale_id, $invoiceId=null)
    {
        if($sale_id)
        {
            return $saleDetailController->store($saleD, $invoiceId);   
        }

        return  $saleDetailController->update($saleD);   
    }

    private function destroySaleDetails($request)
    {
        $saleDetailController = app(SaleDetailController::class);
        $saleDetailsOld = $saleDetailController->show(new Request([
            'sale_id' => $request->id
        ]));

        // Definir la función de comparación personalizada
        $compareById = function($saleDetailsOld, $saleDetailsNew) {
            return $saleDetailsOld['id'] - $saleDetailsNew['id'];
        };

        // Convertir la colección de Eloquent a un array simple
        $saleDetailsOldArray = $saleDetailsOld->toArray();

        // Convertir el array de objetos JSON a un array de arrays
        $saleDetailsNewArray = $request->sale_details;


        // Buscar cuales detalles se eliminaron en el front para eliminarlos en el backend
        $destroyDetails = array_udiff($saleDetailsOldArray, $saleDetailsNewArray, $compareById);

        foreach ($destroyDetails as $detail)
        {
            $saleDetailController->destroy(new Request([
                'id' => $detail['id']
            ]));
        }
    }

    private function saveInvoice($sale)
    {   
        $person = Person::find($sale->person_id);
        $invoice = Invoice::create([
            'personName' => $person->fullname(),
            'totalAmount' => $sale->totalAmount,
            'sale_id' => $sale->id
        ]);
        return $invoice->id;
    }

    private function updateInvoice($invoiceId, $totalAmount)
    {
        $invoice = Invoice::find($invoiceId);
        $invoice->update([
            'totalAmount' => $totalAmount
        ]);
    }
}
