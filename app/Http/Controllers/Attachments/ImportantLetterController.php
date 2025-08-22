<?php

namespace App\Http\Controllers\Attachments;

use App\Http\Controllers\Controller;
use App\Models\ImportantLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\FileUploadService;

class ImportantLetterController extends Controller
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
        $this->authorizeResource(ImportantLetter::class, 'importantLetter');
    }
    
    public function index()
    {
        // if (auth()->guard('admin')->check()) {
        //         $documents = ImportantLetter::with('uploader')->latest()->paginate(10);  
        //         return view('superadmin.attachments.letters.index', compact('documents'));
        // }

        $letters = ImportantLetter::with('uploader')->latest()->paginate(10);
        
        return view('superadmin.attachments.letters.index', compact('letters'));
    }

    public function create()
    {
        return view('superadmin.attachments.letters.create');
    }

    public function store(Request $request)
    {
            $validated = $request->validate([
                'department_name' => 'required|string|max:255',
                'client_name'     => 'required|string|max:255',
                'letter_no'      => 'required|string|max:255',
                'sample'          => 'nullable|string',
                'file'            => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:4096',
                'letter_data'     => 'nullable|date', 
                'status'          => 'nullable|string', 
                'remarks'         => 'nullable|string|max:1000'
            ]);

        try {
            
            if ($request->hasFile('file')) {
                $validated['file_path'] = $this->fileUploadService->upload(
                    $request->file('file'),
                    'importantLetters'
                );
            }

            $validated['uploaded_by'] = Auth::id();

            ImportantLetter::create($validated);

            return redirect()->back()
                             ->with('success', 'Letter saved successfully.');
        } catch (\Exception $e) {
            Log::error("Error saving letter: " . $e->getMessage());
            return back()->with('error', 'Something went wrong while saving the letter.');
        }
    }

    public function show(ImportantLetter $importantLetter)
    {
        return view('important_letters.show', compact('importantLetter'));
    }

    public function edit(ImportantLetter $importantLetter)
    {
        return view('important_letters.edit', compact('importantLetter'));
    }

    public function update(Request $request, ImportantLetter $importantLetter, FileUploadService $fileUploadService)
    {
         $validated = $request->validate([
            'department_name' => 'required|string|max:255',
            'client_name'     => 'required|string|max:255',
            'letter_no'       => 'required|string|max:255',
            'sample'          => 'nullable|string',
            'file'            => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:4096',
            'letter_data'     => 'required|string|max:1000', // corrected: string instead of date
            'status'          => 'required|in:send,archived', // only allowed values
            'remarks'         => 'nullable|string|max:1000',
        ]);

        try {
            
            if ($request->hasFile('file')) {
                $validated['file_path'] = $fileUploadService->upload($request->file('file'), 'importantLetters');
            }
            $validated['uploaded_by'] = Auth::id(); 

            $importantLetter->update($validated);

            return redirect()->back()
                             ->with('success', 'Letter updated successfully.');
        } catch (\Exception $e) {
            Log::error("Error updating letter: " . $e->getMessage());
            return back()->with('error', 'Something went wrong while updating the letter.');
        }
    }

    public function destroy(ImportantLetter $importantLetter)
    {
        try {
            if ($importantLetter->file_path && Storage::disk('public')->exists($importantLetter->file_path)) {
                Storage::disk('public')->delete($importantLetter->file_path);
            }

            $importantLetter->delete();

            return redirect()->back()
                             ->with('success', 'Letter deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Error deleting letter: " . $e->getMessage());
            return back()->with('error', 'Something went wrong while deleting the letter.');
        }
    }
}
