<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleService extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id','service_date','description','kilometers','total_amount','person','service_bill_path'
    ];

    protected $dates = ['service_date'];
    protected $casts = [
        'service_date' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
