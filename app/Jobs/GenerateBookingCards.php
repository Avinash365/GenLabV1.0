<?php

namespace App\Jobs;

use App\Models\NewBooking;
use App\Services\BookingCardService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateBookingCards implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bookingId;

    public function __construct($bookingId)
    {
        $this->bookingId = $bookingId;
    }

    public function handle(BookingCardService $cardService)
    {
        $booking = NewBooking::with('items')->find($this->bookingId);

        if ($booking) {
            // Generate PDF for the booking (no database save)
            $cardService->generateCardsForBooking($booking);
        }
    }
}
