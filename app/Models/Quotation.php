<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_no', 'quotation_date', 'client_name', 'client_gstin', 'name_of_work',
        'bill_issue_to', 'marketing_person_code', 'generated_by', 'items',
        'total_amount', 'discount_percent', 'discount_amount', 'after_discount',
        'cgst_percent', 'cgst_amount', 'sgst_percent', 'sgst_amount',
        'igst_percent', 'igst_amount', 'round_off', 'payable_amount', 'letterhead'
    ];

    protected $casts = [
        'items' => 'array',
        'quotation_date' => 'date',
    ];

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
