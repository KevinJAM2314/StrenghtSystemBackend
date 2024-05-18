<?php

namespace App\Http\Controllers;
use App\Models\Person;
use App\Models\Contact;
use App\Models\TypeContact;
use App\Models\Geo;
use App\Models\TypeGeo;
use App\Models\Direction;
use Illuminate\Validation\ValidationException;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function create()
    {
        $typeGeos = TypeGeo::select('description')->get();
        $typeContacts = TypeContact::select('description')->get();

        $provinces = Geo::where('geo_id', null)->get();

        return response()->json(['typeGeos' => $typeGeos, 'typeContacts' => $typeContacts, 'provinces' => $provinces]); 

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
