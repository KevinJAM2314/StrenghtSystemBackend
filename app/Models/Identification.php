<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Identification extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'type_identification_id',
        'person_id'  
    ];

    public function typeIdentification()
    {
        return $this->belongsTo(TypeIdentification::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
