<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryXProduct extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'quantity',
        'available',
        'product_id',
        'inventory_id'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
