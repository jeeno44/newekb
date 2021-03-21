<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CategorieProduct;

class Categorie extends Model
{
    use HasFactory;

    protected $fillable = [
        "categorie",
        "about"
    ];

    // Отношения многие к одному с таблицой продуктов
    public function Products()
    {
        return $this->hasMany(CategorieProduct::class,'categorie_id','id');
    }
}
