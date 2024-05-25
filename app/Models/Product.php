<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
    ];
    
    public $timestamps = false;

    public function inventary()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function productXCategory()
    {
        return $this->hasMany(ProductXCategory::class);
    }
}
