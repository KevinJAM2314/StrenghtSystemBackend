<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'firstName',
        'secondName',
        'firstLastName',
        'secondLastName',
        'gender',
        'dateBirth',
        'type_person_id',
    ];

    public function directions()
    {
        return $this->hasMany(Direction::class)->select('id','person_id','description', 'geo_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class)->select('id','person_id','value', 'type_contact_id');
    }

    public function typePerson()
    {
        return $this->belongsTo(TypePerson::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function fullname()
    {
        return $this->firstName . ' ' . $this->secondName . ' ' . $this->firstLastName . ' ' . $this->secondLastName;
    }


    // Membresias
    public function salesM()
    {
        return $this->hasMany(Sale::class)->select('id', 'created_at', 'person_id');
    }
    ///
}
