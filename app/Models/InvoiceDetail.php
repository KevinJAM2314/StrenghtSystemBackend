<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantity',
        'total',
        'inventory_name',
        'invoice_id',
    ];

    public $timestamps = false;
}
