<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, SoftDeletes; 

    protected $fillable = [
        'product_category_id',
        'invoice_no',
        'product_code',
        'product_name',
        'purchase_price',
        'purchase_unit',
        'unit',
        'remark',
        'created_by_id',
        'created_by_type',
    ];
 

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    
    public function creator()
    {
        return $this->morphTo(null, 'created_by_type', 'created_by_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'purchase_category_id');
    }  

    /**
     * Boot method to auto-generate invoice_no
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->invoice_no)) {
                $today = now()->format('Ymd'); // e.g., 20250817

                // Include soft-deleted rows too
                $latest = Product::withTrashed()
                    ->whereDate('created_at', now()->toDateString())
                    ->orderBy('id', 'desc')
                    ->first();

                if ($latest && $latest->invoice_no) {
                    // Extract last 3 digits
                    $lastNumber = (int) substr($latest->invoice_no, -3);
                    $nextNumber = $lastNumber + 1;
                } else {
                    $nextNumber = 1;
                }

                // Format as 3-digit number with leading zeros
                $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

                // Final format: yyyymmddXXX
                $product->invoice_no = $today . $formattedNumber;
            }
        
        });
    }


}
