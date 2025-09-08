<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlankInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'blank_invoice_id','description','job_order_no','qty','rate','amount'
    ];

    public function invoice()
    {
        return $this->belongsTo(BlankInvoice::class, 'blank_invoice_id');
    }
}
