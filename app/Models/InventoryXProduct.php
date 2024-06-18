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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function saleDetail()
    {
        return $this->belongsTo(SaleDetail::class);
    }

    public function validateQuantity($saleQuantity)
    {
        $result = $this->quantity - $saleQuantity;
        return $result >= 0;
    }

    // Membresias
    public function productM()
    {
        return $this->belongsTo(Product::class, 'product_id')->select('id');
    }
    ///////

}
