<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ReportEditorFile; 
use App\Models\BookingItem;

use App\Services\{ReportPdfGenerationService,ReportWordGenerationService}; 
use App\Services\CountTextLineBreakService;
use SimpleSoftwareIO\QrCode\Facades\QrCode; 


use Mpdf\Mpdf; 

class ReportEditorController extends Controller
{
    protected $pdfService, $countTextLineBreak;
    protected $wordService;


    public function __construct(ReportPdfGenerationService $pdfService, CountTextLineBreakService $countTextLineBreak, ReportWordGenerationService $wordService)
    {
        $this->pdfService = $pdfService;  
        $this->wordService = $wordService;
        $this->countTextLineBreak = $countTextLineBreak; 
    }
     

    public function index()
    {
        $reports = ReportEditorFile::latest()->get();
        return view('Reportfrmt.index', compact('reports'));
    }    

    public function generate(BookingItem $item, $type = null){  

       $assignedReport = $item->reports->first();
       $booking = $item->booking;

        $reports = ReportEditorFile::latest()->get(); 

        return view('Reportfrmt.generate', compact('reports', 'assignedReport', 'item', 'booking', 'type'));
    }
    
    public function editReport($pivotId, $type = null)
    {
        // Determine table based on type
        $tableName = ($type === '28day') ? 'booking_item_report_28day' : 'booking_item_report';

        // Fetch the pivot record from the correct table
        $pivotRecord = \DB::table($tableName)->where('id', $pivotId)->first();

        if (!$pivotRecord) {
            abort(404, 'Report not found');
        }

        // Determine which relationship to load based on type
        $relationship = ($type === '28day') ? 'reports_28days' : 'reports';

        // Fetch the booking item with the correct reports relationship
        $item = BookingItem::with(['booking', 'analyst', 'receivedBy', $relationship])->find($pivotRecord->booking_item_id);

        if (!$item) {
            abort(404, 'Booking item not found');
        }

        // Fetch assigned report
        $assignedReport = ReportEditorFile::find($pivotRecord->report_editor_file_id);

        $booking = $item->booking;

        // Pass reports via the same variable so Blade doesn't change
        $reports = $item->$relationship;

        return view('Reportfrmt.generate', compact('reports', 'assignedReport', 'item', 'booking', 'pivotRecord', 'type'));
    }


    public function viewPdf($filename)
    {
        
        return redirect(asset('storage/generatedReports/' . ltrim($filename, '/')));
    }


    public function save(Request $request)
    {
        $editingId = $request->input('editing_report_id');

        // Validate inputs
        $request->validate([
            'report_no' => 'required|string|max:255',
            'report_description' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);

        // If editing, find existing record
        if ($editingId) {
            $report = ReportEditorFile::findOrFail($editingId);

            if (ReportEditorFile::where('report_no', $request->report_no)
                ->where('id', '!=', $editingId)
                ->exists()) {
                return back()->withErrors(['report_no' => 'Report name already exists.'])->withInput();
            }
        } else {
            if (ReportEditorFile::where('report_no', $request->report_no)->exists()) {
                return back()->withErrors(['report_no' => 'Report name already exists.'])->withInput();
            }
            $report = new ReportEditorFile();
        }

        $report_no = $request->report_no;
        $report_description = $request->report_description;
        $content = $request->content;

        $sanitizedReportNo = preg_replace('/[^A-Za-z0-9_\-]/', '_', $report_no);
        $fileName = 'reportFormat/' . $sanitizedReportNo . '_' . time() . '.html';

        Storage::disk('public')->put($fileName, $content);

        // Delete old file if updating
        if ($editingId && Storage::disk('public')->exists($report->file_path)) {
            Storage::disk('public')->delete($report->file_path);
        }

        $report->report_no = $report_no;
        $report->report_description = $report_description;
        $report->file_path = $fileName;
        $report->save(); 

        // Preserve values after successful save/update
        return back()->with([
            'success' => $editingId ? 'Report updated successfully!' : 'Report saved successfully!',
        ])->withInput([
            'editing_report_id' => $editingId,
            'report_no' => $report_no,
            'report_description' => $report_description,
            'content' => $content,
        ]); 
    }

    public function destroy($id)
    {
        $report = ReportEditorFile::findOrFail($id);

        // Delete the file from storage
        if (Storage::disk('public')->exists($report->file_path)) {
            Storage::disk('public')->delete($report->file_path);
        }

        // Delete database record
        $report->delete();

        return back()->with('success', 'Report deleted successfully!');
    } 


    public function generateReportPDF(Request $request)
    {     
       
        $request->validate([
            'report_no' => 'required|string|max:255',
            'report_description' => 'nullable|string|max:2000',
            'content' => 'required|string',
            'ulr_no' => 'nullable|string|max:255',
            'issued_to' => 'nullable|string|max:2000',
            'date_of_receipt' => 'nullable|date',
            'date_of_start_analysis' => 'nullable|date',
            'letter_ref_no' => 'nullable|string|max:255',
            'letter_ref_date' => 'nullable|date',
            'completion_date' => 'nullable|date',
            'sample_description' => 'nullable|string|max:2000',
            'date_of_issue' => 'nullable|date',
            'name_of_work' => 'nullable|string|max:2000',
            'booking_item_id' => 'required|integer',
            'booking_id' => 'required|integer',
            'editing_report_id' => 'nullable|integer',
            'include_header'   => 'nullable', 
        ]);

        $headerData = [
            'booking_item_id' => $request->input('booking_item_id') ?? "",  
            'report_no' => $request->input('report_no') ?? "",
            'ulr_no' => $request->input('ulr_no') ?? "",
            'issued_to' => $request->input('issued_to') ?? "", 
            'date_of_receipt' => $request->input('date_of_receipt') ?? "",
            'date_of_start_analysis' => $request->input('date_of_start_analysis') ?? "",
            'letter_ref_date' => $request->input('letter_ref_date') ?? "", 
            'letter_ref' => $request->input('letter_ref_no') ?? "", 
            'date_of_completion' => $request->input('completion_date') ?? "",
            'sample_description' => $request->input('sample_description') ?? "", 
            'date_of_issue' => $request->input('date_of_issue') ?? "",
            'name_of_work' => $request->input('name_of_work') ?? "", 
            'include_header' => $request->input('include_header') ?? "0",
        ]; 

        // Count line breaks for margin adjustments
        $lineBreaks = $this->countTextLineBreak->countLineBreaks([
            $headerData['issued_to'],
            $headerData['sample_description'],
            $headerData['name_of_work'],
        ]); 
        $headerData['line_breaks'] = $lineBreaks; 

        // Check for old record
        $oldRecord = \DB::table('booking_item_report')
            ->where('booking_id', $request->input('booking_id'))
            ->where('booking_item_id', $request->input('booking_item_id'))
            ->where('report_editor_file_id', $request->input('editing_report_id') ?? null)
            ->first(); 

        // Delete old HTML if exists
        if ($oldRecord && $oldRecord->generated_report_path) {
            Storage::disk('public')->delete($oldRecord->generated_report_path);
        }

        // Save the new HTML content
        $htmlFileName = 'reports/' . time() . '_report.html';
        Storage::disk('public')->put($htmlFileName, $request->input('content'));

        // Generate PDF
        $pdfService = new ReportPdfGenerationService();
        $pdfRelativePath = $pdfService->generateFromHtmlFiles(
            [$htmlFileName],
            $headerData
        );
    
        // Delete old PDF if exists
        if ($oldRecord && $oldRecord->pdf_path) { 
            $filePath = 'public/' . ltrim($oldRecord->pdf_path, '/');
            Storage::disk('public')->delete($filePath);
        }
 
        // Update DB with new HTML and PDF paths
        \DB::table('booking_item_report')->updateOrInsert(
            [
                'booking_id' => $request->input('booking_id'),
                'booking_item_id' => $request->input('booking_item_id'),
                'report_editor_file_id' => $request->input('editing_report_id') ?? null,
            ],
            [
                'generated_report_path' => $htmlFileName,
                'pdf_path' => $pdfRelativePath, 
                'ult_r_no' => $headerData['ulr_no'], 
                'date_of_start_of_analysis' => $headerData['date_of_start_analysis'], 
                'date_of_completion_of_analysis' => $headerData['date_of_completion'], 
                'date_of_receipt'   => $headerData['date_of_receipt'],  
                'issue_to_date'   => $headerData['date_of_issue'],
                'updated_at' => now(),
            ]
        );  

        $pivotRecord = \DB::table('booking_item_report')
                ->where('booking_id', $request->input('booking_id'))
                ->where('booking_item_id', $request->input('booking_item_id'))
                ->where('report_editor_file_id', $request->input('editing_report_id') ?? null)
                ->first();
       
        return redirect()->route('generateReportPDF.editReport', ['pivotId' => $pivotRecord->id])
                          ->with('success', 'Report generated successfully!');
    } 
    
    public function generatePdf28Days(Request $request)
    {     
        $request->validate([
            'report_no' => 'required|string|max:255',
            'report_description' => 'nullable|string|max:2000',
            'content' => 'required|string',
            'ulr_no' => 'nullable|string|max:255',
            'issued_to' => 'nullable|string|max:2000',
            'date_of_receipt' => 'nullable|date',
            'date_of_start_analysis' => 'nullable|date',
            'letter_ref_no' => 'nullable|string|max:255',
            'letter_ref_date' => 'nullable|date',
            'completion_date' => 'nullable|date',
            'sample_description' => 'nullable|string|max:2000',
            'date_of_issue' => 'nullable|date',
            'name_of_work' => 'nullable|string|max:2000',
            'booking_item_id' => 'required|integer',
            'booking_id' => 'required|integer',
            'editing_report_id' => 'nullable|integer',
            'include_header'   => 'nullable', 
        ]);

        $headerData = [
            'booking_item_id' => $request->input('booking_item_id') ?? "",  
            'report_no' => $request->input('report_no') ?? "",
            'ulr_no' => $request->input('ulr_no') ?? "",
            'issued_to' => $request->input('issued_to') ?? "", 
            'date_of_receipt' => $request->input('date_of_receipt') ?? "",
            'date_of_start_analysis' => $request->input('date_of_start_analysis') ?? "",
            'letter_ref_date' => $request->input('letter_ref_date') ?? "", 
            'letter_ref' => $request->input('letter_ref_no') ?? "", 
            'date_of_completion' => $request->input('completion_date') ?? "",
            'sample_description' => $request->input('sample_description') ?? "", 
            'date_of_issue' => $request->input('date_of_issue') ?? "",
            'name_of_work' => $request->input('name_of_work') ?? "", 
            'include_header' => $request->input('include_header') ?? "0",
        ]; 

        // Count line breaks for margin adjustments
        $lineBreaks = $this->countTextLineBreak->countLineBreaks([
            $headerData['issued_to'],
            $headerData['sample_description'],
            $headerData['name_of_work'],
        ]); 
        $headerData['line_breaks'] = $lineBreaks; 

        // Check for old record
        $oldRecord = \DB::table('booking_item_report_28day')
            ->where('booking_id', $request->input('booking_id'))
            ->where('booking_item_id', $request->input('booking_item_id'))
            ->where('report_editor_file_id', $request->input('editing_report_id') ?? null)
            ->first(); 

        // Delete old HTML if exists
        if ($oldRecord && $oldRecord->generated_report_path) {
            Storage::disk('public')->delete($oldRecord->generated_report_path);
        }

        // Save the new HTML content
        $htmlFileName = 'reports/' . time() . '_report.html';
        Storage::disk('public')->put($htmlFileName, $request->input('content'));

        // Generate PDF
        $pdfService = new ReportPdfGenerationService();
        $pdfRelativePath = $pdfService->generateFromHtmlFiles(
            [$htmlFileName],
            $headerData
        );
    
        // Delete old PDF if exists
        if ($oldRecord && $oldRecord->pdf_path) { 
            $filePath = 'public/' . ltrim($oldRecord->pdf_path, '/');
            Storage::disk('public')->delete($filePath);
        }
 
        // Update DB with new HTML and PDF paths
        \DB::table('booking_item_report_28day')->updateOrInsert(
            [
                'booking_id' => $request->input('booking_id'),
                'booking_item_id' => $request->input('booking_item_id'),
                'report_editor_file_id' => $request->input('editing_report_id') ?? null,
            ],
            [
                'generated_report_path' => $htmlFileName,
                'pdf_path' => $pdfRelativePath, 
                'ult_r_no' => $headerData['ulr_no'], 
                'date_of_start_of_analysis' => $headerData['date_of_start_analysis'], 
                'date_of_completion_of_analysis' => $headerData['date_of_completion'], 
                'date_of_receipt'   => $headerData['date_of_receipt'],  
                'issue_to_date'   => $headerData['date_of_issue'],
                'updated_at' => now(),
            ]
        );  

        $pivotRecord = \DB::table('booking_item_report_28day')
                ->where('booking_id', $request->input('booking_id'))
                ->where('booking_item_id', $request->input('booking_item_id'))
                ->where('report_editor_file_id', $request->input('editing_report_id') ?? null)
                ->first();
       
        return redirect()->route('generateReportPDF.editReport', ['pivotId' => $pivotRecord->id, 'type' => '28day'])
                          ->with('success', 'Report generated successfully!');
    }




    public function downloadReportPdf($pdfRelativePath){ 

        return response()->download(storage_path('app/public/' . $pdfRelativePath));
    }

    public function downloadMergedBookingPDF($bookingId)
    {
   

        // 1. Get all PDF paths for the given booking_id
        $pdfPaths = \DB::table('booking_item_report')
            ->where('booking_id', $bookingId)
            ->whereNotNull('pdf_path')
            ->pluck('pdf_path')
            ->toArray();

        
        if (empty($pdfPaths)) {
            return response()->json(['message' => 'No PDFs found for this booking.'], 404);
        }

        // 2. Create mPDF instance
        $mpdf = new \Mpdf\Mpdf();

        foreach ($pdfPaths as $index => $path) {
            $fullPath = storage_path('app/public/' . $path);
            if (!file_exists($fullPath)) continue;

            if ($index > 0) $mpdf->AddPage();

            $pageCount = $mpdf->SetSourceFile($fullPath);
            for ($i = 1; $i <= $pageCount; $i++) {
                $tplId = $mpdf->ImportPage($i);
                $mpdf->UseTemplate($tplId);
                if ($i < $pageCount) $mpdf->AddPage();
            }
        }

        // 3. Save PDF to a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'merged_') . '.pdf';
        $mpdf->Output($tempFile, 'F');

        // 4. Return download and delete automatically after sending
        return response()->download($tempFile, "merged_booking_{$bookingId}.pdf")->deleteFileAfterSend(true);
    }

    public function varify($booking_item_id)
    {
        // Fetch data from booking_item_report along with related booking_item and new_booking info
        $data = \DB::table('booking_item_report as bir')
            ->join('booking_items as bi', 'bir.booking_item_id', '=', 'bi.id')
            ->join('new_bookings as nb', 'bi.new_booking_id', '=', 'nb.id')
            ->where('bir.booking_item_id', $booking_item_id)
            ->select(
                'bir.ult_r_no',
                'bi.job_order_no',
                'nb.reference_no as ref_no',
                'bir.date_of_receipt',
                'bir.issue_to_date',
                'bir.generated_report_path',
                'bir.pdf_path'
            )
            ->first(); // Use first() to get a single record

        if ($data) {
            $status = 'OK';
        } else {
            $status = 'Error';
        }

        // Return to Blade view with status and fetched data
        return view('Reportfrmt.varify', compact('status', 'data'));
    }

    public function livePreview(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $html = $request->input('content');

        // Define temporary HTML storage folder
        $tempHtmlFolder = storage_path('app/public/tempHtml');
        if (!file_exists($tempHtmlFolder)) {
            mkdir($tempHtmlFolder, 0755, true);
        }

        // Clean old temp HTML files (older than 10 minutes)
        $this->cleanOldTempFiles($tempHtmlFolder, 1);

        // Store temporary HTML file
        $tempHtmlPath = 'tempHtml/' . time() . '_preview.html';
        Storage::disk('public')->put($tempHtmlPath, $html);

        // Generate PDF using your existing service
        $pdfService = new ReportPdfGenerationService();

        $headerData = [
            'booking_item_id' => $request->input('booking_item_id') ?? 1,
            'report_no' => $request->input('report_no') ?? "",
            'ulr_no' => $request->input('ulr_no') ?? "",
            'issued_to' => $request->input('issued_to') ?? "",
            'date_of_receipt' => $request->input('date_of_receipt') ?? "",
            'date_of_start_analysis' => $request->input('date_of_start_analysis') ?? "",
            'letter_ref_date' => $request->input('letter_ref_date') ?? "",
            'letter_ref' => $request->input('letter_ref_no') ?? "",
            'date_of_completion' => $request->input('completion_date') ?? "",
            'sample_description' => $request->input('sample_description') ?? "",
            'date_of_issue' => $request->input('date_of_issue') ?? "",
            'name_of_work' => $request->input('name_of_work') ?? "",
            'include_header' => $request->input('include_header') ?? "1",
        ];

        // Count line breaks for margin adjustment
        $lineBreaks = $this->countTextLineBreak->countLineBreaks([
            $headerData['issued_to'],
            $headerData['sample_description'],
            $headerData['name_of_work'],
        ]);
        $headerData['line_breaks'] = $lineBreaks;

        // Generate temporary PDF
        $pdfPath = $pdfService->generateFromHtmlFiles(
            [$tempHtmlPath],
            $headerData,
            'live_preview_' . time() . '.pdf',
            true // store as temp
        );

        return response()->json([
            'pdf_url' => asset('storage/' . $pdfPath),
            'html_url' => asset('storage/' . $tempHtmlPath),
        ]);
    }

    protected function cleanOldTempFiles($folder, $minutes = 1)
    {
        if (!file_exists($folder)) {
            return;
        }

        $files = glob($folder . '/*');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                $fileAge = ($now - filemtime($file)) / 60; // in minutes
                if ($fileAge > $minutes) {
                    @unlink($file);
                }
            }
        }
    }


    public function generateReportWord(Request $request)
    {
        $request->validate([
            'report_no' => 'required|string|max:255',
            'report_description' => 'nullable|string|max:2000',
            'content' => 'required|string',
            'ulr_no' => 'nullable|string|max:255',
            'issued_to' => 'nullable|string|max:2000',
            'date_of_receipt' => 'nullable|date',
            'date_of_start_analysis' => 'nullable|date',
            'letter_ref_no' => 'nullable|string|max:255',
            'letter_ref_date' => 'nullable|date',
            'completion_date' => 'nullable|date',
            'sample_description' => 'nullable|string|max:2000',
            'date_of_issue' => 'nullable|date',
            'name_of_work' => 'nullable|string|max:2000',
            'booking_item_id' => 'required|integer',
            'booking_id' => 'required|integer',
            'editing_report_id' => 'nullable|integer',
        ]);
        // Save new HTML content in storage
        $htmlFileName = 'reports/' . time() . '_report.html';
        Storage::disk('public')->put($htmlFileName, $request->input('content'));

        // Generate Word report and trigger download
        $wordService = new \App\Services\ReportWordGenerationService();

        // The service already returns a download response
        return $wordService->generateFromHtmlFiles([$htmlFileName]);
    }

    public function downloadQR(Request $request)
    {
        $request->validate([
            'booking_item_id' => 'required|integer',
        ]);

        $bookingItemId = $request->input('booking_item_id');

        // Generate URL for QR code (same as your PDF verification link)
        $reportUrl = route('varification.view', ['no' => $bookingItemId]);

        // Generate QR code as SVG
        $qrImage = QrCode::format('svg')->size(300)->margin(2)->generate($reportUrl);

        // Set filename
        $fileName = 'booking_' . $bookingItemId . '_qr.svg';

        // Return as download response
        return response($qrImage, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', "attachment; filename=\"$fileName\"");
    } 
    
}
