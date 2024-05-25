<?php

namespace App\Http\Controllers;
use App\Models\Person;
use App\Models\Contact;
use App\Models\TypeContact;
use App\Models\Geo;
use App\Models\Direction;
use Illuminate\Validation\ValidationException;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Person::where('type_person_id', 2)
                ->with(['contacts' => function ($query) {
                    $query->where('type_contact_id', 1);
                }])
                ->get();
        
        $typeContacts = TypeContact::select('id', 'description')->get();

        return response()->json(['clients' => $clients, 'typeContacts' => $typeContacts]);
    }

    public function create()
    {
        $typeContacts = TypeContact::select('id', 'description')->get();

        $provinces = Geo::where('geo_id', null)->select('id', 'description')->get();

        return response()->json(['typeContacts' => $typeContacts, 'provinces' => $provinces]); 

    }

    public function show(Request $request)
    {
        $client = Person::where('id', $request->id)
                        ->where('type_person_id', 2)
                        ->with([
                            'contacts' => function ($query) {
                                $query->select('id','person_id','value', 'type_contact_id');
                            },
                            'directions' => function ($query) {
                                $query->select('id','person_id','description', 'geo_id');
                            }
                        ])
                        ->first();
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
                'person.secondName' => 'max:20|string',
                'person.firstLastName' => 'required|string|max:20',
                'person.secondLastName' => 'required|string|max:20',
                'person.gender' => 'required|boolean',
                'person.dateBirth' => 'date|before:today',
                'contacts' => 'required|array',
                'contacts.*.value' => 'required|string|max:30',
                'contacts.*.type_contact_id' => 'required|exists:type_contacts,id',
                'direction.description' => 'required|string|max:50',
                'direction.district_id' => 'required|exists:geos,id',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()]);
        }
        
        $client = Person::create([
            'firstName' => $request->person['firstName'],
            'secondName' => $request->person['secondName'],
            'firstLastName' => $request->person['firstLastName'],
            'secondLastName' => $request->person['secondLastName'],
            'gender' => $request->person['gender'],
            'dateBirth' => $request->person['dateBirth'],
            'type_person_id' => 2
        ]);

        foreach ($request->contacts as $contact)
        {
            Contact::create([
                'value' => $contact['value'],
                'type_contact_id' => $contact['type_contact_id'],
                'person_id' => $client->id
            ]);
        }

        Direction::create([
            'description' => $request->direction['description'],
            'geo_id' => $request->direction['district_id'],
            'person_id' => $client->id
        ]);

        return response()->json(['message' => 'Cliente creado correctamente']); 
    }

    public function update(Request $request)
    {
        try{
            $request->validate([
                'person.firstName' => 'required|string|max:20',
                'person.secondName' => 'max:20|string',
                'person.firstLastName' => 'required|string|max:20',
                'person.secondLastName' => 'required|string|max:20',
                'person.gender' => 'required|boolean',
                'person.dateBirth' => 'date|before:today',
                'contacts' => 'required|array',
                'contacts.*.value' => 'required|string|max:30',
                'contacts.*.type_contact_id' => 'required|exists:type_contacts,id',
                'direction.description' => 'required|string|max:50',
                'direction.district_id' => 'required|exists:geos,id',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()]);
        }

        $client = Person::find($request->id);

        if (!$client) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $client->update([
            'firstName' => $request->person['firstName'],
            'secondName' => $request->person['secondName'],
            'firstLastName' => $request->person['firstLastName'],
            'secondLastName' => $request->person['secondLastName'],
            'gender' => $request->person['gender'],
            'dateBirth' => $request->person['dateBirth'],
            'type_person_id' => 2
        ]);

        foreach ($request->contacts as $contact)
        {
            Contact::where('person_id', $client->id)
            ->where('type_contact_id', $contact['type_contact_id'])
            ->update(['value' => $contact['value']]);
        }

        // Buscar y actualizar la dirección existente
        Direction::where('person_id', $client->id)
        ->update([
              'description' => $request->direction['description'],
              'geo_id' => $request->direction['district_id']
          ]);

        return response()->json(['message' => 'Cliente Actualizado']); 
    }

    public function destroy(Request $request)
    {   
        if(Person::find($request->id)){
            Person::destroy($request->id);
            return response()->json(['message' => 'Cliente eliminado con exito']); 
        }
        return response()->json(['message' => 'Cliente no encontrado']); 
    }
}
