<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration',
    ];
    
    public $timestamps = false;

    public function productXcategories()
    {
        return $this->hasMany(ProductXCategory::class);
    }

}
