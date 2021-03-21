<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CategorieProduct;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "about",
        "price"
    ];

    // Отношения многие к одному с таблицой категорий
    public function Categorie()
    {
        return $this->hasMany(CategorieProduct::class,'product_id','id');
    }
}
