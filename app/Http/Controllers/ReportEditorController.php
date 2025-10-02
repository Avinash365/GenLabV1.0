<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ReportEditorFile; 
use App\Models\BookingItem;

use App\Services\ReportPdfGenerationService; 
use App\Services\CountTextLineBreakService;

use Mpdf\Mpdf; 

class ReportEditorController extends Controller
{
    protected $pdfService, $countTextLineBreak;

    public function __construct(ReportPdfGenerationService $pdfService, CountTextLineBreakService $countTextLineBreak)
    {
        $this->pdfService = $pdfService;  

        $this->countTextLineBreak = $countTextLineBreak;
    }
     

    public function index()
    {
        $reports = ReportEditorFile::latest()->get();
        return view('Reportfrmt.index', compact('reports'));
    }    

    public function generate(BookingItem $item){  

       $assignedReport = $item->reports->first();
       $booking = $item->booking;

        $reports = ReportEditorFile::latest()->get(); 

        return view('Reportfrmt.generate', compact('reports', 'assignedReport', 'item', 'booking'));
    }
    
    public function editReport($pivotId)
    {
      
        $pivotRecord = \DB::table('booking_item_report')->where('id', $pivotId)->first();

        if (!$pivotRecord) {
            abort(404, 'Report not found');
        }

        $item = BookingItem::with(['booking', 'analyst', 'receivedBy', 'reports'])->find($pivotRecord->booking_item_id);
        if (!$item) {
            abort(404, 'Booking item not found');
        }

        
        $assignedReport = ReportEditorFile::find($pivotRecord->report_editor_file_id);

  
        $booking = $item->booking;

 
        $reports = ReportEditorFile::latest()->get();

        return view('Reportfrmt.generate', compact('reports', 'assignedReport', 'item', 'booking', 'pivotRecord'));
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
        ]);

        $headerData = [ 
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
       
        return back()->with('success', 'Report Genrated successfully!'); 

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

}
