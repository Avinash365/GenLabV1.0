<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ReportEditorFile;

class ReportEditorController extends Controller
{
    public function index()
    {
        $reports = ReportEditorFile::latest()->get();
        return view('Reportfrmt.index', compact('reports'));
    }   

    public function store(Request $request)
    {
        // Validate inputs
        $request->validate([
            'report_no' => [
                'required',
                'string',
                'max:255',
                'unique:report_editor_files,report_no'
            ],
            'report_description' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);
        
        $report_no = $request->input('report_no');
        $report_description = $request->input('report_description');    
        $content = $request->input('content');


        $sanitizedReportNo = preg_replace('/[^A-Za-z0-9_\-]/', '_', $report_no);
        $fileName = 'reportFormat/' . $sanitizedReportNo . '_' . time() . '.html';

        Storage::disk('public')->put($fileName, $content);

        $report = new ReportEditorFile();
        $report->report_no = $report_no;
        $report->report_description = $report_description;
        $report->file_path = $fileName;
        $report->save();

        return back()->with([
            'success' => 'Content saved successfully!',
            'content' => $content,
            'report_no' => $report_no,
            'report_description' => $report_description
        ]);
    }   

    public function update(Request $request, $id)
    {
        $report = ReportEditorFile::findOrFail($id);

        $request->validate([
            'report_no' => [
                'required',
                'string',
                'max:255',
                'unique:report_editor_files,report_no,' . $report->id
            ],
            'report_description' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);

        $report_no = $request->input('report_no');
        $report_description = $request->input('report_description');
        $content = $request->input('content');


        $sanitizedReportNo = preg_replace('/[^A-Za-z0-9_\-]/', '_', $report_no);

        $fileName = 'reportFormat/' . $sanitizedReportNo . '_' . time() . '.html';

        Storage::disk('public')->put($fileName, $content);

    
        if (Storage::disk('public')->exists($report->file_path)) {
            Storage::disk('public')->delete($report->file_path);
        }

        $report->report_no = $report_no;
        $report->report_description = $report_description;
        $report->file_path = $fileName;
        $report->save();

        return back()->with([
            'success' => 'Report updated successfully!',
            'content' => $content,
            'report_no' => $report_no,
            'report_description' => $report_description
        ]);
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

}
