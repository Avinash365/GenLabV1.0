<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductStockEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_stock_entries';

    protected $fillable = [
        'product_code',
        'purchase_price',
        'quantity',
        'purchase_unit',
        'remarks',
        'upload_bill',
        'invoice_no',
    ];

    /**
     * Relationship: A stock entry belongs to a product
     */
    
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_code', 'product_code');
    }

    /**
     * Accessor for formatted purchase price
     */
    public function getFormattedPurchasePriceAttribute()
    {
        return '₹' . number_format($this->purchase_price, 2);
    }

    /**
     * Mutator: Ensure invoice_no is always stored in uppercase
     */
    public function setInvoiceNoAttribute($value)
    {
        $this->attributes['invoice_no'] = strtoupper($value);
    }
}
