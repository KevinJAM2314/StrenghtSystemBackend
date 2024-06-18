<?php

namespace App\Http\Controllers;
use App\Models\Person;
use App\Models\Contact;
use App\Models\Contact;
use App\Models\Geo;
use App\Models\Direction;
use App\Models\Sale;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Person::where('_person_id', 2)
                ->with(['contacts','directions.district.canton.province'])->select('id', 'firstName', 'secondName', 'firstLastName', 'secondLastName', 'gender', 'dateBirth')
                ->get();
        
        // Obtener la duración máxima desde las membresías
        $maxDuration = Category::max('duration');

        // Calcular la fecha mínima de creación de venta permitida
        $fechaMinima = Carbon::now('America/Costa_Rica')->subDays($maxDuration);

        $memberships = Sale::whereHas('saleDetailsM.inventoryXProductsM.productM.productXCategory.categoryM')
        ->with('saleDetailsM.inventoryXProductsM.productM.productXCategory.categoryM')->where('created_at', '>=', $fechaMinima)->get();

        $membershipsDays = $this->validateMembership($memberships);

        foreach($clients as $client){
            if (isset($membershipsDays[$client->id])) {
                $client->membership = $membershipsDays[$client->id];
            } else {
                $client->membership = 0;
            }
        }

        return response()->json(['clients' => $clients], 200);
    }

    public function create()
    {
        $Contacts = Contact::select('id', 'description')->get();

        $provinces = Geo::where('geo_id', null)->select('id', 'description')->get();

        return response()->json(['Contacts' => $Contacts, 'provinces' => $provinces]); 

    }

    public function show(Request $request)
    {
        $client = Person::where('id', $request->id)
                        ->where('_person_id', 2)
                        ->with(['contacts','directions.district.canton.province'])->first();
        if ($client->directions) {
            $district = Geo::where('id', $client->directions[0]->geo_id)->first();
            $canton = Geo::where('id', $district->geo_id)->first();
            $province = Geo::where('id', $canton->geo_id)->first();

            return response()->json(['client' => $client, 'province' => $province, 'canton' => $canton, 'district' => $district]);    
        }
        return response()->json(['client' => $client]);
    }

    public function store(Request $request)
    {
        try{
            $request->validate([
                'person.firstName' => 'required|string|max:20',
                'person.secondName' => 'nullable|max:20|string',
                'person.firstLastName' => 'required|string|max:20',
                'person.secondLastName' => 'nullable|string|max:20',
                'person.gender' => 'required|boolean',
                'person.dateBirth' => 'nullable|date|before:today',
                'contacts' => 'required|array',
                'contacts.*.value' => 'required|string|max:30',
                'contacts.*._contact_id' => 'required|exists:_contacts,id',
                'direction.description' => 'required|string|max:50',
                'direction.district_id' => 'required|exists:geos,id',
            ]);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            
            $errorMessages = implode('*', $errors);
            return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
            'message' => Lang::get('messages.alerts.message.error', ['error' => $errorMessages])], 201);
        }
        
        $client = Person::create([
            'firstName' => $request->person['firstName'],
            'secondName' => $request->person['secondName'],
            'firstLastName' => $request->person['firstLastName'],
            'secondLastName' => $request->person['secondLastName'],
            'gender' => $request->person['gender'],
            'dateBirth' => $request->person['dateBirth'],
            '_person_id' => 2
        ]);

        foreach ($request->contacts as $contact)
        {
            Contact::create([
                'value' => $contact['value'],
                '_contact_id' => $contact['_contact_id'],
                'person_id' => $client->id
            ]);
        }

        Direction::create([
            'description' => $request->direction['description'],
            'geo_id' => $request->direction['district_id'],
            'person_id' => $client->id
        ]);

        return response()->json(['title' => Lang::get('messages.alerts.title.success'), 
        'message' => Lang::get('messages.alerts.message.create', ['table' => 'Client'])], 201); 
    }

    public function update(Request $request)
    {
        try{
            $request->validate([
                'person.firstName' => 'required|string|max:20',
                'person.secondName' => 'nullable|max:20|string',
                'person.firstLastName' => 'required|string|max:20',
                'person.secondLastName' => 'nullable|string|max:20',
                'person.gender' => 'required|boolean',
                'person.dateBirth' => 'nullable|date|before:today',
                'contacts' => 'required|array',
                'contacts.*.value' => 'required|string|max:30',
                'contacts.*._contact_id' => 'required|exists:_contacts,id',
                'direction.description' => 'required|string|max:50',
                'direction.district_id' => 'required|exists:geos,id',
            ]);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            
            $errorMessages = implode('*', $errors);
            return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
            'message' => Lang::get('messages.alerts.message.error', ['error' => $errorMessages])]);
        }

        $client = Person::find($request->id);

        if (!$client) {
            return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
            'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'Client'])]);
        }

        $client->update([
            'firstName' => $request->person['firstName'],
            'secondName' => $request->person['secondName'],
            'firstLastName' => $request->person['firstLastName'],
            'secondLastName' => $request->person['secondLastName'],
            'gender' => $request->person['gender'],
            'dateBirth' => $request->person['dateBirth'],
            '_person_id' => 2
        ]);

        foreach ($request->contacts as $contact)
        {
            Contact::where('person_id', $client->id)
            ->where('_contact_id', $contact['_contact_id'])
            ->update(['value' => $contact['value']]);
        }

        // Buscar y actualizar la dirección existente
        Direction::where('person_id', $client->id)
        ->update([
              'description' => $request->direction['description'],
              'geo_id' => $request->direction['district_id']
          ]);

          return response()->json(['title' => Lang::get('messages.alerts.title.success'), 
          'message' => Lang::get('messages.alerts.message.update', ['table' => 'Client'])], 201);
    }

    public function destroy(Request $request)
    {      
        if(Person::find($request->id)){
            Person::destroy($request->id);
            return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
            'message' => Lang::get('messages.alerts.message.delete', ['table' => 'Client']), 201]);
        }
        return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
        'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'Client'])]);
    }
    
    private function validateMembership($memberships)
    {
        // Arreglo para almacenar resultados
        $results = [];

        // Obtener la fecha actual
        $currentDate = Carbon::now('America/Costa_Rica');
        foreach ($memberships as $membership) {
            $personId = $membership->person_id;

            $createdAt = Carbon::parse($membership->created_at);
            
            $totalDuration = 0;

            foreach ($membership->saleDetailsM as $saleDetail) {
                foreach ($saleDetail->inventoryXProductsM->productM->productXCategory as $productXCategory) {
                    if ($productXCategory->categoryM) {
                        $totalDuration += $productXCategory->categoryM->duration; 
                        
                        break;
                    }
                }
            }            

            if (isset($results[$personId])) {

                $results[$personId]['days_until_expiry'] += $totalDuration;
            } else {
                // Calcular fecha límite sumando la duración a la fecha de creación
                $endDate = $createdAt->copy()->addDays($totalDuration);
                
                // Calcular diferencia de días entre fecha límite y fecha actual
                $differenceInDays = $currentDate->diffInDays($endDate, false);
                
                // Si no existe, crear un nuevo resultado para este person_id
                $results[$personId] = max(0, $differenceInDays);
            }
        }

        return $results;
    }
}



