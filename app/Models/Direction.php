<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'geo_id',
        'person_id',
    ];

    public $timestamps = false;

    public function geo()
    {
        return $this->belongsTo(Geo::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
