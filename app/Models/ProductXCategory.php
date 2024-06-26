<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductXCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'category_id'
    ];

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Membresias
    public function categoryM()
    {
        return $this->belongsTo(Category::class, 'category_id')->whereNotNull('duration');
    }

}
