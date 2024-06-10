<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantity',
        'total',
        'sale_id',
        'inventory_x_products_id',
    ];
    public $timestamps = false;

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function inventoryXProducts()
    {
        return $this->belongsTo(InventoryXProduct::class);
    }
}
