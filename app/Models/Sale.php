<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'totalAmount',
        'cancel',
        'person_id',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    // Membresias
    public function saleDetailsM()
    {
        return $this->hasMany(SaleDetail::class)->select('id', 'sale_id', 'inventory_x_products_id');
    }
    /////
}
