<?php

namespace App\Http\Controllers\Attachments;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploadService;

use App\Models\Approval;

class ApprovalController extends Controller
{
    
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
        $this->authorizeResource(Approval::class, 'approval');

    }
    
    public function index()
    {
        if (auth()->guard('admin')->check()) {
            $approvals = Approval::latest()->paginate(10);  
            return view('superadmin.attachments.approvals.index', compact('approvals'));
        }

        $approvals = Auth::user()->uploadedApprovals()->latest()->paginate(10);
        return view('superadmin.attachments.approvals.index', compact('approvals'));
    }

    public function create()
    {
        return view('superadmin.attachments.approvals.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'department_name' => 'required|string|max:255',
                'approval_data'   => 'required|date',
                'due_date'        => 'nullable|date|after:today',
                'description'     => 'nullable|string',
                'file'            => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:2048',
            ]);


            if ($request->hasFile('file')) {
                $validated['file_path'] = $this->fileUploadService->upload(
                    $request->file('file'),
                    'approvals'
                );
            }
            $validated['uploaded_by']=Auth::id(); 
             

            Approval::create($validated);

            return redirect()->back()->with('success', 'Approval created successfully.');
        } catch (Exception $e) {
            Log::error("Approval Store Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while creating approval.');
        }
    }

    public function show(Approval $approval)
    {
        return view('approvals.show', compact('approval'));
    }

    public function edit(Approval $approval)
    {
        return view('approvals.edit', compact('approval'));
    }

    public function update(Request $request, Approval $approval, FileUploadService $fileUploadService)
    {
        try {
            $validated = $request->validate([
                'department_name' => 'required|string|max:255',
                'approval_data'   => 'required|date',
                'due_date'        => 'nullable|date|after:today',
                'description'     => 'nullable|string',
                'file'            => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:2048',
                'status'          => 'required|in:pending,approved,rejected',
            ]);

           if ($request->hasFile('file')) {
                $validated['file_path'] = $fileUploadService->upload($request->file('file'), 'approvals');
            }

            $approval->update($validated);

            return redirect()->back()->with('success', 'Approval updated successfully.');
        } catch (Exception $e) {
            Log::error("Approval Update Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while updating approval.');
        }
    }

    public function destroy(Approval $approval)
    {
        try {
            if ($approval->file_path && Storage::disk('public')->exists($approval->file_path)) {
                Storage::disk('public')->delete($approval->file_path);
            }

            $approval->delete();

            return redirect()->back()->with('success', 'Approval deleted successfully.');
        } catch (Exception $e) {
            Log::error("Approval Delete Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while deleting approval.');
        }
    }
}
