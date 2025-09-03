<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlankInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id','client_name','marketing_person',
        'invoice_no','reference_no','invoice_date','letter_date',
        'name_of_work','bill_issue_to','client_gstin','address',
        'total_amount','discount_percent','discount_amount',
        'after_discount','cgst_percent','cgst_amount',
        'sgst_percent','sgst_amount','igst_percent','igst_amount',
        'round_off','payable_amount','invoice_type'
    ];

    public function items()
    {
        return $this->hasMany(BlankInvoiceItem::class);
    }
}
