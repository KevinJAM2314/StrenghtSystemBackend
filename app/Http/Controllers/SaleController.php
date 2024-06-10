<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Sale;
use App\Models\Person;
use App\Models\InventoryXProduct;
use App\Http\Controllers\SaleDetailController;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['saleDetails.inventoryXProducts.products'])->get();

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
                'sale_details' => 'required|array|min:1'
            ]);

            DB::beginTransaction();

            $sale = Sale::create([
                'person_id' => $request->person_id,
                'totalAmount' => 0
            ]);
            
            $total = $this->saleDetails($request, $sale->id);
            
            $sale->update([
                'totalAmount' => $total
            ]);

            DB::commit();
            return response()->json(['message' => 'Venta registrada correctamente'], 201); 
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->validator->errors()]);
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
                return response()->json(['error' => 'Venta nunca registrada'], 404);
            }

            DB::beginTransaction();

            $total = $this->saleDetails($request);
            $this->destroySaleDetails($request);

            $sale->update([
                'person_id' => $request->person_id,
                'totalAmount' => $total
            ]);

            DB::commit();
            return response()->json(['message' => 'Venta Actualizada correctamente']); 
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->validator->errors()]);
        }
    }

    public function destroy(Request $request)
    {
        if(Sale::find($request->id)){
            Sale::destroy($request->id);
            return response()->json(['message' => 'Venta eliminada con exito'], 204); 
        }
        return response()->json(['message' => 'Venta no encontrado']); 
    }

    private function saleDetails($request, $sale_id=null)
    {
        $saleDetailController = app(SaleDetailController::class);
        $total = 0;
        foreach ($request->sale_details as $detail)
        {
            $inventoryXPproduct = InventoryXProduct::where('product_id', $detail['product_id'])
                                                        ->where('inventory_id', 1)
                                                        ->first();
                                                
            $saleD = new Request([
                'id' => $detail['id'] ?? null,
                'quantity' => $detail['quantity'],
                'sale_id' => $sale_id ?? $request->id,
                'inventory_x_products_id' => $inventoryXPproduct->id
            ]);
            
            $total += $this->storeOrUpdateSaleDetails($saleDetailController, $saleD, $sale_id);   
        }
        return $total;
    }

    private function storeOrUpdateSaleDetails($saleDetailController, $saleD, $sale_id)
    {
        if($sale_id)
        {
            return $saleDetailController->store($saleD);   
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
        $saleDetailsOldArray = $saleDetailsOld->get()->toArray();

        // Convertir el array de objetos JSON a un array de arrays
        $saleDetailsNewArray = json_decode(json_encode($request->sale_details), true);

        // return response()->json(['saleDetailsOldArray' => $saleDetailsOldArray, 'saleDetailsNewArray' => $saleDetailsNewArray]); 
        // Buscar cuales detalles se eliminaron en el front para eliminarlos en el backend
        $destroyDetails = array_udiff($saleDetailsOldArray, $saleDetailsNewArray, $compareById);


        foreach ($destroyDetails as $detail)
        {
            $saleDetailController->destroy(new Request([
                'id' => $detail->id
            ]));
        }

    }
}
