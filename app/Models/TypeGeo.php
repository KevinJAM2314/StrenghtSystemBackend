<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeGeo extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $fillable = [
        'description',
    ];

    public function geos()
    {
        return $this->hasMany(Geo::class);
    }
}
