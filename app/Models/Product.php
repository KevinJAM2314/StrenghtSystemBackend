<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

     // Accessor para la URL completa de la imagen
     public function getImageUrlAttribute()
     {
         return config('app.url') . ':' . request()->getPort() . Storage::url('products/' . $this->image);
     }

    public function calculateTotal($quantity)
    {
        return $this->price * $quantity;
    }
 
    // Para que el Accessor sea incluido en la serialización JSON
    protected $appends = ['image_url'];

    public function inventoryXProducts()
    {
        return $this->hasMany(InventoryXProduct::class);
    }

    public function productXCategory()
    {
        return $this->hasMany(ProductXCategory::class);
    }

    // Membresias
    public function productXCategoryM()
    {
        return $this->hasMany(ProductXCategory::class, 'product_id')->select('id', 'product_id', 'category_id');
    }

}
