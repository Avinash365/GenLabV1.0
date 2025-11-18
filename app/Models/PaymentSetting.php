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
        'created_by',
        'updated_by',
    ];

    // Mutators to store certain fields in uppercase
    protected function uppercaseOrNull(?string $value): ?string
    {
        return is_null($value) ? null : strtoupper(trim($value));
    }

    public function setAccountNoAttribute($value)
    {
        $this->attributes['account_no'] = $this->uppercaseOrNull($value);
    }

    public function setIfscCodeAttribute($value)
    {
        $this->attributes['ifsc_code'] = $this->uppercaseOrNull($value);
    }

    public function setPanCodeAttribute($value)
    {
        $this->attributes['pan_code'] = $this->uppercaseOrNull($value);
    }

    public function setPanNoAttribute($value)
    {
        $this->attributes['pan_no'] = $this->uppercaseOrNull($value);
    }

    public function setGstinAttribute($value)
    {
        $this->attributes['gstin'] = $this->uppercaseOrNull($value);
    }
}
