<?php
namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\NewBooking;
use Exception;
use Illuminate\Support\Facades\Log;

use App\Models\SiteSetting;

class BookingCardService
{
    /**
     * Render a PDF directly in browser for a booking
     */
    public function renderCardsForBooking(NewBooking $booking)
    {
        try { 

          
            $companyName = SiteSetting::value('company_name'); 
            
            $booking->lr = '0101';
            $booking->companyName = $companyName;  

            $pdf = Pdf::loadView('pdf.booking_cards', [
                'booking' => $booking,
            ]);

            $fileName = 'booking_' . $booking->id . '.pdf';

            // Stream the PDF to browser (opens in new tab)
            return $pdf->stream($fileName);

        } catch (Exception $e) {
            Log::error('Failed to render booking PDF', [
                'booking_id' => $booking->id ?? null,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
