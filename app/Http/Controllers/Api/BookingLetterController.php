<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Storage;
use App\Models\{NewBooking, Invoice};
use Illuminate\Support\Facades\File;

class BookingLetterController extends Controller
{
    public function viewLetter($id)
    {
        $booking = NewBooking::findOrFail($id);

        if (!$booking->upload_letter_path) {
            return response()->json([
                'message' => 'Letter not found'
            ], 404);
        }
        // Remove localhost base URL
        //$path = str_replace('http://127.0.0.1:8000/', '', $booking->upload_letter_path);
        $path = str_replace(url('/'), '', $booking->upload_letter_path);

        if (!file_exists(public_path($path))) {
            return response()->json([
                'message' => 'File does not exist',
                'checked_path' => public_path($path)
            ], 404);
        }
        return response()->file(public_path($path));
    }

    // {
    //     $result = [
    //         'name' => basename($path),
    //         'type' => 'folder',
    //         'children' => []
    //     ];

    //     foreach (File::directories($path) as $folder) {
    //         $result['children'][] = $this->buildTree($folder);
    //     }

    //     foreach (File::files($path) as $file) {
    //         $result['children'][] = [
    //             'name' => $file->getFilename(),
    //             'type' => 'file',
    //             'url' => asset('storage/letters/' .
    //                 str_replace(public_path('storage/letters') . '/', '', $file->getPathname()))
    //         ];
    //     }

    //     return $result;
    // }

    public function showLetters($path = null)
    {
        $basePath = public_path('storage/letters');

        // Prevent directory traversal
        if ($path && str_contains($path, '..')) {
            abort(400, 'Invalid path');
        }

        $currentPath = $path
            ? $basePath . '/' . $path
            : $basePath;

        if (!File::exists($currentPath)) {
            abort(404, 'Folder not found');
        }

        $tree = $this->buildTree($currentPath, $path ?? '');

        return view('letters.explorer', compact('tree', 'path'));
    }

   
    private function buildTree($path, $relativePath = '')
    {
        $result = [
            'name' => basename($path),
            'type' => 'folder',
            'children' => []
        ];

        foreach (File::directories($path) as $folder) {
            $folderName = basename($folder);
            $result['children'][] = [
                'name' => $folderName,
                'type' => 'folder',
                'url'  => url('reports-explorer/' . trim($relativePath . '/' . $folderName, '/'))
            ];
        }

        foreach (File::files($path) as $file) {

            $fileRelativePath = trim(
                $relativePath . '/' . $file->getFilename(),
                '/'
            );

            $result['children'][] = [
                'name' => $file->getFilename(),
                'type' => 'file',
                'url'  => asset('storage/letters/' . $fileRelativePath)
            ];
        }

        return $result;
    }

    // public function showInvoiceLetters($invoiceId)
    // {
    //     $basePath = public_path('storage/letters');

    //     // dd($invoiceId);
    //     // exit; 
    //     //  Get invoice
    //     $invoice = Invoice::findOrFail($invoiceId);

    //     $allBookingIds = [];

    //     //  Single booking ID
    //     if (!empty($invoice->new_booking_id)) {
    //         $allBookingIds[] = $invoice->new_booking_id;
    //     }

    //     //  Multiple booking IDs (comma separated)
    //     if (!empty($invoice->invoice_booking_ids)) {
    //         $multipleIds = explode(',', $invoice->invoice_booking_ids);

    //         foreach ($multipleIds as $id) {
    //             $cleanId = trim($id);
    //             if (!empty($cleanId)) {
    //                 $allBookingIds[] = $cleanId;
    //             }
    //         }
    //     }

    //     //  Remove duplicates
    //     $allBookingIds = array_unique($allBookingIds);
    //     // dd($allBookingIds);
    //     // exit;
    //     if (empty($allBookingIds)) {
    //         abort(404, 'No booking IDs found');
    //     }

       
    //     //  Get bookings with items
    //     $bookings = NewBooking::with('items')
    //         ->whereIn('id', $allBookingIds)
    //         ->get();
        

    //     $result = [
    //         'name' => 'Invoice-' . $invoice->invoice_no,
    //         'type' => 'folder',
    //         'children' => []
    //     ];

    //     $jobOrders = [];

    //     //  Collect job_order_number from items
    //     foreach ($bookings as $booking) {
    //         foreach ($booking->items as $item) {
    //             if (!empty($item->job_order_number)) {
    //                 $jobOrders[] = $item->job_order_number;
    //             }
    //         }
    //     }

    //     //  Remove duplicate job orders
    //     $jobOrders = array_unique($jobOrders);
    //     dd($jobOrders);
    //     exit;
       
    //     //  Show only those folders
    //     foreach ($jobOrders as $jobOrder) {

    //         $folderPath = $basePath . '/' . $jobOrder;

    //         if (File::exists($folderPath) && File::isDirectory($folderPath)) {

    //             $folderData = [
    //                 'name' => $jobOrder,
    //                 'type' => 'folder',
    //                 'children' => []
    //             ];

    //             foreach (File::files($folderPath) as $file) {

    //                 $folderData['children'][] = [
    //                     'name' => $file->getFilename(),
    //                     'type' => 'file',
    //                     'url'  => asset('storage/letters/' . $jobOrder . '/' . $file->getFilename())
    //                 ];
    //             }

    //             $result['children'][] = $folderData;
    //         }
    //     } 
    //     dd($result);
    //     exit;  

    //     return view('letters.invoice_explorer', compact('result', 'invoice'));
    // }

    // public function showInvoiceLetters($invoiceId)
    // {
    //     $basePath = public_path('storage/letters');

    //     //  Get Invoice
    //     $invoice = Invoice::findOrFail($invoiceId);

    //     $allBookingIds = [];

    //     //  Single booking id
    //     if (!empty($invoice->new_booking_id)) {
    //         $allBookingIds[] = $invoice->new_booking_id;
    //     }

    //     //  Multiple booking ids (comma separated)
    //     if (!empty($invoice->invoice_booking_ids)) {
    //         $multipleIds = explode(',', $invoice->invoice_booking_ids);

    //         foreach ($multipleIds as $id) {
    //             $cleanId = trim($id);
    //             if (!empty($cleanId)) {
    //                 $allBookingIds[] = $cleanId;
    //             }
    //         }
    //     }

    //     //  Remove duplicates
    //     $allBookingIds = array_unique($allBookingIds);

    //     if (empty($allBookingIds)) {
    //         return view('letters.invoice_explorer', [
    //             'result' => ['children' => []],
    //             'invoice' => $invoice
    //         ]);
    //     }

    //     //  Directly fetch job_order_number from BookingItem (Optimized)
    //     $jobOrders = \App\Models\BookingItem::whereIn('new_booking_id', $allBookingIds)
    //         ->pluck('job_order_no')
    //         ->filter()
    //         ->unique()
    //         ->toArray();

    //     $result = [
    //         'children' => []
    //     ];

    //     //  Build folder structure
    //     foreach ($jobOrders as $jobOrder) {

    //         $folderPath = $basePath . '/' . $jobOrder;

    //         if (File::exists($folderPath) && File::isDirectory($folderPath)) {

    //             $folderData = [
    //                 'name' => $jobOrder,   // Folder name = job_order_number
    //                 'type' => 'folder',
    //                 'children' => []
    //             ];

    //             foreach (File::files($folderPath) as $file) {

    //                 $folderData['children'][] = [
    //                     'name' => $file->getFilename(),
    //                     'type' => 'file',
    //                     'url'  => asset('storage/letters/' . $jobOrder . '/' . $file->getFilename())
    //                 ];
    //             }

    //             $result['children'][] = $folderData;
    //         }
    //     }

    //     return view('letters.invoice_explorer', compact('result', 'invoice'));
    // }

    public function showInvoiceLetters($invoiceId)
    {
        $basePath = public_path('storage/letters');
        
      
        //  Get Invoice
        $invoice = Invoice::findOrFail($invoiceId);

        $allBookingIds = [];

        //  Single booking id
        if (!empty($invoice->new_booking_id)) {
            $allBookingIds[] = $invoice->new_booking_id;
        } 
        //  Multiple booking ids (comma separated)
        if (!empty($invoice->invoice_booking_ids)) {
            $multipleIds = explode(',', $invoice->invoice_booking_ids);

            foreach ($multipleIds as $id) {
                $cleanId = trim($id);
                if (!empty($cleanId)) {
                    $allBookingIds[] = $cleanId;
                }
            }
        }

        //  Remove duplicates
        $allBookingIds = array_unique($allBookingIds);

        if (empty($allBookingIds)) {
            return view('letters.invoice_explorer', [
                'result' => ['children' => []],
                'invoice' => $invoice
            ]);
        }

        //  Fetch ref_no directly from NewBooking
        $refNumbers = \App\Models\NewBooking::whereIn('id', $allBookingIds)
            ->pluck('reference_no')
            ->filter()
            ->unique()
            ->toArray();

        $result = [
            'children' => []
        ];

        //  Build folder structure using ref_no as folder name
        foreach ($refNumbers as $refNo) {

            $refNo = $this->convertToFolderName($refNo);
            dd($refNo);
            exit;
            $folderPath = $basePath . '/' . $refNo;

            if (File::exists($folderPath) && File::isDirectory($folderPath)) {

                $folderData = [
                    'name' => $refNo,   // Folder name = ref_no
                    'type' => 'folder',
                    'children' => []
                ];

                foreach (File::files($folderPath) as $file) {

                    $folderData['children'][] = [
                        'name' => $file->getFilename(),
                        'type' => 'file',
                        'url'  => asset('storage/letters/' . $refNo . '/' . $file->getFilename())
                    ];
                }

                $result['children'][] = $folderData;
            }
        }

        return view('letters.invoice_explorer', compact('result', 'invoice'));
    }

    private function convertToFolderName($invoiceNumber)
    {
        // Replace special characters with dash
        $folder = str_replace(
            ['/', '\\', '(', ')', '.', ' '],
            '-',
            $invoiceNumber
        );

        // Remove multiple dashes
        $folder = preg_replace('/-+/', '-', $folder);

        // Remove any character not letter, number or dash
        $folder = preg_replace('/[^A-Za-z0-9\-]/', '', $folder);

        // Trim dash from start/end
        return trim($folder, '-');
    }
}
