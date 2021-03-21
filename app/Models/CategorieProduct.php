<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Categorie;
use App\Models\Product;

class CategorieProduct extends Model
{
    use HasFactory;

    protected $table = "categories_products";

    protected $fillable = [
        "categorie_id",
        "product_id"
    ];

    // Отношения многие к одному с таблицой категорий
    public function Categorie()
    {
        return $this->belongsTo(Categorie::class,'categorie_id','id');
    }

    // Отношения многие к одному с таблицой продуктов
    public function Product()
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }

}
