<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

     // 🔹 Mutator for invoice_date (store in DB as Y-m-d)
    public function setInvoiceDateAttribute($value)
    {
        $this->attributes['invoice_date'] = !empty($value)
            ? Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d')
            : null;
    }
     // 🔹 Accessor for invoice_date (show as d-m-Y)
    public function getInvoiceDateAttribute($value)
    {
        return !empty($value)
            ? Carbon::parse($value)->format('d-m-Y')
            : null;
    }

    // 🔹 Mutator for letter_date
    public function setLetterDateAttribute($value)
    {
        $this->attributes['letter_date'] = !empty($value)
            ? Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d')
            : null;
    }

    // 🔹 Accessor for letter_date
    public function getLetterDateAttribute($value)
    {
        return !empty($value)
            ? Carbon::parse($value)->format('d-m-Y')
            : null;
    }
}
