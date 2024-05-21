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
        'money,'
    ];
    
    public $timestamps = false;


    public function productImage()
    {
        return $this->belongsTo(ProductImage::class);
    }

    public function inventary()
    {
        return $this->belongsTo(Inventary::class);
    }

    public function productXcategories()
    {
        return $this->hasMany(ProductXCategory::class);
    }
}
