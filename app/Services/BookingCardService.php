<?php
namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\NewBooking;
use Exception;

class BookingCardService
{
    /**
     * Generate a single PDF with multiple pages (two cards per page) for a booking
     */
    public function generateCardsForBooking(NewBooking $booking): string
    {
        try {
           
            $pdf = Pdf::loadView('pdf.booking_cards', [
                'booking' => $booking,
            ]);

            $fileName = 'booking_' . $booking->id . '.pdf';

            // Save to storage/app/public/cards/
            Storage::disk('public')->put('cards/' . $fileName, $pdf->output());

            Log::info("Booking PDF generated successfully", [
                'booking_id' => $booking->id,
                'file'       => 'storage/cards/' . $fileName,
            ]);

            return $fileName;

        } catch (Exception $e) {
            // Log error for debugging
            Log::error('Failed to generate booking PDF', [
                'booking_id' => $booking->id ?? null,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            // Rethrow so controller can handle it
            throw $e;
        }
    }
}
