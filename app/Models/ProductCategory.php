<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    use HasFactory ,SoftDeletes; 

    protected $fillable = [
        'name',
        'slug',
        'description',
    ]; 

    /**
     * Products belonging to this category
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'product_category_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            // Optional: update slug when name changes
            if ($category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

}   
