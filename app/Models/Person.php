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
    ];

    public function geo()
    {
        return $this->belongsTo(Geo::class);
    }

    
    public function directions()
    {
        return $this->hasMany(Direction::class);
    }
}
