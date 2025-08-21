<?php 


namespace App\Services;

use App\Models\BookingItem;
use App\Models\Department;
use Carbon\Carbon;
use Exception;

class JobOrderService
{
    /**
     * Check if user input matches next job order number
     * Returns the job order number if valid, otherwise throws error
     */
    public static function generateJobOrderNo(string $inputJobOrderNo): string
    {
        $inputJobOrderNo = strtoupper($inputJobOrderNo);

        // Extract department code (letters at the start)
        preg_match('/^[A-Z]{3,4}/', $inputJobOrderNo, $matches);
        if (empty($matches)) {
            throw new Exception('Invalid job order number: no department code found.');
        }
        $deptCode = $matches[0];

        // Ensure department exists
        if (!Department::where('code', $deptCode)->exists()) {
            throw new Exception("Invalid department code: $deptCode does not exist.");
        }

        // Generate the next expected job order number for this department
        $today = now()->format('Ymd');
        $lastBooking = BookingItem::where('job_order_no', 'like', "$deptCode$today%")
            ->orderBy('job_order_no', 'desc')
            ->first();

        $nextNumber = $lastBooking
            ? str_pad((int)substr($lastBooking->job_order_no, strlen($deptCode) + 8) + 1, 3, '0', STR_PAD_LEFT)
            : '001';

        $expectedJobOrderNo = "$deptCode$today$nextNumber";

        // Check if user input matches expected
        if ($inputJobOrderNo !== $expectedJobOrderNo) {
            throw new Exception("Invalid job order number. Expected: $expectedJobOrderNo.");
        }

        return $expectedJobOrderNo; // valid, return it
    }
}


