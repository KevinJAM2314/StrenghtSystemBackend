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
    
    public $timestamps = false;
    
    public function canton()
    {
        return $this->belongsTo(Geo::class, 'geo_id');
    }

    public function province()
    {
        return $this->belongsTo(Geo::class, 'geo_id');
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
