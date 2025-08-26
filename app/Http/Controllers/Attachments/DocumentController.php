<?php

namespace App\Http\Controllers\Attachments;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploadService;

class DocumentController extends Controller
{
    
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
                $this->authorizeResource(Document::class, 'document');
    }

    
    public function index(Request $request)
    {
        try {
            
            // if (auth()->guard('admin')->check()) {
            //     $documents = Document::with('user')->latest()->paginate(10);  
            //     return view('superadmin.attachments.approvals.index', compact('documents'));
            // }

            $search = $request->input('search'); 


            $documents = Document::with('user')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);

            return view('superadmin.attachments.documents.index', compact('documents'));
        
        } catch (\Exception $e) {
            Log::error('Document index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors('Unable to load documents. Please try again later.');
        }
    }

    public function create()
    {
        return view('superadmin.attachments.documents.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:office,important,account,other',
            'description' => 'nullable|string',
            'file'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            // If file exists, upload using service
            if ($request->hasFile('file')) {
                $validated['file_path'] = $this->fileUploadService->upload(
                    $request->file('file'),
                    'documents'
                );
            }

            $validated['uploaded_by'] = Auth::id();

            unset($validated['file']);

            Document::create($validated);

            return redirect()->back()->with('success', 'Document uploaded successfully.');
        } catch (\Exception $e) {
            Log::error('Document store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors('Failed to upload document. Please try again later.');
        }
    }

    public function show(Document $document)
    {
        return view('documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        return view('documents.edit', compact('document'));
    }

    public function update(Request $request, Document $document, FileUploadService $fileUploadService)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:office,important,account,other',
            'description' => 'nullable|string',
            'file'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'status'      => 'required|in:active,archived',
        ]);

        try {
           
            if ($request->hasFile('file')) {
                $validated['file_path'] = $fileUploadService->upload($request->file('file'), 'documents');
            }

            // Save updated data
            $document->update($validated);

            return redirect()->back()->with('success', 'Document updated successfully.');
        } catch (\Exception $e) {
            Log::error('Document update error: ' . $e->getMessage(), [
                'document_id' => $document->id,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors('Failed to update document. Please try again.');
        }
    }
    public function destroy(Document $document)
    {
        try {
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }
            $document->delete();

            return redirect()->back()->with('success', 'Document deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Document delete error: ' . $e->getMessage(), ['document_id' => $document->id]);
            return back()->withErrors('Failed to delete document. Please try again.');
        }
    }
}
