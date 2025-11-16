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
    public function renderCardsForBooking(NewBooking $booking, $item = null)
    {
        try { 

          
            $companyName = SiteSetting::value('company_name');
            try {
                $companyName_old = SiteSetting::value('company_name_old');
            } catch (\Exception $e) {
                $companyName_old = "INDINAN TESTING LABBORATORY";
            }
            
            // Check if job_order_date is before April 21, 2023
            $jobOrderDate = \Carbon\Carbon::parse($booking->job_order_date);
            $cutoffDate = \Carbon\Carbon::parse('2023-04-21');
            
            if ($jobOrderDate->lt($cutoffDate)) {
                $companyName = $companyName_old;
            }
            
            $booking->lr = '0101';
            $booking->companyName = $companyName;  

            $pdf = Pdf::loadView('pdf.booking_cards', [
                'booking' => $booking,
                'item'    => $item,
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
