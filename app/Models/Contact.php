<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'type_contact_id',
        'person_id',
    ];
    
    public $timestamps = false;

    public function typeContact()
    {
        return $this->belongsTo(TypeContact::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
