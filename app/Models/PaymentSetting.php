<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    use HasFactory;

    // Allow mass assignment for all fields
    protected $fillable = [
        'instructions',
        'bank_name',
        'account_no',
        'branch',
        'branch_holder_name',
        'ifsc_code',
        'pan_code',
        'pan_no',
        'gstin',
        'upi',
        'updated_by',
    ];

    // Mutators to store certain fields in uppercase
    public function setAccountNoAttribute($value)
    {
        $this->attributes['account_no'] = strtoupper($value);
    }

    public function setIfscCodeAttribute($value)
    {
        $this->attributes['ifsc_code'] = strtoupper($value);
    }

    public function setPanCodeAttribute($value)
    {
        $this->attributes['pan_code'] = strtoupper($value);
    }

    public function setPanNoAttribute($value)
    {
        $this->attributes['pan_no'] = strtoupper($value);
    }

    public function setGstinAttribute($value)
    {
        $this->attributes['gstin'] = strtoupper($value);
    }
}
