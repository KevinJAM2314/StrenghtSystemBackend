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
        'price'
    ];
    
    public $timestamps = false;


    public function productImage()
    {
        return $this->belongsTo(ProductImage::class);
    }

    public function inventary()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function productXCategory()
    {
        return $this->hasMany(ProductXCategory::class);
    }
}
