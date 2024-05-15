<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'description'
    ];

    public $timestamps = false;

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}