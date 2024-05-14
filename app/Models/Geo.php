<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Geo extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'type_geo_id',
        'geo_id',
    ];

    public function geos()
    {
        return $this->hasMany(Geo::class);
    }

    public function directions()
    {
        return $this->hasMany(Direction::class);
    }

    public function typeGeo()
    {
        return $this->belongsTo(TypeGeo::class);
    }
}
