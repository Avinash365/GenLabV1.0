<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashLetterPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'marketing_person_id',
        'booking_ids',
        'total_amount',
        'payment_mode',
        'transaction_date',
        'amount_received',
        'notes',
    ];

    // Accessors/Mutators
    protected $casts = [
        'booking_ids' => 'array', // so we can store/retrieve booking_ids as JSON
        'transaction_date' => 'date',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function marketingPerson()
    {
        return $this->belongsTo(User::class, 'marketing_person_id');
    }
}
