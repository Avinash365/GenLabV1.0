<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategories extends Model
{
    use HasFactory ,SoftDeletes; 

    protected $fillable = [
        'name',
        'description'
    ]; 

    /**
     * Products belonging to this category
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'purchase_category_id');
    }

}
